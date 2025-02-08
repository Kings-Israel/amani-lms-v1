<?php

namespace App\Console\Commands;

use App\LoginToken;
use App\models\Activity_otp;
use Carbon\Carbon;
use Illuminate\Console\Command;

class UpdateLoginTokens extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:login_tokens';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Marks expired login tokens as inactive';

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
        $tokens = LoginToken::where('token_expires_at', '<', Carbon::now())->where('active', '=', true)->get();
        if ($tokens){
            foreach ($tokens as $token)
            {
                $token->update([
                    "active"=>false,
                    "in_use"=>false,
                    "updated_at"=>Carbon::now()
                ]);
            }
            //echo "tokens updated";
        }

        $activity_tokens = Activity_otp::where('expire_at', '<', Carbon::now())->where('status', '=', true)->get();
        foreach ($activity_tokens as $t){
            $t->update([
                'status' => false
            ]);
        }
    }
}
