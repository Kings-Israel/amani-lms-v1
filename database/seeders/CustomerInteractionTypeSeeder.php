<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class CustomerInteractionTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $types = ["Physical Visit", "Phone Call", "Text/Whatsapp Conversation", "Office Visit"];
        foreach ($types as $type) {
            \App\models\CustomerInteractionType::query()->updateOrCreate([
                'name' => $type
            ], [
                'updated_at' => now(),
                ]
            );
        }

        $categories = [
            "Customer Satisfaction survey",
            "Prepayment",
            "Due Collection",
            "Arrear Collection",
            "First Visit Lo",
            "First Visit Co",


        ];
        foreach ($categories as $category) {
           \App\models\CustomerInteractionCategory::query()->updateOrCreate([
                'name' => $category
            ]
            );
        }
    }
}
