<?php

namespace App\Console\Commands;

use App\CustomerInteractionCategory;
use App\models\Arrear;
use App\models\CustomerInteraction;
use App\models\Installment;
use App\models\Loan;
use App\models\Pre_interaction;
use Carbon\Carbon;
use Illuminate\Console\Command;

class UpdateInteractions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:interactions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $cat = CustomerInteractionCategory::where(['name' => 'Prepayment'])->first();
        $due_cat = CustomerInteractionCategory::where(['name' => 'Due Collection'])->first();
        $ints = CustomerInteraction::where(['interaction_category_id' => $cat->id, 'status' => 1])
            ->orWhere(function ($query) use ($due_cat) {
                $query->where('interaction_category_id', $due_cat->id)
                    ->where('status' , 1);
            })
            ->join('installments', function ($join) {
                $join->on('installments.id', '=', 'customer_interactions.model_id')
                    ->whereDate('installments.due_date', '<', Carbon::now());
            })
            ->select('customer_interactions.*',
                'installments.amount_paid',
                'installments.total',
                'installments.loan_id',
                'installments.due_date')
            ->get();

        foreach ($ints as $int){
            //check if loan is completed
            $loan = Loan::find($int->loan_id);
            $date = Carbon::createFromFormat('Y-m-d', $int->due_date);
            $daysToAdd = 1;
            $date = $date->addDays($daysToAdd);
            if (!$loan->settled){
                //check if total amount has been paid
                $amount = $int->total - $int->amount_paid;
                if($amount > 0){
                    //mark interaction target as failed
                    $int->update(['target' => 2, 'closed_by' => 19, 'closed_date'=> $date, 'status' => 2]);
                } else {
                    $int->update(['target' => 1, 'closed_by' => 19, 'closed_date'=> $date, 'status' => 2]);
                }
            } else {
                //loan has aleady been settled
                $int->update(['target' => 1, 'closed_by' => 19, 'closed_date'=> $date, 'status' => 2]);
            }
        }

        //check arrears interactions
        $arrear_cat = CustomerInteractionCategory::where(['name' => 'Arrear Collection'])->first();

        $pres = CustomerInteraction::where(['interaction_category_id' => $arrear_cat->id, 'status' => 1])->get();

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
            } else {
                // delete pre
                $delete = true;
            }

            if ($delete){
                $pre->update(['status'=>2, 'target' => 1, 'closed_by' => 19, 'closed_date' => Carbon::now() ]);
            }
        }

        //delete arrear pre interaction if arrear has been paid
        $pres = Pre_interaction::where(['interaction_category_id' => $arrear_cat->id])->get();
        foreach ($pres as $pre){
            $delete = false;
            $arrear = Arrear::find($pre->model_id);
            if ($arrear) {
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
            } else {
                // delete pre
                $delete = true;
            }

            if ($delete){
                $pre->delete();
            }
        }

        //if due installment has been paid on time delete due pre interactions
        $prs = Pre_interaction::whereIn('interaction_category_id', [$cat->id, $due_cat->id])->whereDate('due_date', '>=', Carbon::yesterday())->get();
        foreach ($prs as $pr){
            $delet = false;

            //check installment
            $installment = Installment::find($pr->model_id);

            if ($installment){
                //check loan if it has been settled
                $loan = Loan::find($installment->loan_id);
                if ($loan->settled){
                    $delet = true;
                } else{
                   //check if total amount has been paid
                    $amount = $installment->total - $installment->amount_paid;
                    if($amount == 0){
                        //mark interaction target as failed
                        $delet = true;
                    }
                }
            }

            if ($delet){
                $pr->delete();
            }
        }
        return 'done';
    }
}
