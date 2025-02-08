<?php

namespace App\Http\Controllers;

use App\Jobs\Sms;
use App\models\CheckOffEmployee;
use App\models\CheckOffEmployeeSms;
use App\models\CheckOffLoan;
use App\models\CheckOffPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Yajra\DataTables\DataTables;

class CheckOffLoanController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return array|false|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View|mixed
     */
    public function index()
    {
        $this->data['title'] = "Check Off Loans";
        $this->data['sub_title'] = "List of all Advance Loans";
        return view('pages.check-off.loans.index', $this->data);
    }

    /**
     * Data Table
     * @return mixed
     * @throws \Exception
     */
    public function data(){
        $lo = CheckOffLoan::query()
            ->with('employee.employer')
            ->with('product:id,name')
            ->select('check_off_loans.*');
        return DataTables::of($lo)
            ->addColumn('full_name', function ($lo) {
                return $lo->employee->first_name . ' ' . $lo->employee->last_name;
            })
            ->addColumn('balance', function ($lo) {
                return $lo->balance;
            })
            ->addColumn('amount_paid', function ($lo) {
                return $lo->amount_paid;
            })
            ->editColumn('approved', function ($lo) {
                if ($lo->approved) {
                    return 'APPROVED';
                }
                else if ($lo->rejected) {
                    return 'REJECTED';
                }
                else {
                    return 'PENDING APPROVAL';
                }
            })
            ->editColumn('settled', function ($lo) {
                if ($lo->settled) {
                    return 'SETTLED';
                }
                else if ($lo->rejected) {
                    return 'REJECTED';
                }
                else {
                    return 'INCOMPLETE';
                }
            })
            ->addColumn('action', function ($lo) {
                if ($lo->approved){
                    $action_buttons = '<div class="btn-group text-center">
                                        <a type="button" class="btn btn-primary" data-toggle="dropdown" aria-expanded="false"><i class="feather icon-settings"></i> </a>
                                            <ul class="dropdown-menu" style=" left: -10em; padding: 1em">
                                                 <li><a href="'.route('check-off.loans.mark_as_settled', $lo->id).'"><i class="feather icon-user-check text-secondary"></i> Mark as Settled </a></li>
                                                 <li><a href="'.route('check-off.loans.loan_payments', encrypt($lo->id)).'"><i class="feather icon-eye text-secondary"></i> List Payments </a></li>
                                          </ul>
                                    </div>';
                }
                else if ($lo->rejected){
                    $action_buttons = '<div class="btn-group text-center">
                                        <a type="button" class="btn btn-primary" data-toggle="dropdown" aria-expanded="false"><i class="feather icon-settings"></i> </a>
                                            <ul class="dropdown-menu" style=" left: -10em; padding: 1em">
                                                <li>
                                                    <a href="#" onclick="event.preventDefault();
                                                    document.getElementById(`delete-form-'. $lo->id .'`).submit();">
                                                    <i class="feather icon-user text-danger" ></i> Delete Loan Request </a>
                                                </li>

                                                <form id="delete-form-'. $lo->id .'" action="'. route('check-off-loans.destroy', $lo->id) .'"
                                                     method="POST" style="display: none;">
                                                    '.csrf_field().'
                                                    <input type="hidden" name="_method" value="DELETE">
                                                </form>
                                            </ul>
                                    </div>';
                }
                else {
                    $action_buttons = '<div class="btn-group text-center">
                                        <a type="button" class="btn btn-primary" data-toggle="dropdown" aria-expanded="false"><i class="feather icon-settings"></i> </a>
                                        <ul class="dropdown-menu" style=" left: -10em; padding: 1em">
                                             <li><a href="'.route('check-off.loans.mark_as_approved', $lo->id).'"><i class="feather icon-user-check text-secondary"></i> Mark as Approved </a></li>
                                             <li><a onclick="return confirm(`Are you sure you want to mark this loan as rejected? Once confirmed this action cannot be undone and a notification will be sent out to the customer.`)"
                                             href="'.route('check-off.loans.mark_as_rejected', $lo->id).'"><i class="feather icon-user-check text-warning"></i> Mark as Rejected </a></li>
                                             <li><a href="#"><i class="feather icon-user-check text-danger" onclick="event.preventDefault();
                                                document.getElementById(`delete-form-'. $lo->id .'`).submit();"></i> Delete Loan Request </a></li>
                                        <form id="delete-form-'. $lo->id .'" action="'. route('check-off-loans.destroy', $lo->id) .'"
                                                 method="POST" style="display: none;">
                                                '.csrf_field().'
                                                <input type="hidden" name="method" value="DELETE">
                                            </form>

                                          </ul>
                                        </div>';
                }
                return $action_buttons;
            })
            ->rawColumns(['action'])
            ->toJson();
    }

    /**
     * @param $checkOffLoanId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function mark_as_settled($checkOffLoanId): \Illuminate\Http\RedirectResponse
    {
        $loan = CheckOffLoan::query()->find($checkOffLoanId);
        $settled = $loan->settled;
        if ($settled){
            return Redirect::back()->with('warning', ' The specified loan ('.$checkOffLoanId.') is already marked as settled.');
        } else {
            $loan->update([
                'settled' => true,
                'settled_at' => now()
            ]);
            return Redirect::back()->with('success',' The specified loan ('.$checkOffLoanId.') has successfully been marked as settled');
        }
    }

    /**
     * @param $checkOffLoanId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function mark_as_approved($checkOffLoanId): \Illuminate\Http\RedirectResponse
    {
        $loan = CheckOffLoan::query()->find($checkOffLoanId);
        $approved = $loan->approved;
        if ($approved){
            return Redirect::back()->with('warning', ' The specified loan ('.$checkOffLoanId.') is already marked as approved.');
        } else {
            $loan->update([
                'approved' => true,
                'approved_by' => auth()->id(),
                'approved_date' => now()
            ]);
            return Redirect::back()->with('success',' The specified loan ('.$checkOffLoanId.') has successfully been marked as approved');
        }
    }

    /**
     * @param $checkOffLoanId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function mark_as_rejected($checkOffLoanId): \Illuminate\Http\RedirectResponse
    {
        $loan = CheckOffLoan::query()->with('employee')->find($checkOffLoanId);
        $approved = $loan->approved;
        if ($approved){
            return Redirect::back()->with('warning', ' The specified loan ('.$checkOffLoanId.') is already marked as approved and can therefore not be rejected.');
        } else {
            $loan->update([
                'rejected' => true,
                'rejected_by' => auth()->id(),
                'rejected_at' => now()
            ]);
            if ($loan->employee){
                //sms customer
                $phone_number = '254' . substr($loan->employee->phone_number, -9);
                $message = "Hello, Thank you for showing interest in our LITSA CREDIT Advance Loans Product. Unfortunately, your submitted loan has failed to pass our verification process and has therefore been rejected. A member of our team will reach out to you for further clarification.";
                CheckOffEmployeeSms::query()->create([
                    'employee_id' => $loan->employee->id,
                    'sms' => $message,
                    'phone_number' => $phone_number
                ]);
                dispatch(new Sms($phone_number, $message, null, true));
            }
            return Redirect::back()->with('success',' The specified loan ('.$checkOffLoanId.') has successfully been marked as rejected');
        }
    }

    /**
     * @param $checkOffLoanId
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    public function destroy($checkOffLoanId): \Illuminate\Http\RedirectResponse
    {
        $loan = CheckOffLoan::query()->with('employee')->find($checkOffLoanId);
        $approved = $loan->approved;
        $disbursed = $loan->disbursed;
        if ($approved){
            return Redirect::back()->with('warning', ' The specified loan ('.$checkOffLoanId.') is already marked as approved and can therefore not be deleted.');
        }
        else if ($disbursed){
            return Redirect::back()->with('warning', ' The specified loan ('.$checkOffLoanId.') is already marked as disbursed and can therefore not be deleted.');
        }
        else {
            $loan->delete();
            return Redirect::back()->with('success',' The specified loan ('.$checkOffLoanId.') has successfully been deleted successfully');
        }
    }

    /**
     * List of all Checkoff Loan Payments
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    public function payment_index(){
        $this->data['title'] = "Check Off Loan Payments";
        $this->data['sub_title'] = "List of all Advance Loan Payments";
        return view('pages.check-off.loans.payments-index', $this->data);
    }

    public function payment_index_data(Request $request){
        $lo = CheckOffPayment::query()
            ->with('employee.employer')
            ->select('check_off_payments.*');
        return DataTables::of($lo)
            ->addColumn('full_name', function ($lo) {
                return $lo->employee->first_name . ' ' . $lo->employee->last_name;
            })
            ->toJson();
    }

    /**
     * List of Checkoff Loan Payments
     *
     * @throws \Exception
     */
    public function loan_payments($loan_identifier){
        $loan_id = decrypt($loan_identifier);
        $loan = CheckOffLoan::query()->findOrFail($loan_id);
        $this->data['loan'] = $loan;
        $this->data['title'] = "Check Off Loan Payments";
        $this->data['sub_title'] = "List of all Loan Payments under Loan: $loan_id";
        return view('pages.check-off.loans.loan-payments', $this->data);
    }

    public function loan_payments_data(Request $request, $loan_identifier){
        $loan_id = decrypt($loan_identifier);
        $lo = CheckOffPayment::query()
            ->where('loan_id', '=', $loan_id)
            ->with('employee.employer')
            ->select('check_off_payments.*');
        return DataTables::of($lo)
            ->addColumn('full_name', function ($lo) {
                return $lo->employee->first_name . ' ' . $lo->employee->last_name;
            })
            ->toJson();
    }
}
