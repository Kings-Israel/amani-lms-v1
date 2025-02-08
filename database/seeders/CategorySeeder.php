<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $rl = [
            ["name" => "1-30 Days", "days" => "30"],
            ["name" => "31-60 Days", "days" => "60"],
            ["name" => "61-90 Days", "days" => "90"],
            ["name" => "91-120 Days", "days" => "120"],
            ["name" => "121-150 Days", "days" => "150"],
            ["name" => "Over 150 Days", "days" => "151"],



        ];
        DB::table('categories')->insert($rl);
    }
}
