<?php

namespace App\Jobs;

use App\Models\Customer;
use App\Models\Loan;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;  // Add Queueable trait
use Illuminate\Contracts\Queue\ShouldQueue;  // Add ShouldQueue interface if you want to queue the job
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateCustomerClassification implements ShouldQueue  // Implement ShouldQueue if it’s a queued job
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $customers = Customer::all();

        foreach ($customers as $customer) {
            $loans = Loan::where('customer_id', $customer->id)->get();

            $classification = $this->getClassification($loans);

            $customer->update([
                'classification' => $classification,
            ]);
        }
    }

    /**
     * Get the classification for the customer based on their loans.
     *
     * @param \Illuminate\Database\Eloquent\Collection $loans
     * @return string
     */
    private function getClassification($loans)
    {
        $isDefaulter = false;
        $isCleared = true;
        $isSecurityImpounded = false;

        foreach ($loans as $loan) {
            $loanDueDate = Carbon::parse($loan->end_date);
            $loanRepaymentStatus = $loan->settled;
            $loanTotalPaid = $loan->total_amount_paid;
            $loanAmount = $loan->loan_amount;

            // Skip loans that are fully paid
            if ($loanRepaymentStatus == 1 && $loanTotalPaid >= $loanAmount) {
                continue;
            }

            // Check if the loan is overdue
            if ($loanRepaymentStatus == 0 && $loanDueDate->isPast()) {
                $isDefaulter = true;
            }

            // Check for security impound status
            $securityFields = [
                $loan->document_path,
                $loan->audio_path,
                $loan->video_path,
                $loan->customer_id_front,
                $loan->customer_id_back,
                $loan->guarantor_id
            ];

            $filledFields = array_filter($securityFields, function($field) {
                return !is_null($field) && !empty($field);
            });

            if (count($filledFields) >= 2) {
                $isSecurityImpounded = true;
            }

            // Check if the loan is still active
            if ($loanRepaymentStatus == 0) {
                $isCleared = false;
            }
        }

        // Determine classification based on flags
        if ($isDefaulter) {
            return 'Defaulter';
        }

        if ($isSecurityImpounded) {
            return 'security_impounded';
        }

        if ($isCleared) {
            return 'cleared_share';
        }

        return 'Good';
    }
}
