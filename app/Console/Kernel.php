<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $env = config('app.env');
        $email = config('mail.username');

        if ($env === 'live') {
            //Scheduling backup, specify the time when the backup will get cleaned & time when it will run.
            
            $schedule->command('backup:clean')->daily()->at('01:00');
            $schedule->command('backup:run')->daily()->at('01:30');


            //Schedule to create recurring invoices
            $schedule->command('pos:generateSubscriptionInvoices')->dailyAt('23:30');
            $schedule->command('pos:updateRewardPoints')->dailyAt('23:45');

            $schedule->command('pos:autoSendPaymentReminder')->dailyAt('8:00');

            // ======== ATTENDANCE & PAYROLL AUTOMATION ========
            
            // Process attendance files every morning at 6:00 AM
            $schedule->command('attendance:process-files')
                     ->dailyAt('06:00')
                     ->emailOutputOnFailure($email ?: 'admin@interrandadmin.com')
                     ->withoutOverlapping()
                     ->runInBackground();

            // Generate payroll reports on the first day of each month at 8:00 AM
            $schedule->command('attendance:generate-payroll --send-reports')
                     ->monthlyOn(1, '08:00')
                     ->emailOutputOnFailure($email ?: 'admin@interrandadmin.com')
                     ->withoutOverlapping()
                     ->runInBackground();

            // Weekly attendance summary report every Monday at 9:00 AM
            $schedule->command('attendance:process-files --dry-run')
                     ->weeklyOn(1, '09:00')
                     ->emailOutputTo($email ?: 'admin@interrandadmin.com')
                     ->withoutOverlapping();

            // Backup/archive attendance data monthly on the 2nd at 2:00 AM
            $schedule->command('attendance:process-files --archive-only')
                     ->monthlyOn(2, '02:00')
                     ->emailOutputOnFailure($email ?: 'admin@interrandadmin.com')
                     ->withoutOverlapping();

        }

        if ($env === 'demo') {
            //IMPORTANT NOTE: This command will delete all business details and create dummy business, run only in demo server.
            $schedule->command('pos:dummyBusiness')
                    ->cron('0 */3 * * *')
                    //->everyThirtyMinutes()
                    ->emailOutputTo($email);
        }
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}
