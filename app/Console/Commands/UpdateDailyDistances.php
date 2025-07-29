<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\DailyDistanceController;

class UpdateDailyDistances extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-daily-distances';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calcule les distances journalières parcourues par les motos';

    /**
     * Execute the console command.
     */
    public function handle()
    {
         \Log::info("🕒 [CRON] Début calcul distances");
        app(DailyDistanceController::class)->updateDailyDistances();
        \Log::info("✅ [CRON] Fin calcul distances");
    }
}
