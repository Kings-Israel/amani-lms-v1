<?php

namespace App\Console\Commands;

use App\Jobs\AutoReconcileTransactions;
use Illuminate\Console\Command;

class AutoReconcilePayments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:auto-reconcile-payments';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto reconcile payments';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        AutoReconcileTransactions::dispatch();
    }
}
