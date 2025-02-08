<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class MsettingsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\models\Msetting::create([
            'paybill' => encrypt('gff'),
            'SecurityCredential' =>encrypt('fg/7Potw0htQhNMfJ1UWszaPsIvuPDixEt5RHfP0dF6cmLR+IWh0WF/prTs/b01YapOmKs2yL++E44SQSgOWA80e7cHiFN6i7G9+KVMhj8nAneSefRnntwt2AgAmtmU0O9y9joC3EywUKqi5H5u7ew/Zm1zFRcI0trPIlkL+RzO5Q45cf9tUOEkkpvj5AsA6eb+Ww+lvUoDsv2brHyN9W1+UiTvF6VYLiEDfAG+FADDFPWATfq6wlApchL42ccee3Se0nViKcesF8b9CpziYtyQhB5mn31MkDGxzhahxBaN+UylkZiTj/Wl89UqDcP+QWJbKr55Uw=='),
            'InitiatorName' => encrypt('df'),
            'Consumer_Key' => encrypt('df'),
            'Consumer_Secret' => encrypt('fd'),
            'MMF_balance' => "Working Account|KES|0.00|0.00|0.00|0.00",
            'Utility_balance' => "Utility Account|KES|0.00|0.00|0.00|0.00",
            'last_updated' => \Carbon\Carbon::now(),

        ]);
    }
}
