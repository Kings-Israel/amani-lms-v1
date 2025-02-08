<?php

use App\models\Arrear;
use App\models\Customer;
use App\models\CustomerInteraction;
use App\models\CustomerInteractionCategory;
use App\models\Installment;
use App\models\Loan;
use App\models\Pre_interaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

function delete_preinteractions_duplicate(){
       $cat = CustomerInteractionCategory::where(['name' => 'Arrear Collection'])->first();


       $duplicates = DB::table('pre_interactions') // replace table by the table name where you want to search for duplicated values
       ->where(['interaction_category_id' => $cat->id])

           ->select('id', 'model_id') // name is the column name with duplicated values
       ->whereIn('model_id', function ($q){
           $q->select('model_id')
               ->from('pre_interactions')
               ->groupBy('model_id')
               ->havingRaw('COUNT(*) > 1');
       })
           ->orderBy('model_id')
           ->orderBy('id', 'desc') // keep smaller id (older), to keep biggest id (younger) replace with this ->orderBy('id', 'desc')
           ->get();
       $value = "";
       $i = 0;



       foreach ($duplicates as $duplicate) {
           if($duplicate->model_id === $value)
           {
               $i = $i + 1;
               DB::table('pre_interactions')->where('id', $duplicate->id)->delete(); // comment out this line the first time to check what will be deleted and keeped
               echo "$duplicate->model_id with id $duplicate->id deleted! \n";
           }
           else
               echo "$duplicate->model_id with id $duplicate->id keeped \n";
           $value = $duplicate->model_id;
       }
}

function new_arrears_addition(){
    $cat = \App\CustomerInteractionCategory::where(['name' => 'Arrear Collection'])->first();
    $arrears = Arrear::where('amount','>', 0)
        ->join('loans', function ($join) {
            $join->on('loans.id', '=', 'arrears.loan_id')
                ->where('loans.settled', false);

        })
        ->select('arrears.*')
        ->get();
    $i = 0;
    foreach ($arrears as $arrear) {


        if ($arrear->last_payment_date > Carbon::now()->subDays(180) || $arrear->created_at > Carbon::now()->subDays(180)){
            //if ($arrear->last_payment_date > Carbon::now()->subDays(180) ){
            //if ($arrear->created_at > Carbon::now()->subDays(180) ){


            //check if loan has been paid
            $loan = Loan::find($arrear->loan_id);
            if (!$loan->settled){

                //check if the arrear is in the interaction
                $interaction = CustomerInteraction::where(['interaction_category_id' => $cat->id, 'model_id' =>$arrear->id])->first();
                if (!$interaction){
                    // $i = $i+1;


                    //add interaction
                    //check if its in the pre_interaction
                    $pre = Pre_interaction::where(['model_id' => $arrear->id,  'interaction_category_id' => $cat->id])->first();
                    if (!$pre){
                        $i = $i+1;

                        Pre_interaction::insert([
                            'model_id' => $arrear->id,
                            'amount' => $arrear->amount,
                            'customer_id' => $loan->customer_id,
                            'interaction_category_id' => $cat->id,
                            'due_date' => $arrear->installment->due_date,
                            'system_remark' => 'Kes ' . $arrear->amount . ' was due on ' . $arrear->installment->due_date . ' for loan number ' . $arrear->loan->loan_account,
                            'created_at' => Carbon::now()
                        ]);
                    }



                }
            }

        }


    }
    dd($i);


}

function handling_mixup(){
    /*  $cats = CustomerInteractionCategory::whereIn('id' , [1, 5, 6])->get();
        foreach ($cats as $cat){
            $ints = CustomerInteraction::where(['interaction_category_id' => $cat->id, 'target' => 2])->get();
           // dd($ints);
            foreach ($ints as $int){
                $int->update(['target' => 1]);


            }

        }
        dd('dome');*/
    $i = 0;
    $d = 0;
    $ar = array();

    $interactions = CustomerInteraction::whereIn('interaction_category_id', ['2', '3'])->get();
    foreach ($interactions as $interaction){
        $installment = Installment::find($interaction->model_id);
        $customer = $installment->loan->customer_id;
        if($customer != $interaction->customer_id){

            $i = $i + 1;
            //check if there is arrear with the same model id
            $arrear = Arrear::find($interaction->model_id);
            if ($arrear){
                if($arrear->loan->customer_id == $interaction->customer_id){
                    // $i = $i + 1;

                    $interaction->update(['interaction_category_id' => 4]);

                }

            } else{
                $interaction->update(['interaction_category_id' => 4, 'target' => 1, 'status' => 2]);

                $d = $d + 1;
                array_push($ar, $interaction->model_id);
            }
        }

    }
    dd($i, $d, $ar);

}

function check_arrear_pre_interaction_if_paid(){
    $pres = Pre_interaction::where(['interaction_category_id' => 4])->get();
    $paid = 0;
    $non_p = 0;
    foreach ($pres as $pre){
        $delete = false;
        $arrear = Arrear::find($pre->model_id);
        if ($arrear){

            if ($arrear->amount > 1){
                //check is loan has been cleared
                $loan = Loan::find($arrear->loan_id);
                if ($loan->settled){
                    //pre delete
                    $delete = true;
                }

            } else{
                // delete pre
                $delete = true;

            }

        }else{
            // delete pre
            $delete = true;


        }

        if ($delete){
            $paid = $paid + 1;
            // $pre->delete();
            $delete = false;
        } else{
            $non_p = $non_p + 1;
        }
    }

    dd($paid, $non_p, $pres->count());
}

function check_arrear_interaction_if_paid(){
    $pres = CustomerInteraction::where(['interaction_category_id' => 4, 'status' => 1])->get();

    $paid = 0;
    $non_p = 0;
    $ids = array();
    foreach ($pres as $pre){
        $delete = false;
        $arrear = Arrear::find($pre->model_id);
        if ($arrear){

            if ($arrear->amount > 0){
                //check is loan has been cleared
                $loan = Loan::find($arrear->loan_id);
                if ($loan->settled){

                    //pre delete
                    $delete = true;
                }

            } else{
                // delete pre
                $delete = true;

            }

        }else{
            // delete pre
            $delete = true;


        }

        if ($delete){
            array_push($ids, $pre->id);
            $paid = $paid + 1;
            $pre->update(['status'=>2, 'target' => 1, 'closed_by' => 19, 'closed_date' => Carbon::now() ]);
            // $pre->delete();
            $delete = false;
        } else{
            $non_p = $non_p + 1;
        }
    }
}


function update_pre_qualified_amount(){
    $customers = Customer::where(['status' => 1])->get();
    foreach ($customers as $customer){
        $loan = Loan::where(['customer_id' => $customer->id, 'disbursed' => true])->orderBy('id', 'desc')->first();
        //dd($loan);
        if ($loan){
            $customer->update(['prequalified_amount' => $loan->loan_amount, 'previous_prequalified_amount'=> $customer->prequalified_amount]);
        }

    }
}
