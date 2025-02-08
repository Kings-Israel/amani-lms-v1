<?php

namespace Database\Seeders;

use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = ['manager', 'customer_informant', 'accountant', 'field_agent', 'investor', 'admin'];

        foreach ($data as $dt){
            $role = Role::create(['name' => $dt]);

        }
        $user = User::create([
            'name' => 'LMS Admin',
            'email' => 'info@amaniaccess.com',
            'phone' => '254712717844',
            'password' => Hash::make('lms@amani#2025'),
            'branch_id' => 1
        ]);
        $user->assignRole('admin');


        ///add counties
       $counties = ["Mombasa", "Kwale", "Kilifi", "Tana-River", "Lamu", "Taita-Taveta", "Garissa", "Wajir", "Mandera", "Marsabit", "Isiolo", "Meru", "Tharaka-Nithi", "Embu", "Kitui", "Machakos", "Makueni", "Nyandarua", "Nyeri"
                    ,"Kirinyaga", "Muranga", "Kiambu", "Turkana", "West Pokot", "Samburu", "Trans-Nzoia", "Uasin Gishu", "Elgeyo-Marakwet", "Nandi", "Baringo", "Laikipia", "Nakuru", "Narok", "Kajiado", "Kericho", "Bomet", "Kakamega", "Vihiga", "Bungoma"
                    ,"Busia", "Siaya", "Kisumu", "Homa Bay", "Migori", "Kisii", "Nyamira", "Nairobi"
                ];

        foreach ($counties as $dt){
            $role = \App\models\County::create(['cname' => $dt]);
        }

        foreach ($counties as $dt){
            $role = \App\models\Branch::create(['bname' => $dt]);
        }

        //add accounts

        $ac = \App\models\Account::create([
            'aname' => 'Loan Account',

        ]);
        $ac2 = \App\models\Account::create([
            'aname' => 'Saving Account',

        ]);

        //add types of Identification documents in Kenya
        $id = \App\models\Document::insert([
            ['dname' => 'National ID'],
            ['dname' => 'Passport'],
            ['dname' => 'Alien ID'],
            ['dname' => 'Driving License']
        ]);

        // $id1 = \App\models\Document::create([
        //     'dname' => 'Kenyan passport',

        // ]);
        // $id2 = \App\models\Document::create([
        //     'dname' => 'Millitary ID',

        // ]);


        //add business
        // $bus = ["Registered Companies", "Partnership", "Sole Proprietorships", "Societies"];
        // foreach ($bus as $bs){
        //     $id2 = \App\models\Business_type::create([
        //         'bname' => $bs,

        //     ]);
        // }


        // income range
       $incomeRanges = [
           "Below KSh 10,000",
           "KSh 10,000 - 20,000",
           "KSh 20,000 - 30,000",
           "KSh 30,000 - 40,000",
           "KSh 40,000 - 50,000",
           "KSh 50,000 - 60,000",
           "KSh 60,000 - 70,000",
           "KSh 70,000 - 80,000",
           "KSh 80,000 - 90,000",
           "Above KSh 90,000"
        ];

        foreach ($incomeRanges as $range){
            $role = DB::table('income_ranges')->insert( ["name" => $range] );
        }

        // prequalified loan
        for ($i=3000; $i<=20000; $i+=500) {
            $role = DB::table('prequalified_loans')->insert( ["amount" => $i] );
        }



        //add industries
        $industries = [
            "Personal Services" => [
            "Dry Cleaning/Laundry",
            "Tailoring Services",
            "Beauty Salon",
            "Photography",
            "Entertainment, Party & Events Planning",
            "Massage & Fitness Centre",
            "Barber Shop",
            "Cleaning Services"
            ],

            "Real Estate & Housing" => [
                "Contractor, Plumbing & Interior Design",
                "Warehouse & Equipment Rental",
                "Workshop (Wood & Metal)",
                "Concrete/Balast Manufacturing",
                "Real Estate Broker/Agent",
                "House Repair & Maintenance"
            ],

            "Safety/Security & Legal"=>[
                "Security System Services",
                "Legal Services",
                "Security Guard Company"
            ],

            "Transportation"=> [
                "Motor Bike Transportation",
                "Taxi & Rental Services",
                "Boat Services",
                "Towing"
            ],


            "Natural Resource/Environment"=>[
                "Firewood & Charcoal Vendor",
                "Oil & Gas Distribution",
                "Water Vending"
            ],

            "Human Health & Animal Services"=>[
                "Dentistry",
                "Pharmacy/Dispensing Chemist",
                "Agro-vet",
                "Private Health Services"
            ],

            "General Hardware & Electronics"=>[
                "Household Utensils",
                "Building & Construction Material",
                "Electronic Accessory shops/Repairs"
            ],

            "Food & Hospitality"=>[
                "Green Grocery (Fruit/Vegetables)",
                "Food Kiosks",
                "Retail Shop",
                "Guest House/Lodges",
                "Bar/Restaurant",
                "Ice Cream",
                "Hawking - Mobile merchandise services",
                "Caterer",
                "Beverage Manufacturing - Juice etc",
                "Seafood - Fish vendor",
                "Cereals",
                "Meat Vendor - Butchery",
                "Bakery (Bread & Confectionaries)"
            ],

            "Business & Information"=>[
                "Video Production",
                "Travel Agency",
                "Bureu & Publishing Services",
                "Business Consultant (Records keeping)"
            ],

            "Fashion & Beauty Products"=>[
                "Cosmetic Shop",
                "Footware Shop",
                "New Clothes",
                "Second Hand Clothes"
            ],

            "Finance & Insurance"=>[
                "Book Keeping & Collections Agency",
                "Pawn Brokers (Shylock)",
                "Mobile Money Services (M-PESA)",
                "Insurance Services"

            ],

            "Automobile Services"=>[
                "Motor Vehicle/Bike Repair",
                "Automotive Part Sale",
                "Car Wash/Detailing",
                "New Motor Vehicle/Bike Sales"
            ]
        ];

        foreach ($industries as $industry => $businessTypes) {

            $newIndustry = \App\models\Industry::create([
                'iname' => $industry
            ]);

            // create all children (business types)

            foreach ($businessTypes as $idx => $businessType) {

                \App\models\Business_type::create([
                    'bname' => $businessType,
                    'industry_id' => $newIndustry->id
                ]);
            }
        }




        //add relationship
        $rl = ["Father", "Mother", "Son", "Daughter", "Brother", "Sister", "Spouse", "Other"];
        foreach ($rl as $bs) {
            $id2 = \App\models\Relationship::create([
                'rname' => $bs,

            ]);
        }

    }
}
