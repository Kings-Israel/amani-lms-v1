<?php

namespace App\Console;

use App\Jobs\UpdateCustomerClassification;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        'App\Console\Commands\CheckInstallments',
        'App\Console\Commands\NotifyScheduler',
        'App\Console\Commands\Ro',
        'App\Console\Commands\SendAutomatedEmails',
        'App\Console\Commands\TodayLoanReminder',

    ];

    protected function schedule(Schedule $schedule)
    {
        $schedule->command('check:installments')
            ->dailyAt('23:50')->timezone('Africa/Nairobi');

        // Send Reminder for loans due today
        // $schedule->command('remind:loan_due_today')
        //     ->dailyAt('07:30')->timezone('Africa/Nairobi');
        // $schedule->command('remind:loan_due_today')
        //     ->dailyAt('18:30')->timezone('Africa/Nairobi');

        $schedule->command('update:Ro')
            ->dailyAt('00:10')->timezone('Africa/Nairobi');

        $schedule->command('update:login_tokens')
            ->timezone('Africa/Nairobi')
            ->everyFiveMinutes();

        // Laravel backup commands
        $schedule->command('backup:clean')
            ->dailyAt('01:00')
            ->timezone('Africa/Nairobi')
            ->withoutOverlapping();

        $schedule->command('backup:run')
            ->dailyAt('00:30')
            ->timezone('Africa/Nairobi')
            ->withoutOverlapping();

        $schedule->command('backup:run')
            ->dailyAt('06:30')
            ->timezone('Africa/Nairobi')
            ->withoutOverlapping();

        $schedule->command('backup:run')
            ->dailyAt('12:30')
            ->timezone('Africa/Nairobi')
            ->withoutOverlapping();

        $schedule->command('backup:run')
            ->dailyAt('18:30')
            ->timezone('Africa/Nairobi')
            ->withoutOverlapping();

        $schedule->command('backup:monitor')
            ->sundays()
            ->at('03:00');

        $schedule->command('update:loans')
            ->timezone('Africa/Nairobi')
            ->everyTenMinutes();

        $schedule->command('dueinstallments:interactions')
            ->timezone('Africa/Nairobi')
            ->mondays()->at('02:00');

        $schedule->command('update:interactions')
            ->dailyAt('02:00')->timezone('Africa/Nairobi');

        // $schedule->command('app:auto-reconcile-payments')
        //     ->dailyAt('20:00')->timezone('Africa/Nairobi');

        $schedule->command('installments:reconcile')->everyThirtyMinutes();
        // $schedule->command('app:resolve-raw-payments')->everyThreeMinutes();

        // Daily Job for UpdateCustomerClassification
        $schedule->job(new UpdateCustomerClassification)->daily();
        $schedule->job(UpdateCustomerClassification::class)->daily();

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

    protected function scheduleTimezone()
    {
        return 'Africa/Nairobi';
    }
}
