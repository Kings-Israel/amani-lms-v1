<?php

namespace App\Console\Commands;

use App\Jobs\UpdateCustomerClassification;
use Illuminate\Console\Command;

class UpdateCustomerClassificationCommand extends Command
{
    protected $signature = 'update:customer-classification';

    protected $description = 'Update the customer classification based on loan information';

    public function handle()
    {
        UpdateCustomerClassification::dispatch();

        $this->info('Customer classification updated successfully!');
    }
}
