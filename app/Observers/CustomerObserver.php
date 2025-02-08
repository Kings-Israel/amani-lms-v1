<?php

namespace App\Observers;

use App\models\Customer;
use App\Jobs\Sms;
use App\models\Branch;
use Log;

class CustomerObserver
{
    public function created(Customer $customer)
    {
        // Send sms on registration
        $message = 'Dear %customer_name%
                    You have been registered on LITSA CREDIT.
                    Looking forward to serving you soon.';

        $message = str_replace('%customer_name%', strtoupper($customer->fname),  $message);

        $branch_id = Branch::whereIn('bname', ['Bungoma', 'Kakamega', 'Homabay', 'Siaya', 'Busia', 'Migori'])->get()->pluck('id');
        if (collect($branch_id)->contains($customer->branch_id) && $customer->field_agent_id != 252) {
            dispatch(new Sms(
                $customer->phone, $message, $customer, false
            ));
        }
    }
}
