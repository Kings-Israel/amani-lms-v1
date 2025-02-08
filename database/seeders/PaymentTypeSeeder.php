<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class PaymentTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //add payment type
        $rl = ["Loan Settlement", "Loan Disbursement", "Processing Fee"];
        foreach ($rl as $bs) {
            $id2 = \App\models\Payment_type::create([
                'name' => $bs,

            ]);
        }
    }
}
