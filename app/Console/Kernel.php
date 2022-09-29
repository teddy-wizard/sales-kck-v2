<?php

namespace App\Console;

use App\Console\Commands\SyncCron;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        SyncCron::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('sync:cron')
        //         ->daily()
        //         ->onSuccess(function(){
        //             Log::info("Success!!!");
        //         })
        //         ->onFailure(function(){
        //             Log::info("Failed!!!");
        //         });
        $schedule->command('sync:cron')
                ->withoutOverlapping()
                ->onSuccess(function(){
                    Log::info("sync:cron success!!!");
                })
                ->onFailure(function(){
                    Log::info("sync:cron failed!!!");
                });
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
