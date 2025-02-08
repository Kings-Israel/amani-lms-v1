<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ExpenseTypesseeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //add payment type
        $rl = ["RO salaries", "Investors withdrawal", "Investors Interest Settlement", "miscelleneous","tax payments"];
        foreach ($rl as $bs) {
            $id2 = \App\models\Expense_type::create([
                'expense_name' => $bs,

            ]);
        }
    }
}
