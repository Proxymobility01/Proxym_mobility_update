<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Jobs\RecalculateDailyDistances;
use App\Http\Controllers\DailyDistanceController;
use App\Http\Controllers\Gps\LocalisationController;
<<<<<<< HEAD
=======
use App\Http\Controllers\DashboardController;
>>>>>>> bdcca81 (version stable avec authentifiacation et ravitaillement et gestion efficasse des stations)


Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');



//Schedule::command('motos:check-zone')->everyFiveMinutes(); // ou everyMinute()
// Schedule::job(new RecalculateDailyDistances)->everyFiveMinutes();

Schedule::call(function () {
    app(LocalisationController::class)->updateCacheMotosEtZones();
<<<<<<< HEAD
})->everyTwentySeconds();

Schedule::call(function () {
    app(DailyDistanceController::class)->updateDailyDistances();
})->everyTwentySeconds();
=======
})->everyMinute();

Schedule::call(function () {
    app(DailyDistanceController::class)->updateDailyDistances();
})->everyMinute();
>>>>>>> bdcca81 (version stable avec authentifiacation et ravitaillement et gestion efficasse des stations)


// cron job derniere positions des moto
Schedule::call(function () {
    app(LocalisationController::class)->updateCacheMotosEtZones();
<<<<<<< HEAD
})->everyTwentySeconds();
=======
})->everyMinute();


Schedule::call(function () {
    app(DashboardController::class)->updateBatteryCache();
})->everyMinute(); // ou everyMinute() pour tester
>>>>>>> bdcca81 (version stable avec authentifiacation et ravitaillement et gestion efficasse des stations)
