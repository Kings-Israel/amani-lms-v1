<?php

namespace App\Console\Commands;

use App\CustomerInteractionCategory;
use App\models\Installment;
use App\models\Pre_interaction;
use Carbon\Carbon;
use Illuminate\Console\Command;

class DueInstallmentsInteractions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dueinstallments:interactions';

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
        //$instalments = Installment::whereBetween('due_date', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->where(['completed' => 0])->get();
        $instalments = Installment::whereBetween('due_date', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->where(['completed' => 0])
            ->join('loans', function ($join) {
                $join->on('loans.id', '=', 'installments.loan_id')
                    ->where('loans.settled', '=', false);

            })->select('installments.*')->get();
        $category = CustomerInteractionCategory::where(['name' => 'Prepayment'])->first();


        foreach ($instalments as $instalment){
            $amount = $instalment->total - $instalment->amount_paid;
            Pre_interaction::insert([
                'model_id' => $instalment->id,
                'amount' => $amount,
                'customer_id' => $instalment->loan->customer_id,
                'interaction_category_id' => $category->id,
                'due_date' => $instalment->due_date,
                'system_remark' =>'Kes '. $amount.' is due on '.$instalment->due_date. ' for loan number '.$instalment->loan->loan_account,
                'created_at' => Carbon::now()
            ]);
        }
    }
}
