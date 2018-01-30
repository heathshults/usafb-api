<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        'App\Console\Commands\ImportPending',
        'App\Console\Commands\ImportFile',
        'App\Console\Commands\ProviderCreate',
        'App\Console\Commands\ProviderCreateAuthentication',
        'App\Console\Commands\ProviderList',
        'App\Console\Commands\ProviderRole',
        'App\Console\Commands\RoleList',
        'App\Console\Commands\RoleCreate',
        'App\Console\Commands\UserCreate',
        'App\Console\Commands\UserDelete',
        'App\Console\Commands\UserRole',
        'App\Console\Commands\WarehouseStage',
        'App\Console\Commands\WarehouseLoad',
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        //
    }
}
