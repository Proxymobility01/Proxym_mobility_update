<?php

namespace App\Services;

use App\Models\MotosValide;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class CommandMotoService
{
    /* ============================================================
       CONFIG – adaptez ces 4 blocs à votre fournisseur / boîtier
       ============================================================ */
    // 1) Hôtes/API
    private string $BASE_URL  = 'http://apitest.18gps.net';
    private string $LOGIN_URL = 'http://apitest.18gps.net/GetDateServices.asmx/loginSystem';

    // 2) Identifiants de connexion API
    private string $LOGIN_NAME = 'PROXYM';
    private string $LOGIN_PASS = 'Proxym2024.';

    // 3) Méthodes de lecture d’état (essayées dans cet ordre)
    //    => le service saute automatiquement celles non supportées
    private array $STATE_METHODS = [
        'getGpsInfoByMacidsUtcNew',
        'getGpsInfoByMacidsUtc',
        'GetGpsInfoByMacidsUtcNew',
        'GetGpsInfoByMacidsUtc',
        'getGpsInfoByMacids',
        'GetGpsInfoByMacids',
        'getGpsInfoByMacid',
        'GetGpsInfoByMacid',
        'getGpsInfoByImei',
        'GetGpsInfoByImei',
    ];

    // 4) Commandes ON/OFF (ex: passthrough HEX)
    //    cmd=PASSTHROUGH + param="1,<HEX>"  (1 => payload en HEX)
    private array $ENGINE_ON  = ['cmd' => 'PASSTHROUGH', 'param' => '1,55AA'];
    private array $ENGINE_OFF = ['cmd' => 'PASSTHROUGH', 'param' => '1,AA55'];


    /* =========================
       Helpers de configuration
       ========================= */
    private function baseUrl(): string
    {
        return rtrim($this->BASE_URL, '/');
    }

    private function loginUrl(): string
    {
        return $this->LOGIN_URL ?: $this->baseUrl() . '/GetDateServices.asmx/loginSystem';
    }

    private function getDateUrl(): string
    {
        return $this->baseUrl() . '/GetDateServices.asmx/GetDate';
    }


    /* =========================
       Auth & token MDS (caché)
       ========================= */
    private function mds(): string
    {
        $cacheKey = 'gps_mds_' . md5($this->BASE_URL . '|' . $this->LOGIN_NAME);

        return Cache::remember($cacheKey, now()->addMinutes(15), function () {
            $resp = Http::timeout(12)->retry(1, 200)->get($this->loginUrl(), [
                'LoginName'     => $this->LOGIN_NAME,
                'LoginPassword' => $this->LOGIN_PASS,
                'LoginType'     => 'ENTERPRISE',
                'language'      => 'en',
                'ISMD5'         => 0,
                'timeZone'      => '+08',
                'apply'         => 'APP',
                'loginUrl'      => $this->baseUrl(),
            ]);

            if (!$resp->ok()) {
                throw new RuntimeException('Login GPS HTTP ' . $resp->status());
            }

            $json = $resp->json() ?: [];
            $code = (int) ($json['errorCode'] ?? 500);
            if ($code !== 200) {
                throw new RuntimeException('Login GPS échoué: ' . ($json['errorDescribe'] ?? 'inconnu'));
            }

            // Note: certaines implémentations NE renvoient PAS userId. Pas bloquant ici.
            if (!isset($json['userId'])) {
                Log::warning('Login GPS: userId absent dans la réponse', ['login_json' => $json]);
            }

            $mds = (string) ($json['mds'] ?? '');
            if ($mds === '') {
                throw new RuntimeException('Login GPS: token mds manquant');
            }

            return $mds;
        });
    }

    /** GET générique sur /GetDate + gestion refresh token si 403 */
    private function getDate(array $query)
    {
        $url = $this->getDateUrl();

        $resp = Http::timeout(15)->retry(1, 250)->get($url, $query);

        // Token expiré ?
        $json      = $resp->json() ?: [];
        $errorCode = (int) ($json['errorCode'] ?? 0);

        if ($errorCode === 403) {
            // purge et retente une fois
            Cache::forget('gps_mds_' . md5($this->BASE_URL . '|' . $this->LOGIN_NAME));
            $query['mds'] = $this->mds();
            $resp = Http::timeout(15)->retry(1, 250)->get($url, $query);
            $json = $resp->json() ?: [];
        }

        if (!$resp->ok()) {
            throw new RuntimeException('Erreur API HTTP ' . $resp->status());
        }

        $errorCode = (int) ($json['errorCode'] ?? 500);
        if ($errorCode !== 200) {
            // Beaucoup d’instances renvoient le chinois “不支持接口【xxx】” quand non supporté.
            throw new RuntimeException('Erreur API: ' . ($json['errorDescribe'] ?? 'inconnu'));
        }

        return $json;
    }

    /** Appelle une méthode GetDate avec fallback si "non supporté" */
    private function callMethod(string $method, array $params)
    {
        try {
            $payload = array_merge($params, ['method' => $method, 'mds' => $this->mds()]);
            $json = $this->getDate($payload);
            Log::debug('GPS call OK', ['method' => $method, 'keys' => array_keys($json)]);
            return $json;
        } catch (RuntimeException $e) {
            $msg = $e->getMessage();
            // si non supporté → on laisse l’appelant essayer la suivante
            if (str_contains($msg, '不支持接口') || str_contains(strtolower($msg), 'not support')) {
                Log::notice('GPS: méthode non supportée', ['method' => $method, 'err' => $msg]);
                return null;
            }
            // autre erreur → on propage
            throw $e;
        }
    }


    /* =======================================
       Lecture d’état moteur depuis l’API GPS
       ======================================= */
    /**
     * Déduit l’état du moteur depuis un JSON d’info GPS.
     * Retour: 1 = ON (autorisé), 0 = OFF (coupé), null = non déductible
     */
    private function extractEngineState(?array $json): ?int
    {
        if (!$json) return null;

        $data = $json['data'] ?? [];
        if (!is_array($data) || empty($data)) {
            return null;
        }

        // format classique: data[0].key + data[0].records[0]
        $first   = $data[0] ?? [];
        $keyMap  = $first['key']     ?? [];
        $records = $first['records'] ?? [];

        // parfois "rows" au lieu de "records"
        if (empty($records) && isset($first['rows']) && is_array($first['rows'])) {
            $records = $first['rows'];
        }

        // si toujours vide: tenter data[0] direct
        if (empty($records) && isset($first['status'])) {
            $records = [$first];
        }

        $row = $records[0] ?? [];

        // 1) si mappage "status" par index (keyMap['status'] = index)
        if (is_array($keyMap) && array_key_exists('status', $keyMap)) {
            $idx = $keyMap['status'];
            $statusVal = (string) ($row[$idx] ?? '');
            if ($statusVal !== '' && ctype_digit($statusVal)) {
                $statusVal = str_pad($statusVal, 3, '0');
                $oilBit = substr($statusVal, 2, 1); // 3e bit
                return $oilBit === '1' ? 1 : 0;
            }
        }

        // 2) champs explicites communs
        $candidates = [
            'oilElectric', 'oilelectric', 'oil_ele', 'oilEle', 'elecOil', 'elecoil',
            'engine', 'engineOn', 'engine_on',
            'lockPower', 'powerLock', 'powerlock',
            'acc', 'ACC',
        ];
        foreach ($candidates as $field) {
            // clé par index (via keyMap) ou clé par nom
            $val = null;

            if (is_array($keyMap) && array_key_exists($field, $keyMap)) {
                $idx = $keyMap[$field];
                $val = $row[$idx] ?? null;
            } elseif (is_array($row) && array_key_exists($field, $row)) {
                $val = $row[$field];
            }

            if ($val !== null) {
                // différents formats (0/1, "0"/"1", "true"/"false")
                if (is_string($val)) $val = trim($val);
                if (in_array($val, [1, '1', true, 'true'], true))  return 1;
                if (in_array($val, [0, '0', false, 'false'], true)) return 0;
            }
        }

        // 3) plan C: un champ "status" par nom direct
        foreach (['status', 'Status'] as $name) {
            if (isset($row[$name])) {
                $sv = (string) $row[$name];
                if ($sv !== '' && ctype_digit($sv)) {
                    $sv = str_pad($sv, 3, '0');
                    $oilBit = substr($sv, 2, 1);
                    return $oilBit === '1' ? 1 : 0;
                }
            }
        }

        Log::notice('GPS: état moteur non déductible', [
            'keys' => array_keys($first ?? []),
            'row'  => is_array($row) ? array_keys($row) : []
        ]);

        return null;
    }

    /** Lecture état via IMEI (macid) en essayant plusieurs méthodes */
    public function engineStateByMacid(string $macid): int
    {
        foreach ($this->STATE_METHODS as $method) {
            // paramètres typiques
            $params = [
                'mapType' => 'BAIDU',
                'option'  => 'en',
                // on met plusieurs noms par compatibilité
                'macids'  => $macid,
                'macid'   => $macid,
                'imei'    => $macid,
            ];

            $json = $this->callMethod($method, $params);
            if ($json === null) {
                // méthode non supportée → on continue
                continue;
            }

            $state = $this->extractEngineState($json);
            if ($state !== null) {
                Log::info('GPS état lu', ['method' => $method, 'imei' => $macid, 'state' => $state]);
                return $state;
            }
        }

        Log::warning('GPS: aucune méthode de lecture par IMEI supportée', ['imei' => $macid]);
        // Par sécurité: retourner 0 (considérer coupé si indéterminé)
        return 0;
    }


    /* ==================
       Envoi de commande
       ================== */
    private function engineCommandPayload(bool $turnOn): array
    {
        return $turnOn ? $this->ENGINE_ON : $this->ENGINE_OFF;
    }

    /** Envoie une commande au device (par IMEI) */
    private function sendCommandByMacid(string $macid, string $cmd, string $param = ''): array
    {
        $json = $this->callMethod('SendCommands', [
            'macid'    => $macid,
            'cmd'      => $cmd,
            'param'    => $param,
            'pwd'      => '',
            'sendTime' => '',
        ]);

        if ($json === null) {
            // SendCommands non supporté → erreur franche
            throw new RuntimeException("La méthode 'SendCommands' n'est pas supportée par l'API.");
        }

        $row = data_get($json, 'data.0', []);
        Log::info('GPS commande envoyée', ['macid' => $macid, 'cmd' => $cmd, 'param' => $param, 'ret_keys' => array_keys($row)]);
        return $row;
    }


    /* ==========================
       API publique de ce service
       ========================== */
    /** Lit l’état réel par ID de moto */
    public function getStateByMotoId(int $motoId): int
    {
        $moto  = MotosValide::findOrFail($motoId);
        $macid = trim((string) ($moto->gps_imei ?? ''));
        if ($macid === '') {
            Log::warning('Moto sans IMEI', ['moto_id' => $motoId]);
            return 0;
        }
        return $this->engineStateByMacid($macid);
    }

    /** Force un état (ON/OFF) et renvoie l’état final observé (1/0) */
    public function setStateByMotoId(int $motoId, bool $on): int
    {
        $moto  = MotosValide::findOrFail($motoId);
        $macid = trim((string) ($moto->gps_imei ?? ''));
        if ($macid === '') {
            Log::warning('Moto sans IMEI', ['moto_id' => $motoId]);
            return 0;
        }

        // 1) Envoi
        $p = $this->engineCommandPayload($on);
        $this->sendCommandByMacid($macid, $p['cmd'] ?? 'PASSTHROUGH', $p['param'] ?? '');

        // 2) Mini-polling pour confirmer
        $expected = (int) $on;
        $state    = -1;
        for ($i = 0; $i < 3; $i++) {
            usleep(1200_000); // ~1,2 s
            $state = $this->engineStateByMacid($macid);
            if ($state === $expected) break;
        }

        $final = ($state === 0) ? 0 : 1;
        Log::info('GPS final state', ['moto_id' => $motoId, 'imei' => $macid, 'expected' => $expected, 'final' => $final]);
        return $final;
    }

    /** Bascule l’état courant et renvoie l’état final (1/0) */
    public function toggleByMotoId(int $motoId): int
    {
        $cur = $this->getStateByMotoId($motoId);
        $targetOn = $cur === 1 ? false : true;
        return $this->setStateByMotoId($motoId, $targetOn);
    }
}
