<?php

namespace Database\Seeders;

use App\models\Setting;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(UsersTableSeeder::class);
        $this->call(PaymentTypeSeeder::class);
        $this->call(ReportsSeeder::class);
        $this->call(ExpenseTypesseeder::class);
        $this->call(MsettingsTableSeeder::class);
        $this->call(CategorySeeder::class);
        $this->call(CustomerInteractionTypeSeeder::class);

        Setting::create([
            'registration_fee' => 1000,
            'loan_processing_fee' => 500,
            'rollover_interest' => 2,
            'lp_fee' => 1000
        ]);
    }
}
