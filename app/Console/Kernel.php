<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Register the commands for the application.
     */
    protected $commands = [
        \App\Console\Commands\CalculateDailyDistances::class,

         // Ajoute la commande que nous avons créée
       \App\Console\Commands\RefreshDashboardCache::class,

    
        \App\Console\Commands\UpdateMotosCache::class,
        \App\Console\Commands\UpdateDailyDistances::class,
        \App\Console\Commands\BroadcastDashboard::class,


    \App\Console\Commands\MotosCheckZone::class,
    // ... autres commandes


    ];

    protected $routeMiddleware = [
        'auth' => \App\Http\Middleware\Authenticate::class,
        'check.employe' => \App\Http\Middleware\CheckEmployeSession::class,
        // ... autres middlewares
    ];

    

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule)
    {
        // Planifie la commande pour s'exécuter toutes les heures
        $schedule->command('dashboard:refresh-cache')->everyFiveMinutes();
        // Schedule the daily distance calculation at 23:55
        $schedule->command('calculate:daily-distances')->dailyAt('23:55');

        
        $schedule->command('motos:update-cache')->everyMinute()->withoutOverlapping();
        $schedule->command('motos:update-distances')->everyTenMinutes()->withoutOverlapping();


        // Planifie la commande
        $schedule->command('motos:check-zone')->everyMinute();

    }

    /**
     * Register the commands for the application.
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
