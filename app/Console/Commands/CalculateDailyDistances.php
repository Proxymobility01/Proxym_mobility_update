<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ValidatedUser;
use App\Models\DailyDistance;
use Illuminate\Support\Facades\DB;
use App\Helpers\GeoHelper;
use Carbon\Carbon;

class CalculateDailyDistances extends Command
{
    protected $signature = 'calculate:daily-distances';
    protected $description = 'Calculate and store daily distances traveled by each user based on GPS data';

    public function handle()
    {
        $users = \App\Models\ValidatedUser::with('moto')->get();

        foreach ($users as $user) {
            $moto = $user->moto;
            if (!$moto || !$moto->gps_imei) {
                $this->warn("⛔ Skipping user {$user->id} - no moto or gps_imei.");
                continue;
            }

            $macid = $moto->gps_imei;
            $this->line("📡 Processing user {$user->id} | MAC ID: {$macid}");

            // ✅ Get all distinct dates from the data (converted from ms to s)
            $dates = DB::table('device_locations')
                ->selectRaw("DATE(FROM_UNIXTIME(server_time / 1000)) as date")
                ->where('macid', $macid)
                ->groupBy('date')
                ->pluck('date');

            foreach ($dates as $date) {
                // ✅ Fetch location points for this macid and date
                $locations = DB::table('device_locations')
                    ->where('macid', $macid)
                    ->whereDate(DB::raw('FROM_UNIXTIME(server_time / 1000)'), $date)
                    ->orderBy('server_time')
                    ->get();

                $count = $locations->count();
                $this->line("📅  → Date: {$date} | Points found: {$count}");

                if ($count < 2) {
                    $this->warn("⚠️  Not enough points for user {$user->id} on {$date} — skipping.");
                    continue;
                }

                $distance = 0;
                for ($i = 1; $i < $count; $i++) {
                    $prev = $locations[$i - 1];
                    $curr = $locations[$i];

                    if ($prev->latitude && $prev->longitude && $curr->latitude && $curr->longitude) {
                        $distance += \App\Helpers\GeoHelper::haversine(
                            $prev->latitude,
                            $prev->longitude,
                            $curr->latitude,
                            $curr->longitude
                        );
                    }
                }

                \App\Models\DailyDistance::updateOrCreate(
                    [
                        'validated_user_id' => $user->id,
                        'date' => $date,
                    ],
                    [
                        'total_distance_km' => round($distance, 2)
                    ]
                );

                $this->info("✅ Saved for {$date} → " . round($distance, 2) . " km");
            }

            $this->line("—— Finished user {$user->id} —————————————\n");
        }

        $this->info("🏁 All distances calculated and saved.");
    }



}
