<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Jobs\RecalculateDailyDistances;
use App\Http\Controllers\DailyDistanceController;
use App\Http\Controllers\Gps\LocalisationController;


Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');



//Schedule::command('motos:check-zone')->everyFiveMinutes(); // ou everyMinute()
// Schedule::job(new RecalculateDailyDistances)->everyFiveMinutes();

Schedule::call(function () {
    app(LocalisationController::class)->updateCacheMotosEtZones();
})->everyTwentySeconds();

Schedule::call(function () {
    app(DailyDistanceController::class)->updateDailyDistances();
})->everyTwentySeconds();


// cron job derniere positions des moto
Schedule::call(function () {
    app(LocalisationController::class)->updateCacheMotosEtZones();
})->everyTwentySeconds();