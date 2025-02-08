<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ReportsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $rl = [
            ["LOAN COLLECTIONS PER MONTH", "Loan Collections per month", "loan_collections_per_month"],
            ["LOAN COLLECTION", "Loan Collections", "loan_collections"],
            ["DISBURSED LOANS SUMMARY PER MONTH", "disbursement loan summary per month", "loan_disbursement_permonth"],
            ["NON - PERFORMING LOANS", "Non - Performing Loans", "non_performing_loans"],
            ["SMS SUMMARY REPORT", "SMS SUMMARY REPORT	", "sms_summary"],
            ["LOAN OFFICER PERFORMANCE", "LOAN OFFICER PERFORMANCE", "field_agent_performance"],
            ["ROLLED OVER LOANS", "Rolled Over Loans", "rolled_over_loans"],
            ["SYSTEM USERS", "System Users", "systems_users"],
            ["INACTIVE CUSTOMERS", "Inactive Customers", "inactive_customers"],
            ["BLOCKED CUSTOMERS", "Blocked Customers", "blocked_customers"],
            ["OUTSTANDING LOAN BALANCE", "Outstanding Loan Balance", "loans_balance"],
            ["CUSTOMER ACCOUNT STATEMENT", "Customer Account Statement", "customer_account_statement"],
            /*["PAR SUMMARY", "PAR Summary", "par_summary"],*/
            ["MPESA REPAYMENTS", "MPesa Repayments", "mpesa_repayments"],
            ["LOANS PENDING DISBURSEMENT", "Loans Pending Disbursement", "loan_pending_disbursements"],
            ["LOANS PENDING APPROVAL", "Loans Pending Approval", "loan_pending_approval"],
            ["LOANS DUE TODAY", "Loans Due Today", "loan_due_today"],
            ["LOAN ARREARS", "Loan Arrears", "loan_arrears"],
            ["INCOME STATEMENT", "Income Statement", "income_statement"],
            ["DISBURSED LOANS SUMMARY", "Disbursed Loans Summary", "disbursed_loans_summary"],
            ["DISBURSED LOANS", "Disbursed Loans", "disbursed_loans"],
            ["CUSTOMER LISTING", "Customer Listing", "customer_listing"],
            ["CASHFLOW STATEMENT", "Cashflow statement", "cash_flow_statement"],




        ];
        foreach ($rl as $bs) {
            $id2 = \App\models\Report::create([
                'rname' => $bs[0],
                'description' => $bs[1],
                'route' => $bs[2],


            ]);
        }
    }
}
