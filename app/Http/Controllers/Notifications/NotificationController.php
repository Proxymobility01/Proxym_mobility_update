<?php

namespace App\Http\Controllers\Notifications;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        // Validation des filtres
        $data = $request->validate([
            'scope' => 'nullable|in:today,week,month,year,date,range,all',
            'date'  => 'nullable|date',
            'start' => 'nullable|date',
            'end'   => 'nullable|date',
        ]);

        $scope  = $data['scope'] ?? 'all';
        $appTz  = config('app.timezone', 'UTC');

        // Construction de la plage temporelle en LOCAL, puis conversion en UTC pour la requête
        $startUTC = null;
        $endUTC   = null;
        $filterLabel = 'Toutes les notifications';

        $nowLocal = Carbon::now($appTz);

        switch ($scope) {
            case 'today':
                $startLocal = $nowLocal->copy()->startOfDay();
                $endLocal   = $nowLocal->copy()->endOfDay();
                $filterLabel = "Aujourd'hui";
                break;

            case 'week':
                // Semaine courante (lundi -> dimanche)
                $startLocal = $nowLocal->copy()->startOfWeek(Carbon::MONDAY);
                $endLocal   = $nowLocal->copy()->endOfWeek(Carbon::SUNDAY);
                $filterLabel = 'Cette semaine';
                break;

            case 'month':
                $startLocal = $nowLocal->copy()->startOfMonth();
                $endLocal   = $nowLocal->copy()->endOfMonth();
                $filterLabel = 'Ce mois';
                break;

            case 'year':
                $startLocal = $nowLocal->copy()->startOfYear();
                $endLocal   = $nowLocal->copy()->endOfYear();
                $filterLabel = 'Cette année';
                break;

            case 'date':
                if (!empty($data['date'])) {
                    $d = Carbon::parse($data['date'], $appTz);
                    $startLocal = $d->copy()->startOfDay();
                    $endLocal   = $d->copy()->endOfDay();
                    $filterLabel = 'Le ' . $d->format('d/m/Y');
                }
                break;

            case 'range':
                if (!empty($data['start']) && !empty($data['end'])) {
                    $s = Carbon::parse($data['start'], $appTz)->startOfDay();
                    $e = Carbon::parse($data['end'], $appTz)->endOfDay();
                    if ($e->lt($s)) { [$s, $e] = [$e, $s]; }
                    $startLocal = $s;
                    $endLocal   = $e;
                    $filterLabel = 'Du ' . $s->format('d/m/Y') . ' au ' . $e->format('d/m/Y');
                }
                break;

            case 'all':
            default:
                // pas de filtre
                break;
        }

        if (isset($startLocal, $endLocal)) {
            // Convertir en UTC pour comparer à la colonne created_at (généralement stockée en UTC)
            $startUTC = $startLocal->clone()->setTimezone('UTC');
            $endUTC   = $endLocal->clone()->setTimezone('UTC');
        }

        $query = DB::table('notifications_web')->select('title', 'description', 'created_at');

        if ($startUTC && $endUTC) {
            $query->whereBetween('created_at', [$startUTC, $endUTC]);
        }

        $notifications = $query
            ->orderBy('created_at', 'desc')
            ->paginate(12)
            ->appends($request->query()); // conserve les filtres dans la pagination

        return view('notifications.notification', [
            'notifications' => $notifications,
            'scope'         => $scope,
            'date'          => $data['date']  ?? '',
            'start'         => $data['start'] ?? '',
            'end'           => $data['end']   ?? '',
            'filterLabel'   => $filterLabel,
            'appTz'         => $appTz,
        ]);
    }
}
