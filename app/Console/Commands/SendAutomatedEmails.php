<?php

namespace App\Console\Commands;

use App\Jobs\AutomatedEmail;
use App\Jobs\ScoreSheetEmail;
use App\models\Branch;
use App\models\Loan;
use App\User;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

class SendAutomatedEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:automated_emails';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sends automated email reports to the relevant system users';

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
        $recipients = User::where('is_recipient', true)->get();
        foreach ($recipients as $recipient)
        {
            // skipped payments report
            if ($recipient->hasRole('admin') || $recipient->hasRole('accountant')){
                $loans_w_arrears = Loan::whereHas('arrears', function (Builder $builder){
                    $builder->where('amount', '!=',0);
                })->where(['disbursed' => true, 'settled' => false, 'rolled_over'=>false])->orderBy('id', 'desc')->get();
            }elseif ($recipient->hasRole('field_agent')){
                $loans_w_arrears = $recipient->loans()->whereHas('arrears', function (Builder $builder){
                    $builder->where('amount', '!=',0);
                })->where(['disbursed' => true, 'settled' => false, 'rolled_over'=>false])->orderBy('id', 'desc')->get();
            }else{
                $recipient_branch = Branch::find($recipient->branch_id);
                $loans_w_arrears = $recipient_branch->loans()->whereHas('arrears', function (Builder $builder){
                    $builder->where('amount', '!=',0);
                })->where(['disbursed' => true, 'settled' => false, 'rolled_over'=>false])->orderBy('id', 'desc')->get();
            }

            dispatch(new AutomatedEmail($loans_w_arrears, $recipient));

            //performance tracker report
//            if ($recipient->hasRole('admin') || $recipient->hasRole('accountant')) {
//                $users = User::query()->role('field_agent')->where('status', true)->get();
//                $branches = Branch::all();
//            }elseif ($recipient->hasRole('field_agent')){
//                $users = User::query()->role('field_agent')->where(['id'=>$recipient->id , 'status'=> true])->get();
//                $branches = Branch::where('id', $recipient->id)->get();
//            }else{
//                $branches = Branch::where('id', $recipient->id)->get();
//                $users = User::query()->role('field_agent')->where(['branch_id'=>$recipient->branch_id , 'status'=> true])->get();
//            }

            $users = User::query()->role('field_agent')->where('status', true)->get();
            $branches = Branch::all();
            dispatch(new ScoreSheetEmail($users, $branches, $recipient));
        }


//        echo "Email jobs have been scheduled. \n";
    }
}
