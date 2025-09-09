<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BmsApi
{
    public function __construct(
        private ?string $vendor = null // 'huiming' | 'vercom'
    ) {
        Log::info("[BMS][API] BmsApi instantiated", ['vendor' => $this->vendor]);
    }

    public static function forVendor(string $vendor): self
    {
        $normalized = self::normalizeVendor($vendor);
        Log::info("[BMS][API] Creating instance for vendor", [
            'input_vendor' => $vendor,
            'normalized'   => $normalized
        ]);
        return new self($normalized);
    }

    public static function normalizeVendor(string $vendor): string
    {
        $v = strtolower(trim($vendor));
        $normalized = match (true) {
            str_starts_with($v, 'ver') => 'vercom',
            default                    => 'huiming',
        };
        Log::debug("[BMS][API] normalizeVendor()", ['input' => $vendor, 'normalized' => $normalized]);
        return $normalized;
    }

    /** ---------- GUARDS & HELPERS ---------- */

    private function assertVendorConfigured(): string
    {
        if (!$this->vendor) {
            Log::error("[BMS][API] Vendor not specified.");
            throw new \InvalidArgumentException("BMS vendor is not specified. Use BmsApi::forVendor('huiming'|'vercom').");
        }
        return $this->vendor;
    }

    private static function mask(?string $value, int $keepStart = 5, int $keepEnd = 4): string
    {
        if ($value === null || $value === '') return '';
        $len = mb_strlen($value, 'UTF-8');
        if ($len <= $keepStart + $keepEnd) return str_repeat('*', $len);
        return mb_substr($value, 0, $keepStart, 'UTF-8')
            . str_repeat('*', max(3, $len - $keepStart - $keepEnd))
            . mb_substr($value, $len - $keepEnd, null, 'UTF-8');
    }

    private function endpointBase(): string
    {
        $this->assertVendorConfigured();

        $raw = Config::get("bms.endpoints.{$this->vendor}");
        Log::debug("[BMS][API] Using endpoint (raw)", ['vendor' => $this->vendor, 'endpoint' => $raw]);

        if (!is_string($raw) || trim($raw) === '') {
            Log::error("[BMS][API] Missing endpoint config", ['vendor' => $this->vendor, 'got' => $raw]);
            throw new \RuntimeException("Missing BMS endpoint for vendor [{$this->vendor}]. Check config/bms.php and .env");
        }

        $base = rtrim($raw, '/');
        // Trim accidental method suffixes to keep a clean base
        foreach (['loginSystem', 'getDateFunc'] as $suffix) {
            if (preg_match('#/' . preg_quote($suffix, '#') . '$#i', $base)) {
                $before = $base;
                $base = preg_replace('#/' . preg_quote($suffix, '#') . '$#i', '', $base);
                Log::warning("[BMS][API] Trimming method from endpoint base", ['before' => $before, 'after' => $base]);
                break;
            }
        }
        return $base;
    }

    private function loginUrl(): string
    {
        return $this->endpointBase() . '/loginSystem';
    }

    private function dataUrl(): string
    {
        return $this->endpointBase() . '/getDateFunc';
    }

    private function credentials(): array
    {
        $this->assertVendorConfigured();

        $creds = Config::get("bms.auth.{$this->vendor}");
        Log::debug("[BMS][API] Using credentials", [
            'vendor' => $this->vendor,
            'creds'  => is_array($creds)
                ? ['LoginName' => $creds['LoginName'] ?? null, 'Password' => '***']
                : $creds
        ]);

        if (!is_array($creds) || empty($creds['LoginName']) || empty($creds['Password'])) {
            Log::error("[BMS][API] Missing credentials", ['vendor' => $this->vendor, 'got' => $creds]);
            throw new \RuntimeException("Missing BMS credentials for vendor [{$this->vendor}]. Check config/bms.php and .env");
        }

        return $creds;
    }

    private function cacheKey(): string
    {
        $key = "bms_mds_token:{$this->assertVendorConfigured()}";
        Log::debug("[BMS][API] Generated cache key", ['key' => $key]);
        return $key;
    }

    /** ---------- PUBLIC API ---------- */

    public function getMds(): string
    {
        Log::info("[BMS][API] Fetching MDS token", ['vendor' => $this->assertVendorConfigured()]);

        $mds = Cache::remember(
            $this->cacheKey(),
            now()->addMinutes((int) Config::get('bms.token_ttl', 18)),
            function () {
                Log::info("[BMS][API] No cached MDS token found. Logging in...");
                return $this->login();
            }
        );

        $masked = self::mask($mds);
        Log::info("[BMS][API] MDS token obtained", ['mds.masked' => $masked]);
        echo "<b>[MDS TOKEN]</b> {$masked}<br>";
        return $mds;
    }

    public function login(): string
    {
        $creds = $this->credentials();

        // Spec requires GET with these param names
        // LoginName, LoginPassword, LoginType, language, timeZone, apply, ISMD5  (success errorCode=200)
        $params = [
            'LoginName'     => $creds['LoginName'],
            'LoginPassword' => $creds['Password'],   // map to spec name
            'LoginType'     => Config::get('bms.login_type', 'ENTERPRISE'),
            'language'      => Config::get('bms.language', 'en'),
            'timeZone'      => Config::get('bms.timezone', '+01'), // Africa/Douala
            'apply'         => Config::get('bms.apply', 'APP'),
            'ISMD5'         => Config::get('bms.is_md5', 0),
        ];

        $safeParams = $params;
        $safeParams['LoginPassword'] = '***';

        Log::info("[BMS][API] Logging in (GET)", ['url' => $this->loginUrl(), 'params' => $safeParams]);
        echo "<hr><b>LOGIN (GET):</b> {$this->loginUrl()}<br>";
        echo "<b>Query:</b><pre>" . json_encode($safeParams, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";

        $res = Http::timeout(30)->acceptJson()->get($this->loginUrl(), $params);

        $resp = $res->json();
        if (!is_array($resp)) {
            $decoded = json_decode($res->body(), true);
            $resp = is_array($decoded) ? $decoded : [];
        }

        Log::info("[BMS][API] Login response received", [
            'status'  => $res->status(),
            'body'    => $resp
        ]);
        echo "<b>HTTP Status:</b> {$res->status()}<br>";
        echo "<b>Login Response:</b><pre>" . json_encode($resp, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";

        if (
            !isset($resp['errorCode']) ||
            (string) $resp['errorCode'] !== '200' ||
            empty($resp['mds'])
        ) {
            Log::error("[BMS][API] Login failed", ['response' => $resp]);
            echo "<b style='color:red'>Login failed!</b><br>";
            throw new \RuntimeException('BMS login failed: ' . json_encode($resp, JSON_UNESCAPED_UNICODE));
        }

        $mds = (string) $resp['mds'];
        $maskedMds = self::mask($mds);
        Log::info("[BMS][API] Login successful", ['mds.masked' => $maskedMds]);
        echo "<b style='color:green'>Login successful!</b><br>";

        return $mds;
    }

    /**
     * Core requester for non-login calls: POST form -> /getDateFunc
     */
    private function postData(array $params): array
    {
        $url = $this->dataUrl();

        // Mask sensitive
        $safe = $params;
        if (isset($safe['mds'])) $safe['mds'] = self::mask($safe['mds']);

        Log::info("[BMS][API] Sending FORM POST", ['url' => $url, 'params' => $safe]);
        echo "<hr><b>REQUEST (form):</b> {$url}<br>";
        echo "<b>Body:</b><pre>" . json_encode($safe, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";

        $res = Http::timeout(30)->acceptJson()->asForm()->post($url, $params);

        $resp = $res->json();
        if (!is_array($resp)) {
            $decoded = json_decode($res->body(), true);
            $resp = is_array($decoded) ? $decoded : [];
        }

        Log::info("[BMS][API] Response received", [
            'status'  => $res->status(),
            'body'    => $resp
        ]);

        echo "<b>HTTP Status:</b> {$res->status()}<br>";
        echo "<b>API Response:</b><pre>" . json_encode($resp, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";

        if (!$res->ok()) {
            Log::error("[BMS][API] HTTP error", ['status' => $res->status(), 'body' => $res->body()]);
            echo "<b style='color:red'>BMS HTTP Error {$res->status()}</b><br>";
            throw new \RuntimeException("BMS HTTP error {$res->status()}: " . $res->body());
        }

        if ($resp === []) {
            Log::error("[BMS][API] Malformed/empty JSON response");
            throw new \RuntimeException("BMS returned an empty or malformed JSON response.");
        }

        return $resp;
    }

    /**
     * Call with automatic token refresh on 401/403/-100/-200 (once).
     */
    private function callWithMds(array $payload): array
    {
        $this->assertVendorConfigured();

        if (empty($payload['method'])) {
            throw new \InvalidArgumentException("Missing 'method' in payload.");
        }

        Log::info("[BMS][API] Calling API with MDS", ['vendor' => $this->vendor, 'payload' => $payload]);
        echo "<hr><b>Calling API Method:</b> {$payload['method']}<br>";

        $mds = $this->getMds();
        $resp = $this->postData($payload + ['mds' => $mds]);

        $err = isset($resp['errorCode']) ? (int) $resp['errorCode'] : null;
        if (in_array($err, [401, 403, -100, -200], true)) {
            Log::warning("[BMS][API] MDS token invalid/expired. Refreshing...", ['errorCode' => $err]);
            echo "<b style='color:orange'>MDS token expired. Refreshing...</b><br>";

            $mds = $this->login();
            Cache::put($this->cacheKey(), $mds, now()->addMinutes((int) Config::get('bms.token_ttl', 18)));
            $resp = $this->postData($payload + ['mds' => $mds]);

            echo "<b>Retried Response:</b><pre>" . json_encode($resp, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";
        }

        echo "<b>Final Response:</b><pre>" . json_encode($resp, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";
        return $resp;
    }

    // === Vendor-specific methods (aligned with PDF) ===

    /**
     * Send command(s) to device(s).
     * - Spec: SendCommands requires a single macid per request (returns CmdNo).
     * - If you pass multiple $numbers, we'll send one request per macid and aggregate.
     * - $bmsparam must contain 'cmd' and optional 'param' (spec terms). Any legacy keys are rejected.
     */
    public function sendBmsCommands(array $numbers, array $bmsparam): array
    {
        $this->assertVendorConfigured();
        if (empty($numbers)) {
            throw new \InvalidArgumentException("At least one device number is required.");
        }

        // Validate keys if you want (optional): ensure only known spec keys are sent

        // IMPORTANT: vendor expects bmsparam as a JSON STRING, not a nested object
        $bmsparamJson = json_encode($bmsparam, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        Log::info("[BMS][API] Sending BMS commands", [
            'vendor'   => $this->vendor,
            'numbers'  => $numbers,
            'bmsparam' => $bmsparam
        ]);

        $payload = [
            'method'   => 'SendBmsCommands',
            'numbers'  => implode(';', $numbers),
            'bmsparam' => $bmsparamJson,
        ];

        return $this->callWithMds($payload);
    }


    /**
     * Batch realtime by params (spec: numbers + optional params).
     * @param array $numbers
     * @param null|array|string $params  e.g. ['SOC','CHON'] or "SOC;CHON"
     */
    public function bmsRealTimeStateByParam(array $numbers, array|string|null $params = null): array
    {
        $this->assertVendorConfigured();

        if (empty($numbers)) {
            Log::error("[BMS][API] bmsRealTimeStateByParam called with empty numbers");
            throw new \InvalidArgumentException("At least one device number is required.");
        }

        $payload = [
            'method'  => 'BMSRealTimeStateByParam',
            'numbers' => implode(';', $numbers),
        ];
        if (is_array($params) && !empty($params)) {
            $payload['params'] = implode(';', $params);
        } elseif (is_string($params) && trim($params) !== '') {
            $payload['params'] = $params;
        }

        Log::info("[BMS][API] Fetching BMS realtime by params", ['vendor' => $this->vendor, 'payload' => $payload]);
        echo "<hr><b>Fetching Real-time State (By Params)</b><br>";

        $resp = $this->callWithMds($payload);
        Log::info("[BMS][API] BMSRealTimeStateByParam response", ['response' => $resp]);
        echo "<b>Real-time State Response:</b><pre>" . json_encode($resp, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";
        return $resp;
    }

    /**
     * Single device realtime (spec: macid).
     */
    public function bmsRealTimeState(string $macid): array
    {
        $this->assertVendorConfigured();

        if (trim($macid) === '') {
            Log::error("[BMS][API] bmsRealTimeState called with empty macid");
            throw new \InvalidArgumentException("Device number (macid) is required.");
        }

        Log::info("[BMS][API] Fetching BMS real-time state", ['vendor' => $this->vendor, 'macid' => $macid]);
        echo "<hr><b>Fetching Real-time State for:</b> {$macid}<br>";

        $payload = [
            'method' => 'BMSrealTimeState',
            'macid'  => $macid,
        ];

        $resp = $this->callWithMds($payload);
        Log::info("[BMS][API] BMSrealTimeState response", ['response' => $resp]);
        echo "<b>Real-time State Response:</b><pre>" . json_encode($resp, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";
        return $resp;
    }

    /**
     * Get command result (spec: macid + cmdNo).
     * Kept backward compatibility by allowing old signature getCommandResults($cmdNo).
     */
    public function getCommandResults(string $cmdNo, ?string $macid = null): array
    {
        $this->assertVendorConfigured();

        if (trim($cmdNo) === '') {
            Log::error("[BMS][API] getCommandResults called with empty cmdNo");
            throw new \InvalidArgumentException("cmdNo is required.");
        }
        if ($macid === null || trim($macid) === '') {
            Log::error("[BMS][API] getCommandResults missing macid per spec");
            throw new \InvalidArgumentException("macid is required by the vendor API for GetCommandResults.");
        }

        Log::info("[BMS][API] Fetching BMS command results", ['vendor' => $this->vendor, 'macid' => $macid, 'cmdNo' => $cmdNo]);
        echo "<hr><b>Fetching Command Results for:</b> macid={$macid} cmdNo={$cmdNo}<br>";

        $payload = [
            'method' => 'GetCommandResults',
            'macid'  => $macid,
            'cmdNo'  => $cmdNo, // note: lowercase 'c' per spec
        ];

        $resp = $this->callWithMds($payload);
        Log::info("[BMS][API] GetCommandResults response", ['response' => $resp]);
        echo "<b>Command Results Response:</b><pre>" . json_encode($resp, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";
        return $resp;
    }

    // Utility
    public static function chunkNumbers(array $numbers): array
    {
        $size = (int) Config::get('bms.numbers_chunk', 100);
        $chunks = array_chunk($numbers, max(1, $size));

        Log::debug("[BMS][API] Chunking numbers", [
            'original_count' => count($numbers),
            'chunk_size'     => $size,
            'chunks'         => $chunks
        ]);

        return $chunks;
    }
}


