<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Jobs\RecalculateDailyDistances;
use App\Http\Controllers\DailyDistanceController;
use App\Http\Controllers\Gps\LocalisationController;
use App\Console\Commands\RefreshDashboardCache;
use App\Http\Controllers\Batteries\BatterieSOEController;
use App\Http\Controllers\Batteries\EtatBatterieAssociation5MinController;





Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');



//Schedule::command('motos:check-zone')->everyFiveMinutes(); // ou everyMinute()
// Schedule::job(new RecalculateDailyDistances)->everyFiveMinutes();



//Schedule::command('motos:check-zone')->everyFiveMinutes(); // ou everyMinute()
// Schedule::job(new RecalculateDailyDistances)->everyFiveMinutes();



/*** 
Schedule::call(function () {
    app(LocalisationController::class)->updateCacheMotosEtZones();
})->everyMinute();

Schedule::call(function () {
    app(DailyDistanceController::class)->updateDailyDistances();
})->everyTenMinutes();



Schedule::call(function () {
    app(LocalisationController::class)->updateCacheMotosEtZones();
})->everyMinute();

*/

//Schedule::job(new RecalculateDailyDistances)->everyFiveMinutes();

// Planifier la commande pour s'exécuter à une fréquence donnée



//Schedule::command('dashboard:refresh-cache')->everyFiveMinutes();



//Schedule::command('motos:check-zone')->everyFiveMinutes();


// cron pour le SOE des batteries chaque jour
Schedule::call(function () {
    app(BatterieSOEController::class)->storeDailySoe();
})->everyMinute();



// cron etat des batteries toutes les 5 minutes avec leurs associations
   // ✅ Bon : en utilisant Schedule directement
Schedule::call(function () {
    app(EtatBatterieAssociation5MinController::class)->enregistrer();
})->everyMinute();