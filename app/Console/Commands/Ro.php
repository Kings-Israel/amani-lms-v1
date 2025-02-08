<?php

namespace App\Console\Commands;

use App\models\RoTarget;
use App\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class Ro extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:Ro';

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
       // Log::info('run');
        $lo = User::role('field_agent')->where('status','=',true)->get();
        foreach ($lo as $l ){
            /*$rotarget  = RoTarget::where('user_id', $l->id)->whereMonth('date', Carbon::now())->whereYear('date', Carbon::now())->first();
            $due = $l->installmets_due(Carbon::now());
            if ($rotarget){
                if ($due > 0){
                    $rotarget->update(['collection_target' =>  $rotarget->collection_target + $due]);
                }
            }
            else{
                if ($due > 0) {
                    RoTarget::create([
                        'user_id' => $l->id,
                        'disbursement_target' => 0,
                        'collection_target' => $due,
                        'date' => Carbon::now(),
                    ]);
                }
            }*/
            $rotarget  = RoTarget::where('user_id', $l->id)->whereMonth('date', Carbon::now())->whereYear('date', Carbon::now())->first();
            $due = $l->installmets_due(Carbon::now());
            if ($rotarget){
                if ($due > 0){
                    RoTarget::create([
                        'user_id' => $l->id,
                        'disbursement_target' => $rotarget->disbursement_target,
                        'collection_target' => $due,
                        'date' => Carbon::now(),
                    ]);
                }
            }
            else{
                if ($due > 0) {
                    RoTarget::create([
                        'user_id' => $l->id,
                        'disbursement_target' => 0,
                        'collection_target' => $due,
                        'date' => Carbon::now(),
                    ]);
                }
            }
        }
    }
}
