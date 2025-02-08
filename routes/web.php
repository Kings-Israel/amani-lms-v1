<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


use App\Jobs\Sms;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Route;

Route::get('/', ['middleware' => 'guest', function () {
    return view('auth.login');
}]);

Route::get('/api_test', function () {
    $ses = Session::get("otp_session");
    return $ses;
});

Route::group(['middleware' => 'guest', 'prefix' => 'advance-loans'], function () {
    Route::get('/verify', 'CheckOffEmployeeController@verify')->name('check-off.employee.verify');
    Route::post('/verify', 'CheckOffEmployeeController@verify_post')->name('check-off.employee.verify_post');
    Route::get('/register/{code}', 'CheckOffEmployeeController@register')->name('check-off.employee.register');
    Route::post('/register/{code}', 'CheckOffEmployeeController@register_post')->name('check-off.employee.register_post');
});

Route::group(['middleware' => 'guest', 'prefix' => 'customer-reapplications'], function () {
    Route::get('/prompt-verification', 'LoanReapplicationController@prompt_verification')->name('customer-reapplications.prompt-verification');
    Route::post('/prompt-verification', 'LoanReapplicationController@prompt_verification_post')->name('customer-reapplications.prompt_verification_post');

    Route::get('/verify/{code}', 'LoanReapplicationController@verify')->name('customer-reapplications.verify');
    Route::post('/verify', 'LoanReapplicationController@verify_post')->name('customer-reapplications.verify_post');

    Route::get('/create-application/{customer_id_number}', 'LoanReapplicationController@application')->name('customer-reapplications.application');
    Route::post('/submit-application/{customer_identifier}', 'LoanReapplicationController@submit_application')->name('customer-reapplications.submit_application');
    Route::get('/close-application', 'LoanReapplicationController@close_application')->name('customer-reapplications.close_application');
});


Route::get('/artisan-storage-link', ['middleware' => 'guest', function () {
    \Illuminate\Support\Facades\Artisan::call('storage:link');
}]);

//Route::get('/download-skipped-payments-report', 'EmailedReportsController@skipped_payments_report');
Route::get('/download-skipped-payments-report', function () {
    return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\LoansExport, 'skipped-payments.xlsx');
});

Route::get('/download-skipped-payments-pdf/{recipientID}', 'EmailedReportsController@skipped_payments_pdf')->name('skipped_payments_pdf');
Route::get('/perf_tracker', 'EmailedReportsController@perf_tracker');
Route::get('/test-pdf', 'EmailedReportsController@testpdf');

Auth::routes();

Route::group(['middleware' => 'auth'], function () {
    //2fa
    Route::get('/2fa', 'Auth\TokenController@show2faForm')->name('2fa');
    Route::post('/2fa', 'Auth\TokenController@verifyToken')->name('post.2fa');
    Route::get('/2fa/logout', 'Auth\TokenController@logout')->name('logout.2fa');
    Route::get('/2fa/resend', 'Auth\TokenController@resend')->name('resend.2fa');

    //2fa middleware
    // Route::group(['middleware' => 'two_factor_auth'], function () {
        //use this route for manual update of changes in the db
        Route::get('/changes', 'AdminController@changes')->name('changes');

        //customers transfers

        Route::get('/home', 'HomeController@index')->name('home');
        // Route::get('/home', 'RegistryController@index')->name('home');

        Route::post('/home', 'HomeController@index')->name('home.post');

        Route::get('/test-notif', 'HomeController@test_notification_scheduler')->name('home.test_notification_scheduler');

        //added loan officer filter on the dashboard
        Route::get('/home/field_agent_filter', 'HomeController@field_agent_filter')->name('home.get.loan.officer.filter');
        Route::post('/home/field_agent_filter', 'HomeController@field_agent_filter')->name('home.post.loan.officer.filter');

        // Route::get('/loans_chart', 'HomeController@getMonthlyLoanDisbursementData')->name('loan.disbursement.chart.data');

        Route::get('/home/monthly-disbursement-filter', 'HomeController@disbursedLoansFilter')->name('home.disb_filter');
        Route::post('/home/monthly-disbursement-filter', 'HomeController@disbursedLoansFilter')->name('home.post.disb_filter');


        Route::get('/home/monthly-loan-repayments-filter', 'HomeController@loanRepaymentsFilter')->name('home.repayments_filter');
        Route::post('/home/monthly-loan-repayments-filter', 'HomeController@loanRepaymentsFilter')->name('home.post.repayments_filter');

        /************************Loan Officers routes*********************/
        // Route::resource('field_agent', 'LoanOfficerController');
        Route::get('/field_agent', 'LoanOfficerController@index')->name('field_agent.index');
        Route::get('/field_agent/create', 'LoanOfficerController@create')->name('field_agent.create');
        Route::post('/field_agent/store', 'LoanOfficerController@store')->name('field_agent.store');
        Route::get('/field_agent/{id}/edit', 'LoanOfficerController@edit')->name('field_agent.edit');
        Route::get('/field_agent/{id}/show', 'LoanOfficerController@show')->name('field_agent.show');
        Route::put('/field_agent/{id}/update', 'LoanOfficerController@update')->name('field_agent.update');
        Route::delete('/field_agent/{id}/delete', 'LoanOfficerController@destroy')->name('field_agent.destroy');
        Route::get('/field_agent_data', 'LoanOfficerController@data')->name('field_agent.data');
        Route::get('/branch_ros/{id}', 'LoanOfficerController@branch_ros')->name('branch_ros');
        Route::get('/del/{id}', 'LoanOfficerController@del')->name('field_agent.del');

        Route::get('field_agent_collection_report', 'ReportController@field_agents_collection_report')->name('home.field_agent_collection_report');

        /************************collection Officers routes*********************/
        // Route::resource('collection_officer', 'CollectionOfficerController');
        Route::get('/collection_officer', 'CollectionOfficerController@index')->name('collection_officer.index');
        Route::get('/collection_officer/create', 'CollectionOfficerController@create')->name('collection_officer.create');
        Route::post('/collection_officer/create', 'CollectionOfficerController@store')->name('collection_officer.store');
        Route::get('/collection_officer/{id}/edit', 'CollectionOfficerController@store')->name('collection_officer.edit');
        Route::put('/collection_officer/{id}/update', 'CollectionOfficerController@update')->name('collection_officer.update');
        Route::delete('/collection_officer/{id}/delete', 'CollectionOfficerController@destroy')->name('collection_officer.destroy');
        Route::get('/collection_officer_data', 'CollectionOfficerController@data')->name('Collection_Officer.data');

        /************************Branches routes*********************/
        // Route::resource('branches', 'BranchesController');
        Route::get('/branches', 'BranchesController@index')->name('branches.index');
        Route::get('/branches/create', 'BranchesController@create')->name('branches.create');
        Route::post('/branches/create', 'BranchesController@store')->name('branches.store');
        Route::get('/branches/{idi}/show', 'BranchesController@show')->name('branches.show');
        Route::get('/branches/{id}/edit', 'BranchesController@store')->name('branches.edit');
        Route::put('/branches/{id}/update', 'BranchesController@update')->name('branches.update');
        Route::delete('/branches/{id}/delete', 'BranchesController@destroy')->name('branches.destroy');
        Route::get('/branch_data', 'BranchesController@data')->name('branch.data');

        /************************Registry routes*********************/
        // Route::resource('registry', 'RegistryController');
        Route::get('/registry', 'RegistryController@index')->name('registry.index');
        Route::get('/registry/blocked', 'RegistryController@blocked')->name('registry.blocked');

        Route::get('/registry/pending', 'RegistryController@pending')->name('registry.pending');
        Route::post('/registry', 'RegistryController@store')->name('registry.store');
        Route::get('/registry/create', 'RegistryController@create')->name('registry.create');
        // Route::post('/registry/store', 'RegistryController@store')->name('registry.store');
        Route::get('/registry/{id}/show', 'RegistryController@show')->name('registry.show');
        Route::get('/registry/{id}/edit', 'RegistryController@edit')->name('registry.edit');
        Route::get('/registry/{id}/classification', 'RegistryController@classification')->name('registry.classification');
        Route::post('/registry/{id}/update-classification', 'RegistryController@updateClassification')->name('registry.updateClassification');

        Route::get('/registry/imports', 'RegistryController@registryImports')->name('registry.import');

        Route::post('/registry/import/data', 'RegistryController@importData')->name('import.registry.data');

        Route::get('/user/registry/{id}/edit', 'RegistryController@edits')->name('registrys.edit');

        Route::put('/registry/{id}/update', 'RegistryController@update')->name('registry.update');
        Route::delete('/registry/{id}/delete', 'RegistryController@destroy')->name('registry.destroy');
        Route::post('registry/{id}/block', 'RegistryController@block')->name('registry.block')->middleware('role:admin|accountant|field_agent|manager|sector_manager');
        Route::post('registry/{id}/unblock', 'RegistryController@unblock')->name('registry.unblock')->middleware('role:admin|accountant|field_agent|manager|sector_manager');

        Route::get('registry-update-customer-co', 'RegistryController@getCreditOfficers')->name('registry.changeCreditOfficer')->middleware('role:admin|sector_manager');
        Route::get('registry-update-customer-co/{id}', 'RegistryController@selectedCO')->name('selectedCO')->middleware('role:admin|sector_manager');
        Route::get('update-co-customer-data/{credOfficer}', 'RegistryController@changeCOCustomerData')->name('changeCOCustomerData')->middleware('role:admin|sector_manager');
        Route::get('get-co', 'RegistryController@changeCOData')->name('changeCOData')->middleware('role:admin|sector_manager');
        Route::post('post-update-co', 'RegistryController@post_update_co')->name('post_update_co')->middleware('role:admin|sector_manager');

        Route::get('prequalified-loan-amount-adjustment', 'RegistryController@preq_amt_adjustment')->name('preq_amt_adjustment')->middleware('role:admin|sector_manager');
        Route::get('approve_preq_amt_adjustment/{id}', 'RegistryController@approve_preq_amt_adjustment')->name('approve_preq_amt_adjustment')->middleware('role:admin|sector_manager');
        Route::get('preq_amt_adjustment_data', 'RegistryController@preq_amt_adjustment_data')->name('preq_amt_adjustment_data')->middleware('role:admin|sector_manager');

        Route::prefix("registry-data")->group(function () {
            Route::get('registry-data', 'RegistryController@ajaxData')->name('registry.ajax-data');
            Route::get('/blocked-customers', 'RegistryController@blockedCustomers')->name('blocked.customers');
            Route::get('registry-pending-data', 'RegistryController@ajaxPendingData')->name('registry.pending.ajax-data');
            Route::get('relationship-officers', 'RegistryController@relationshipOfficers')->name('registry.relationship-officers');
            Route::get('supervisors', 'RegistryController@supervisors')->name('registry.supervisors');
            Route::get('id-types', 'RegistryController@idTypes')->name('registry.id-types');
            Route::get('kin-relations', 'RegistryController@kinRelations')->name('registry.kin-relations');
            Route::get('guarantors', 'RegistryController@guarantors')->name('registry.guarantors');
            Route::get('counties', 'RegistryController@counties')->name('registry.counties');
            Route::get('industries', 'RegistryController@industries')->name('registry.industries');
            Route::get('business-types', 'RegistryController@businessTypes')->name('registry.business-types');
            Route::get('loan-products', 'RegistryController@loanProducts')->name('registry.loan-products');
            Route::get('loan-types', 'RegistryController@loanTypes')->name('registry.loan-types');
            Route::get('income-ranges', 'RegistryController@incomeRanges')->name('registry.income-ranges');
            Route::get('prequalified-amount', 'RegistryController@prequalifiedAmount')->name('registry.prequalified-amount');
            Route::get('accounts', 'RegistryController@accounts')->name('registry.accounts');
            Route::get('customer-personal-details/{id}', 'RegistryController@customerPersonalDetails');
            Route::get('customer-location-details/{id}', 'RegistryController@customerLocationDetails');
            Route::get('customer-profession-details/{id}', 'RegistryController@customerProfessionDetails');
            Route::get('customer-account-details/{id}', 'RegistryController@customerAccountDetails');

            Route::get('unique-phone-number/{phone_number}', 'RegistryController@uniquePhoneNumber');
            Route::get('unique-id-number/{id_number}', 'RegistryController@uniqueIdNumber');

            Route::get('prospects', 'ProspectController@index')->name('prospects');
            Route::get('prospects_create', 'ProspectController@create')->name('prospects.create');
            Route::get('prospect_delete/{id}', 'ProspectController@delete')->name('prospect.delete');

            Route::get('prospects_get_template', 'ProspectController@get_template')->name('prospects.get_template');
            Route::post('prospects_post_template', 'ProspectController@post_template')->name('prospects.post_template');
            Route::get('prospects_data', 'ProspectController@prospects_data')->name('prospects.data');
            Route::get('prospects_sms', 'ProspectController@prospects_sms')->name('prospects_sms');
            Route::post('sms_selected', 'ProspectController@prospects_sms')->name('sms_selected');
            Route::post('delete_selected', 'ProspectController@delete_selected')->name('delete_selected');
            Route::post('prospect_sms_post', 'ProspectController@prospect_sms_post')->name('prospect_sms_post');
            Route::get('delete_all_prospects', 'ProspectController@delete_all_prospects')->name('delete_all_prospects');

            Route::get('customers_sms', 'RegistryController@customers_sms')->name('customers_sms')->middleware('role:admin|sector_manager');
            Route::post('customers_sms_post', 'RegistryController@customers_sms_post')->name('customers_sms_post')->middleware('role:admin|sector_manager');
            Route::get('single_customer_sms', 'RegistryController@single_customer_sms')->name('single_customer_sms')->middleware('role:admin|sector_manager');
            Route::post('single_customer_sms_post', 'RegistryController@single_customer_sms_post')->name('single_customer_sms_post')->middleware('role:admin|sector_manager');
            Route::get('guarantors_sms', 'GuarantorController@guarantors_sms')->name('guarantors_sms')->middleware('role:admin|sector_manager');;
        }
        );

        /************************guarantors routes*********************/
        // Route::resource('guarantors', 'GuarantorController');
        Route::get('/guarantors', 'GuarantorController@index')->name('guarantors.index');
        Route::get('/guarantors/create', 'GuarantorController@create')->name('guarantors.create');
        Route::post('/guarantors/create', 'GuarantorController@store')->name('guarantors.store');
        Route::get('/guarantors/{id}/show', 'GuarantorController@show')->name('guarantors.show');
        Route::get('/guarantors/{id}/edit', 'GuarantorController@edit')->name('guarantors.edit');
        Route::put('/guarantors/{id}/update', 'GuarantorController@update')->name('guarantors.update');
        Route::delete('/guarantors/{id}/delete', 'GuarantorController@destroy')->name('guarantors.destroy');
        Route::get('/guarantors_data', 'GuarantorController@data')->name('guarantors.data');

               /************************referees routes*********************/
        // Route::resource('referees', 'RefereeController');
        Route::get('/referees', 'RefereeController@index')->name('referee.index');
        Route::get('/referees/create', 'RefereeController@create')->name('referee.create');
        Route::post('/referees/create', 'RefereeController@store')->name('referee.store');
        Route::get('/referees/{id}/show', 'RefereeController@show')->name('referee.show');
        Route::get('/referees/{id}/edit', 'RefereeController@edit')->name('referee.edit');
        Route::put('/referees/{id}/update', 'RefereeController@update')->name('referee.update');
        Route::delete('/referees/{id}/delete', 'RefereeController@destroy')->name('referee.destroy');
        Route::get('/referees_data', 'RefereeController@data')->name('referees.data');
        Route::get('referees_sms', 'RefereeController@referees_sms')->name('referee_sms')->middleware('role:admin|sector_manager');;

        /*************************Kin relationshi controller************************/
        Route::resource('kin', 'KinController');
        Route::get('/kinrelationship_data', 'KinController@data')->name('kin.data');

        /*************************Employers route************************/
        Route::resource('employers', 'EmployerController');
        Route::get('/employers_data', 'EmployerController@data')->name('employers.data');
        Route::get('/employer_view/{id}', 'EmployerController@view')->name('employers.view');

        /**************************admin module****************************/
        Route::group(['middleware' => ['role:admin|sector_manager']], function () {
            // Route::resource('admin', 'AdminController', ['except' => 'destroy']);
            Route::get('/admin', 'AdminController@index')->name('admin.index');
            Route::get('/admin/create', 'AdminController@create')->name('admin.create');
            Route::post('/admin/store', 'AdminController@store')->name('admin.store');
            Route::get('/admin/{id}/show', 'AdminController@show')->name('admin.show');
            Route::get('/admin/{id}/edit', 'AdminController@edit')->name('admin.edit');
            Route::put('/admin/{id}/update', 'AdminController@update')->name('admin.update');
            Route::delete('admin/{id}/delete', 'AdminController@destroy')->name('admin.destroy');
            Route::get('/admin_data', 'AdminController@data')->name('admin.data');
            Route::get('/admin_change_status/{id}', 'AdminController@deactivate')->name('admin.deactivate');

            Route::get('/admin_view/{id}', 'AdminController@view')->name('admin.view');
            Route::get('/admin_delete/{id}', 'AdminController@destroy')->name('admin.destroy');

            Route::get('/settings', 'AdminController@settings')->name('admin.settings');
            Route::post('/settings_store', 'AdminController@settings_store')->name('settings.store');

            //smses
            Route::get('/customer_sms', 'AdminController@customer_sms')->name('admin.customer_sms');
            Route::get('/customer_sms_data', 'AdminController@customer_sms_data')->name('admin.customer_sms.data');
            Route::get('/system_sms', 'AdminController@system_sms')->name('admin.system_sms');
            Route::get('/system_sms_data', 'AdminController@system_sms_data')->name('admin.system_sms.data');

            Route::get('/view_users_last_seen', 'AdminController@view_users_last_seen')->name('admin.view_users_last_seen');
            Route::get('/view_users_last_seen_data', 'AdminController@view_users_last_seen_data')->name('admin.view_users_last_seen_data');
        });

        /****************************account module************************/
        // Route::resource('products', 'ProductController');
        Route::get('/products', 'ProductController@index')->name('products.index');
        Route::get('/products/create', 'ProductController@create')->name('products.create');
        Route::post('/products/store', 'ProductController@store')->name('products.store');
        Route::get('/products/{id}/show', 'ProductController@show')->name('products.show');
        Route::get('/products/{id}/edit', 'ProductController@edit')->name('products.edit');
        Route::put('/products/{id}/update', 'ProductController@update')->name('products.update');
        Route::delete('products/{id}/delete', 'ProductController@destroy')->name('products.destroy');
        Route::get('/products_data', 'ProductController@data')->name('products.data');
        // Route::get('/ro_salary_settlement', 'AccountsController@ro_salary_settlement')->name('ro_salary_settlement');
        // Route::get('/ro_salary_settlement_data', 'AccountsController@ro_salary_settlement_data')->name('ro_salary_settlement.data');

        Route::get('/investors_withdrawal', 'AccountsController@investors_withdrawal')->name('investors.withdrawal');
        Route::get('/investors_withdrawal_data', 'AccountsController@investors_withdrawal_data')->name('investors_withdrawal.data');
        Route::post('/investment_withdrawal_post', 'InvestorsController@investment_withdrawal_post')->name('investment_withdrawal_post')->middleware('role:admin|accountant|sector_manager');

        Route::get('/investors_interest', 'AccountsController@investors_interest')->name('investors.interest')->middleware('role:admin|accountant|sector_manager');
        Route::get('/investors_interest_data', 'AccountsController@investors_interest_data')->name('investors_interest.data')->middleware('role:admin|accountant|sector_manager');
        Route::post('/interest_payment', 'InvestorsController@interest_payment')->name('interest_payment')->middleware('role:admin|accountant|sector_manager');

        /*************Reconcile***************/
        Route::get('/reconcile', 'AccountsController@reconcile')->name('reconcile')->middleware('role:admin|accountant|agent_care|customer_informant|sector_manager');
        Route::post('/reconcile_post', 'AccountsController@reconcile_post')->name('reconcile_post')->middleware('role:admin|accountant|agent_care|customer_informant|sector_manager');
        Route::get('/reconciled_transactions', 'AccountsController@reconciled_transactions')->name('reconciled_transactions')->middleware('role:admin|accountant|agent_care|customer_informant|sector_manager');
        Route::get('/reconciled_transactions_data', 'AccountsController@reconciled_transactions_data')->name('reconciled_transactions.data')->middleware('role:admin|accountant|agent_care|customer_informant|sector_manager');
        Route::get('/unreconciled_transactions', 'AccountsController@unreconciled_transactions')->name('unreconciled_transactions')->middleware('role:admin|accountant|agent_care|customer_informant|sector_manager');
        Route::get('/unreconciled_transactions_data', 'AccountsController@unreconciled_transactions_data')->name('unreconciled_transactions.data')->middleware('role:admin|accountant|agent_care|customer_informant|sector_manager');

        Route::get('/other_settlement', 'AccountsController@other_settlement')->name('other_settlement')->middleware('role:admin|manager|accountant|sector_manager');
        Route::post('/other_settlement_post', 'AccountsController@other_settlement_post')->name('other_settlement_post')->middleware('role:admin|manager|accountant|sector_manager');


        Route::get('/reconcile/bulk', 'AccountsController@reconcile_bulk_post')->name('reconcile_bulk')->middleware('role:admin|accountant|agent_care|customer_informant|sector_manager');
        Route::post('/reconcile_bulk_post', 'AccountsController@reconcile_bulk_data')->name('reconcile_bulk_data')->middleware('role:admin|accountant|agent_care|customer_informant|sector_manager');

        /**************************Groups*************************/
        // Route::resource('groups', 'GroupController');
        Route::get('/groups', 'GroupController@index')->name('groups.index');
        Route::get('/groups/create', 'GroupController@create')->name('groups.create');
        Route::post('/groups/store', 'GroupController@store')->name('groups.store');
        Route::get('/groups/{id}/show', 'GroupController@show')->name('groups.show');
        Route::get('/groups/{id}/edit', 'GroupController@edit')->name('groups.edit');
        Route::put('/groups/{id}/update', 'GroupController@update')->name('groups.update');
        Route::delete('groups/{id}/delete', 'GroupController@destroy')->name('groups.destroy');

        Route::get('/group_delete/{id}', 'GroupController@destroy')->name('groups.delete')->middleware('role:admin|manager|sector_manager');
        Route::get('/groups_data', 'GroupController@data')->name('groups.data');
        Route::get('/group_loans_data/{id}', 'GroupController@group_loans_data')->name('groups.group_loans_data');
        Route::get('/groups_customer_data/{id}', 'GroupController@customer_data')->name('groups.customer_data');
        Route::get('/groups_leader_data', 'GroupController@leader_data')->name('groups.leader_data');
        Route::get('/group_members/{id}', 'GroupController@group_members')->name('groups.members');
        Route::get('/group_add_member/{group_id}/{customer_id}', 'GroupController@add_member')->name('groups.add_member');
        Route::get('/group_remove_member/{group_id}/{customer_id}', 'GroupController@remove_member')->name('groups.remove_member');
        Route::get('/groups_approval', 'GroupController@approval')->name('groups.approval')->middleware('role:admin|manager|sector_manager');
        Route::get('/awaiting_approval_data', 'GroupController@awaiting_approval_data')->name('groups.awaiting_approval_data')->middleware('role:admin|manager|sector_manager');
        Route::get('/group_approve_single/{id}', 'GroupController@approve_single')->name('groups.approve_single')->middleware('role:admin|manager|sector_manager');
        Route::post('/group_approve_multiple/', 'GroupController@approve_multiple')->name('groups.approve_multiple')->middleware('role:admin|manager|sector_manager');
        Route::get('/suspend_group/{id}', 'GroupController@suspend_group')->name('groups.suspend_group')->middleware('role:admin|manager|sector_manager');
        Route::get('/reactivate_group/{id}', 'GroupController@reactivate_group')->name('groups.reactivate_group')->middleware('role:admin|manager|sector_manager');

        /**************************Loan Module*************************/
        // Route::resource('loans', 'LoanController');
        Route::get('/loans', 'LoanController@index')->name('loans.index');
        Route::get('/loans/active', 'LoanController@active')->name('loans.active');
        Route::get('/loans/create', 'LoanController@create')->name('loans.create');
        Route::post('/loans/store', 'LoanController@store')->name('loans.store');
        Route::get('/loans/{id}/show', 'LoanController@show')->name('loans.show');
        Route::get('/loans/{id}/edit', 'LoanController@edit')->name('loans.edit');
        Route::put('/loans/{id}/update', 'LoanController@update')->name('loans.update');
        Route::delete('loans/{id}/delete', 'LoanController@destroy')->name('loans.destroy');

        Route::get('/loans_delete/{id}', 'LoanController@destroy')->name('loans.delete')->middleware('role:admin|manager|accountant|sector_manager');
        Route::post('/loans_delete/{id}', 'LoanController@destroy')->name('loans.delete')->middleware('role:admin|manager|accountant|sector_manager');

        Route::get('/loans_delete_document/{id}', 'LoanController@loans_delete_document')->name('loans.delete.document')->middleware('role:admin|manager|accountant|collection_officer|sector_manager');

        Route::get('/loans_data', 'LoanController@data')->name('loans.data');
        Route::get('/loans_data/active', 'LoanController@activeData')->name('loans.active.data');
        Route::get('/lp/{id}', 'LoanController@lp')->name('loans.lp');
        Route::get('/customer_data', 'LoanController@customer_data')->name('loans.customer_data');
        Route::get('/loans_approval', 'LoanController@approval')->name('loans.approval')->middleware('role:admin|manager|sector_manager');
        Route::get('/loans_waiting_approval', 'LoanController@waitingapproval')->name('loans.waitingapproval')->middleware('role:admin|manager|sector_manager|accountant');
        Route::get('/loans_approval_revamped', 'LoanController@approval_revamped')->name('loans_approval_revamped')->middleware('role:admin|manager|sector_manager');
        Route::get('/loans/{id}/documents', 'LoanController@viewDocuments')->name('loans.viewDocuments')->middleware('role:admin|manager|sector_manager');
        Route::post('/loan/{id}/update-external-video-link', 'LoanController@updateExternalVideoLink')->name('loan.updateExternalVideoLink')->middleware('role:admin|manager|sector_manager');
        Route::post('/loan/{id}/delete-video',  'LoanController@deleteVideo')->name('loan.deleteVideo');


        Route::get('/approve_loans', 'LoanController@approve_loans')->name('approve_loans.data')->middleware('role:admin|manager|accountant|agent_care|sector_manager');
        Route::get('/waiting_approve_loans', 'LoanController@waiting_approve_loans')->name('waiting_approve_loans.data')->middleware('role:admin|manager|accountant|agent_care|sector_manager');
        Route::get('/post_approve/{id}', 'LoanController@post_approve')->name('loans.post_approve')->middleware('role:admin|manager|sector_manager');
        Route::post('/post_approve_multiple/', 'LoanController@post_approve_multiple')->name('loans.post_approve_multiple')->middleware('role:admin|manager|sector_manager');
        Route::post('/agent_post_approve_multiple/{id}', 'LoanController@postApproveMultiple')->name('loans.postApproveMultiple')->middleware('role:admin|manager|sector_manager');

        Route::get('/loan/create_group_loan', 'LoanController@group_create')->name('loans.group_create');
        Route::post('/loan/store_group_loan', 'LoanController@store_group_loan')->name('loans.store_group_loan');
        Route::get('/customer_group_data', 'LoanController@customer_group_data')->name('loans.customer_group_data');

        Route::get('/restructure_loan', 'LoanController@loan_restructure')->name('loans.loan_restructure')->middleware('role:admin|accountant|manager|agent_care|sector_manager');
        Route::get('/restructure_loan/{id}', 'LoanController@show_customer_loans')->name('loans.show_customer_loans')->middleware('role:admin|accountant|manager|agent_care|sector_manager');
        Route::get('/restructure_loan_cust_data', 'LoanController@restructureCustomerData')->name('loans.restructureCustomerData')->middleware('role:admin|accountant|manager|agent_care|sector_manager');
        Route::get('/restructure_loan_cust_loans_data/{id}', 'LoanController@customer_loans_data')->name('loans.customer_loans_data')->middleware('role:admin|accountant|manager|agent_care|sector_manager');
        Route::get('/restructure_loan/cust/{id}', 'LoanController@restructure')->name('loans.restructure')->middleware('role:admin|accountant|manager|agent_care|sector_manager');
        Route::get('/installments_data/{id}', 'LoanController@installments_data')->name('loans.installments_data')->middleware('role:admin|accountant|manager|agent_care|sector_manager');
        Route::post('/restructure_loan/cust/{id}', 'LoanController@restructure_post')->name('loans.restructure_post')->middleware('role:admin|accountant|sector_manager');
        Route::post('/loan_collaterals/{id}', 'LoanController@add_collateral')->name('loan.add_collateral');

        /******************************investors Module******************************************/
        // Route::resource('investors', 'InvestorsController');
        Route::get('/investors', 'InvestorsController@index')->name('investors.index');
        Route::get('/investors/create', 'InvestorsController@create')->name('investors.create');
        Route::post('/investors/store', 'InvestorsController@store')->name('investors.store');
        Route::get('/investors/{id}/show', 'InvestorsController@show')->name('investors.show');
        Route::get('/investors/{id}/edit', 'InvestorsController@edit')->name('investors.edit');
        Route::put('/investors/{id}/update', 'InvestorsController@update')->name('investors.update');
        Route::delete('investors/{id}/delete', 'InvestorsController@destroy')->name('investors.destroy');

        Route::get('/investors_data', 'InvestorsController@investors_data')->name('investors.data')->middleware('role:admin|accountant|agent_care|sector_manager');
        Route::get('/investor_investments/{id}', 'InvestorsController@investor_investments')->name('investor.investments')->middleware('role:admin|accountant|agent_care|sector_manager');
        Route::post('/add_investment', 'InvestorsController@add_investment')->name('add.investment')->middleware('role:admin');
        Route::get('/ajax_investors/{id}', 'InvestorsController@ajax_investors')->name('ajax_investors')->middleware('role:admin|accountant|agent_care|sector_manager');
        Route::get('/ajax_investors_investments/{id}', 'InvestorsController@ajax_investors_investments')->name('ajax_investors_investments')->middleware('role:admin|accountant|agent_care|sector_manager');

        /**************************************disbursed routes******************************/
        Route::get('/disbursed_loans', 'DisbursementController@disbursed')->name('disbursed.loans')->middleware('role:admin|accountant|sector_manager');
        // Route::get('/disbursed_loans_data', 'DisbursementController@disbursed_loans_data')->name('disbursed_loans.data')->middleware('role:admin|accountant|investor|agent_care|sector_manager');

        /**************************************disbursement routes******************************/
        Route::get('/loans_disbursement', 'DisbursementController@disbursement')->name('loans.disbursement')->middleware('role:admin|accountant|sector_manager');
        Route::get('/loans_disbursement_revamped', 'DisbursementController@disbursement_revamped')->name('loans_disbursement_revamped')->middleware('role:admin|accountant|sector_manager');

        Route::get('/disburse_loans_data', 'DisbursementController@disburse_loans_data')->name('disburse_loans.data')->middleware('role:admin|accountant|investor|agent_care|sector_manager');
        Route::get('/post_disburse/{id}', 'DisbursementController@post_disburse')->name('loans.post_disburse')->middleware('role:admin|accountant|sector_manager');
        Route::post('/re_disburse/{id}', 'DisbursementController@post_disburse')->name('re_disburse_post')->middleware('role:admin|accountant|sector_manager');

        Route::post('/post_disburse_multiple', 'DisbursementController@post_disburse_multiple')->name('loans.post_disburse_multiple')->middleware('role:admin|accountant|sector_manager');

        Route::get('/disbursement_pending', 'DisbursementController@disbursement_pending')->name('loans.disbursement_pending')->middleware('role:admin|accountant|sector_manager');
        Route::get('/disburse_loans_pending_data', 'DisbursementController@disburse_loans_pending_data')->name('disburse_loans_pending.data')->middleware('role:admin|accountant|agent_care|sector_manager');
        Route::get('/loans_post_disburse_reconcile/{id}', 'DisbursementController@disburse_reconcile_form')->name('disburse_reconcile')->middleware('role:admin|accountant|sector_manager');
        Route::post('/post_disburse_reconcile/{id}', 'DisbursementController@post_disburse_reconcile')->name('post_disburse_reconcile')->middleware('role:admin|accountant|sector_manager');

        /********************************MPesa balance request*****************************/
        Route::get('/mpesa_balance', 'DisbursementController@mpesa_balance')->name('mpesa_balance')->middleware('role:admin|accountant|investors|sector_manager');

        /****************************payments Controller***************/
        // Route::resource('payments', 'PaymentController');
        Route::get('/payments', 'PaymentController@index')->name('payments.index');
        Route::get('/payments/create', 'PaymentController@create')->name('payments.create');
        Route::post('/payments/store', 'PaymentController@store')->name('payments.store');
        Route::get('/payments/{id}/edit', 'PaymentController@edit')->name('payments.edit');
        Route::get('/payments/{id}/show', 'PaymentController@show')->name('payments.show');
        Route::put('/payments/{id}/update', 'PaymentController@update')->name('payments.update');
        Route::delete('payments/{id}/delete', 'PaymentController@destroy')->name('payments.destroy');

        Route::get('/payments_loan/{id}', 'PaymentController@payments')->name('payments')->middleware('role:admin|accountant|field_agent|manager|customer_informant|agent_care|manager|sector_manager');
        Route::get('/payments_data', 'PaymentController@data')->name('payments.data')->middleware('role:admin|accountant|agent_care|manager|sector_manager');
        Route::get('/settlement_transactions', 'PaymentController@settlement_transactions')->name('settlement_transactions')->middleware('role:admin|accountant|agent_care|sector_manager');
        Route::get('/settlement_transactions_data', 'PaymentController@settlement_transactions_data')->name('settlement_transactions.data')->middleware('role:admin|accountant|agent_care|sector_manager');
        Route::get('/settlement_transactions_edit/{id}}', 'PaymentController@settlement_transactions_edit')->name('settlement_transactions.edit')->middleware('role:admin|accountant|sector_manager');
        Route::post('/settlement_transactions_update', 'PaymentController@settlement_transactions_update')->name('settlement_transactions.update')->middleware('role:admin|accountant|sector_manager');
        Route::get('/settlement_transactions_delete/{id}', 'PaymentController@settlement_transactions_delete')->name('settlement_transactions.delete')->middleware('role:admin|accountant|sector_manager');

        Route::get('/registration_transactions', 'PaymentController@registration_transactions')->name('registration_transactions')->middleware('role:admin|accountant|agent_care|sector_manager');
        Route::get('/registration_transactions_data', 'PaymentController@registration_transactions_data')->name('registration_transactions.data')->middleware('role:admin|accountant|agent_care|sector_manager');
        Route::get('/other_transactions', 'PaymentController@others_transactions')->name('others_transactions')->middleware('role:admin|accountant|agent_care|sector_manager');
        Route::get('/others_transactions_data', 'PaymentController@others_transactions_data')->name('others_transactions.data')->middleware('role:admin|accountant|investor|agent_care|sector_manager');
        Route::get('/other_transaction_edit/{id}', 'PaymentController@settlement_transactions_edit')->name('others_transactions.edit')->middleware('role:admin|accountant|sector_manager');
        Route::get('/other_transaction_delete/{id}', 'PaymentController@other_transaction_delete')->name('others_transactions.delete')->middleware('role:admin|accountant|sector_manager');

        /*******************************reports routes********************/
        Route::get('/reports', 'ReportController@index')->name('reports')/*->middleware('role:admin|accountant')*/;
        Route::get('/reports_data', 'ReportController@data')->name('reports.data')/*->middleware('role:admin|manager')*/;
        Route::get('/systems_users', 'ReportController@system_users_report')->name('system_users_report')->middleware('role:admin|accountant|agent_care|sector_manager');
        Route::get('/system_users_report_data', 'ReportController@system_users_report_data')->name('system_users_report_data')->middleware('role:admin|accountant|agent_care|sector_manager');
        Route::get('/disbursed_loans', 'ReportController@disbursed_loans')->name('disbursed_loans');
        Route::get('/disbursed_loans_data', 'ReportController@disbursed_loans_data')->name('disbursed_loans_data');
        Route::get('/loan_pending_approval', 'ReportController@loan_pending_approval')->name('loan_pending_approval')->middleware('role:admin|accountant|agent_care');
        Route::get('/loan_pending_disbursements', 'ReportController@loan_pending_disbursements')->name('loan_pending_disbursements')->middleware('role:admin|accountant|investor|agent_care');
        Route::get('/loan_due_today', 'ReportController@loan_due_today')->name('loan_due_today');
        Route::get('/loans_due_today_data', 'ReportController@loans_due_today_data')->name('loans_due_today_data');
        Route::get('/mpesa_repayments', 'ReportController@mpesa_repayments')->name('mpesa_repayments')->middleware('auth');
        Route::get('/mpesa_repayments_data', 'ReportController@mpesa_repayments_data')->name('mpesa_repayments.data')->middleware('auth');
        Route::get('/inactive_customers', 'ReportController@inactive_customers')->name('inactive_customers');
        Route::get('/blocked_customers', 'ReportController@blocked_customers')->name('blocked_customers');
        Route::get('/rolled_over_loans', 'ReportController@rolled_over_loans')->name('rolled_over_loans');
        Route::get('/rolled_over_loans_data', 'ReportController@rolled_over_loans_data')->name('rolled_over_loans.data');
        Route::get('/loan_collections', 'ReportController@loan_collections')->name('loan_collections');
        Route::get('/loan_collections_data', 'ReportController@loan_collections_data')->name('loan_collections.data');
        Route::get('/loans_balance', 'ReportController@loans_balance')->name('loans_balance');
        Route::get('/loans_balance_data', 'ReportController@loans_balance_data')->name('loans_balance.data');
        Route::get('/loan_arrears', 'ReportController@loan_arrears')->name('loan_arrears');
        Route::get('/loan_arrears_data', 'ReportController@loan_arrears_data')->name('loan_arrears.data');
        Route::get('/non_performing_loans', 'ReportController@non_performing_loans')->name('non_performing_loans');
        Route::get('/non_performing_loans_data', 'ReportController@non_performing_loans_data')->name('non_performing_loans.data');
        Route::get('/par_summary', 'ReportController@par_summary')->name('par_summary');
        Route::get('/par_summary_data', 'ReportController@par_summary_data')->name('par_summary.data');
        Route::get('/loan_collections_per_month', 'ReportController@loan_collections_per_month')->name('loan_collections_per_month');
        Route::post('/loan_collections_per_month', 'ReportController@loan_collections_per_month')->name('loan_collections_per_month');
        Route::get('/loan_disbursement_permonth', 'ReportController@loan_disbursement_permonth')->name('loan_disbursement_permonth');
        Route::post('/loan_disbursement_permonth', 'ReportController@loan_disbursement_permonth')->name('loan_disbursement_permonth');
        Route::get('/field_agent_performance', 'ReportController@field_agent_performance')->name('field_agent_performance')->middleware('role:admin|manager|investor|accountant|field_agent|collection_officer|agent_care|sector_manager');
        Route::get('/field_agent_performance_data', 'ReportController@field_agent_performance_data')->name('field_agent_performance.data')->middleware('role:admin|manager|investor|accountant|field_agent|collection_officer|agent_care|sector_manager');
        Route::get('/field_agent_performance/{id}', 'ReportController@ro_performance')->name('ro.performance')->middleware('role:admin|manager|investor|accountant|field_agent|collection_officer|agent_care|sector_manager');
        Route::get('/field_agent_performance/ro_performance_data/{id}', 'ReportController@ro_performance_data')->name('ro.performance_data')->middleware('role:admin|manager|investor|accountant|field_agent|collection_officer|agent_care|sector_manager');

        Route::get('/field_agent_performance/target_add/{id}', 'ReportController@ro_target_add')->name('ro.target_add');
        Route::post('/field_agent_performance/target_add/{id}', 'ReportController@ro_target_add')->name('ro.target_add');

        Route::get('/income_statement', 'ReportController@income_statement')->name('income_statement')->middleware('role:admin|accountant|agent_care|sector_manager');
        Route::post('/income_statement', 'ReportController@income_statement')->name('income_statement')->middleware('role:admin|accountant|agent_care|sector_manager');

        Route::get('/income_statement_v2', 'ReportController@income_statement_v2')->name('income_statement_v2')->middleware('role:admin|accountant|agent_care|investor|sector_manager');
        Route::post('/income_statement_v2', 'ReportController@income_statement_v2')->name('income_statement_v2')->middleware('role:admin|accountant|agent_care|investor|sector_manager');

        //Route::post('/income_statement_summary', 'ReportController@income_statement_summary')->name('income_statement_summary')->middleware('role:admin');

        Route::get('/disbursed_loans_summary', 'ReportController@disbursed_loans_summary')->name('disbursed_loans_summary')->middleware('role:admin|investor|accountant|agent_care|sector_manager');
        Route::post('/disbursed_loans_summary', 'ReportController@disbursed_loans_summary')->name('disbursed_loans_summary')->middleware('role:admin|investor|accountant|agent_care|sector_manager');
        Route::post('/cash_flow_statement', 'ReportController@cash_flow_statement')->name('cash_flow_statement')->middleware('role:admin|investor|accountant|agent_care|sector_manager');
        Route::get('/cash_flow_statement', 'ReportController@cash_flow_statement')->name('cash_flow_statement')->middleware('role:admin|investor|accountant|agent_care|sector_manager');

        Route::get('/customer_listing', 'ReportController@customer_listing')->name('customer_listing');
        Route::get('/customer_listing_data', 'ReportController@customer_listing_data')->name('customer_listing.data');
        Route::get('/customer_account_statement', 'ReportController@customer_account_statement')->name('customer_account_statement');
        Route::get('/customer_account_statement_data', 'ReportController@customer_account_statement_data')->name('customer_account_statement.data');
        Route::get('/customer_account_statement_single/{customer}', 'ReportController@customer_account_statement_single')->name('customer_account_statement_single')->middleware('role:admin|accountant|manager|customer_informant|field_agent|investor|collection_officer|agent_care|sector_manager');
        Route::get('/customer_account_statement_single_data/{customer}', 'ReportController@customer_account_statement_single_data')->name('customer_account_statement_single_data')->middleware('role:admin|accountant|manager|customer_informant|field_agent|investor|collection_officer|agent_care|sector_manager');
        Route::get('/customer_account_statement_loans_data/{customer}', 'ReportController@customer_account_statement_loans_data')->name('customer_account_statement_loans_data')->middleware('role:admin|accountant|manager|customer_informant|field_agent|investor|collection_officer|agent_care|sector_manager');

        Route::post('/customer_account_statement_single/{customer}', 'ReportController@customer_account_statement_single_post')->name('customer_account_statement_single_post')->middleware('role:admin|accountant|manager|customer_informant|field_agent|investor|collection_officer|agent_care|sector_manager');

        Route::get('/inactive_customers', 'ReportController@inactive_customers')->name('inactive_customers');
        Route::get('/inactive_customers_data', 'ReportController@inactive_customers_data')->name('inactive_customers.data');
        Route::get('/blocked_customers_data', 'ReportController@blocked_customers_data')->name('blocked_customers.data');

        Route::get('/sms_summary', 'ReportController@sms_summary')->name('sms_summary');
        Route::get('/sms_summary_data', 'ReportController@sms_summary_data')->name('sms_summary.data');
        Route::get('/branch_expenses', 'ReportController@branch_expenses')->name('branch_expenses')->middleware('role:admin|accountant|investor|agent_care|sector_manager');
        Route::get('/branch_expenses_data', 'ReportController@branch_expenses_data')->name('branch_expenses_data');

        Route::get('/manager_officer_performance', 'ReportController@manager_officer_performance_list')->name('manager_officer_performance');
        Route::get('/manager_performance/{id}', 'ReportController@manager_performance_revamped')->name('manager_performance');
        Route::get('/manager_performance_data/{id}', 'ReportController@manager_performance_data')->name('manager_performance_data');
        Route::get('/manager_performance_revamped/{id}', 'ReportController@manager_performance_revamped')->name('manager_performance_revamped');
        Route::get('/ajax_manager_performance/{id}', 'ManagerAjaxController@manager_performance')->name('ajax_manager_performance');

        Route::get('/par_analysis', 'ReportController@par_summary')->name('par_analysis');
        Route::get('/collection_rate', 'ReportController@collection_rate')->name('collection_rate');
        Route::get('/collection_rate_data', 'ReportController@colllection_rate_data')->name('collection_rate_data');
        Route::get('/collection_rate_post', 'ReportController@collection_rate')->name('collection_rate_post');

        Route::get('/customer_scoring', 'ReportController@customer_scoring')->name('customer_scoring');
        Route::get('/customer_scoring_data', 'ReportController@customer_scoring_data')->name('customer_scoring_data');

        Route::get('/credit_officer_performance/{id}', 'ReportController@loco_performance')->name('credit_officer_performance');
        Route::get('/loco_performance/{id}', 'ReportController@loco_performance')->name('loco_performance');

        Route::get('/par_summary_data_CO/{id}', 'ReportController@par_summary_data_CO')->name('par_summary_data_CO.data');
        Route::get('/monthly_collection_performance_data/{id}', 'ReportController@monthly_collection_performance_data')->name('monthly_collection_performance_data');

        Route::get('/credit_officer_monthly_collection_overview/{id}', 'ReportController@credit_officer_monthly_collection_overview')->name('credit_officer_monthly_collection_overview');
        Route::get('/credit_officer_monthly_collection_overview_data/{id}', 'ReportController@credit_officer_monthly_collection_overview_data')->name('credit_officer_monthly_collection_overview_data');
        // Route::get('/group_data_CO/{id}', 'ReportController@group_data_CO')->name('group_data_CO');

        Route::get('/co_income_data/{id}', 'ReportController@co_income_data')->name('co_income_data')->middleware('role:admin|investor|investor|accountant|manager|field_agent|collection_officer|agent_care|sector_manager');
        Route::get('/co_income_data_ajax/{id}', 'ReportController@co_income_data_ajax')->name('co_income_data_ajax')->middleware('role:admin|investor|investor|accountant|manager|field_agent|collection_officer|agent_care|sector_manager');

        Route::get('/loan_arreas_skipped_payments', 'ReportController@loan_skipped_payments')->name('loan_skipped_payments');
        Route::get('/loan_skipped_payments_data', 'ReportController@loan_skipped_payments_data')->name('loan_skipped_payments.data');

        Route::get('/default_analysis_report', 'ReportController@default_analysis_report')->name('defaul_analysis_report');
        Route::get('/default_analysis_report_data', 'ReportController@default_analysis_report_data')->name('default_analysis_report.data');
        Route::get('/default_ajax', 'ReportController@default_ajax')->name('default_ajax');

        //leads
        Route::get('/leads', 'ReportController@leads')->name('leads');
        Route::get('/leads_data', 'ReportController@leads_data')->name('leads_data');
        Route::get('/lead_create', 'ReportController@lead_create')->name('lead.create');
        Route::post('/lead_post', 'ReportController@lead_post')->name('lead_post');
        Route::get('/lead_delete/{id}', 'ReportController@lead_delete')->name('lead.delete');
        Route::get('/import-leads', 'ReportController@importLead')->name('import_lead');
        Route::post('/import-leads-data', 'ReportController@importLeads')->name('import_leads');


        Route::get('/mp/register_url', 'MpesaPaymentController@registerurl')->name('mpesa.registerurl');

        /****************************************GROUP REPORTS**************************************/
        Route::get('/group_reports', 'ReportController@group_index')->name('group_index');
        Route::get('/group_reports_data', 'ReportController@group_data')->name('group_reports_data');
        Route::get('/group_loan_arrears', 'ReportController@group_loan_arrears')->name('group_loan_arrears');
        Route::get('/group_loan_arrears_data', 'ReportController@group_loan_arrears_data')->name('group_loan_arrears_data');
        Route::get('/group_loan_skipped_payments', 'ReportController@group_loan_skipped_payments')->name('group_loan_skipped_payments');
        Route::get('/group_loan_skipped_payments_data', 'ReportController@group_loan_skipped_payments_data')->name('group_loan_skipped_payments_data');
        Route::get('/group_disbursed_loans', 'ReportController@group_disbursed_loans')->name('group_disbursed_loans');
        Route::get('/group_disbursed_loans_data', 'ReportController@group_disbursed_loans_data')->name('group_disbursed_loans_data');
        Route::get('/group_scoring', 'ReportController@group_scoring')->name('group_scoring');
        Route::get('/group_scoring_data', 'ReportController@group_scoring_data')->name('group_scoring_data');
        Route::get('/group_loans_balance', 'ReportController@group_loans_balance')->name('group_loans_balance');
        Route::get('/group_loans_balance_data', 'ReportController@group_loans_balance_data')->name('group_loans_balance_data');

        // Route::resource('customer-history-thread', 'CustomerHistoryThreadController')->only(['store']);
        Route::post('/customer-history-thread/store', 'CustomerHistoryThreadController@store')->name('customer-history-thread.store');
        Route::get('/list_customer_thread/{customer_identifier}', 'CustomerHistoryThreadController@list_customer_thread')->name('list_customer_thread');
        Route::get('/list_customer_thread_data/{customer_identifier}', 'CustomerHistoryThreadController@data')->name('list_customer_thread_data');

        Route::get('/customer-documents/{customer_identifier}', 'CustomerDocumentsController@create')->name('customer-documents.create');
        Route::post('/customer-documents/{id}/upload-video', 'CustomerDocumentsController@uploadVideo')->name('customer-documents.upload_video');
        Route::get('/customer-document-edit/{customer_document_identifier}', 'CustomerDocumentsController@edit')->name('customer-documents.edit');
        Route::post('/customer-documents', 'CustomerDocumentsController@store')->name('customer-documents.store');
        Route::post('/customer-documents-upload-mpesa-statement_revamped', 'CustomerDocumentsController@store_mpesa_statement')->name('customer-documents.store_mpesa_statement');
        Route::post('/customer-documents/{customer}/upload-video', 'CustomerDocumentsController@uploadVideo')->name('customer-documents.upload-video');
        Route::post('/customer-documents/{customer_id}', 'CustomerDocumentsController@uploadStoreVideo')->name('customer-documents.upload-store-video');

        Route::put('/customer-documents/{customer_document_identifier}', 'CustomerDocumentsController@update')->name('customer-documents.update');
        Route::get('/customer-documents-view-mpesa-statement/{customer_document_identifier}', 'CustomerDocumentsController@view_mpesa_statement')->name('customer-documents.view_mpesa_statement');

        // Route::resource('check-off-employers', 'CheckOffEmployerController');
        Route::get('/check-off-employers', 'CheckOffEmployerController@index')->name('check-off-employers.index');
        Route::get('/check-off-employers/create', 'CheckOffEmployerController@create')->name('check-off-employers.create');
        Route::post('/check-off-employers/store', 'CheckOffEmployerController@store')->name('check-off-employers.store');
        Route::get('/check-off-employers/{id}/edit', 'CheckOffEmployerController@edit')->name('check-off-employers.edit');
        Route::get('/check-off-employers/{id}/show', 'CheckOffEmployerController@show')->name('check-off-employers.show');
        Route::put('/check-off-employers/{id}/update', 'CheckOffEmployerController@update')->name('check-off-employers.update');
        Route::delete('check-off-employers/{id}/delete', 'CheckOffEmployerController@destroy')->name('check-off-employers.destroy');

        Route::get('/check-off-employers-data', 'CheckOffEmployerController@data')->name('check-off.employers.data');
        Route::get('/check-off-employers-change-status/{employer}', 'CheckOffEmployerController@change_status')->name('check-off.employers.change_status');

        // Route::resource('check-off-products', 'CheckOffProductsController');
        Route::get('/check-off-products', 'CheckOffProductsController@index')->name('check-off-products.index');
        Route::get('/check-off-products/create', 'CheckOffProductsController@create')->name('check-off-products.create');
        Route::post('/check-off-products/store', 'CheckOffProductsController@store')->name('check-off-products.store');
        Route::get('/check-off-products/{id}/edit', 'CheckOffProductsController@edit')->name('check-off-products.edit');
        Route::get('/check-off-products/{id}/show', 'CheckOffProductsController@show')->name('check-off-products.show');
        Route::put('/check-off-products/{id}/update', 'CheckOffProductsController@update')->name('check-off-products.update');
        Route::delete('check-off-products/{id}/delete', 'CheckOffProductsController@destroy')->name('check-off-products.destroy');

        Route::get('/check-off-products-data', 'CheckOffProductsController@data')->name('check-off.products.data');
        Route::get('/check-off-products-change-status/{employer}', 'CheckOffProductsController@change_status')->name('check-off.products.change_status');

        // Route::resource('check-off-employees', 'CheckOffEmployeeController')->only(['index']);
        Route::get('/check-off-employees', 'CheckOffEmployeeController@index')->name('check-off-employees.index');
        Route::get('/check-off-employees/{id}/delete', 'CheckOffEmployeeController@destroy')->name('check-off-employees.destroy');
        Route::get('/check-off-employee-data', 'CheckOffEmployeeController@data')->name('check-off.employees.data');

        // Route::resource('check-off-loans', 'CheckOffLoanController')->only(['index', 'destroy']);
        Route::get('/check-off-loans', 'CheckOffEmployeeController@index')->name('check-off-loans.index');
        Route::delete('/check-off-loans/{id}/delete', 'CheckOffEmployeeController@destroy')->name('check-off-loans.destroy');

        Route::get('/check-off-loans-data', 'CheckOffLoanController@data')->name('check-off.loans.data');
        Route::get('/check-off-loans-mark-as-settled/{loanID}', 'CheckOffLoanController@mark_as_settled')->name('check-off.loans.mark_as_settled');
        Route::get('/check-off-loans-mark-as-approved/{loanID}', 'CheckOffLoanController@mark_as_approved')->name('check-off.loans.mark_as_approved');
        Route::get('/check-off-loans-mark-as-rejected/{loanID}', 'CheckOffLoanController@mark_as_rejected')->name('check-off.loans.mark_as_rejected');

        Route::get('/check-off-loans-payments', 'CheckOffLoanController@payment_index')->name('check-off.loans.payment_index');
        Route::get('/check-off-loans-payment-data', 'CheckOffLoanController@payment_index_data')->name('check-off.loans.payment_index_data');
        Route::get('/check-off-loans-payments/{loan_identifier}', 'CheckOffLoanController@loan_payments')->name('check-off.loans.loan_payments');
        Route::get('/check-off-loans-payment-data/{loan_identifier}', 'CheckOffLoanController@loan_payments_data')->name('check-off.loans.loan_payments_data');

        // Route::resource('check-off-loans-disbursement', 'CheckOffDisbursementController')->only(['index', 'store']);
        Route::get('/check-off-loans-disbursement', 'CheckOffDisbursementController@index')->name('check-off-loans-disbursement.index');
        Route::post('/check-off-loans-disbursement', 'CheckOffDisbursementController@store')->name('check-off-loans-disbursement.store');
        Route::get('/check-off-loans-disbursement-data', 'CheckOffDisbursementController@data')->name('check-off.loans-disbursement.data');

        // Route::resource('customer-interactions', 'CustomerInteractionController')->only(['store']);
        Route::post('/customer-interactions', 'CustomerInteractionController@store')->name('customer-interactions.store');

        Route::get('/all_interactions', 'CustomerInteractionController@all_interactions')->name('all_interactions');
        Route::get('/unhandled_arrears_interactions/{id}', 'CustomerInteractionController@unhandled_arrears')->name('unhandled_arrears');
        Route::get('/unhandled_arrears_data/{id}', 'CustomerInteractionController@unhandled_arrears_data')->name('unhandled_arrears_data');

        Route::get('/customer-interactions/select-customer', 'CustomerInteractionController@select_customer')->name('customer-interactions.select_customer');
        Route::get('/customer-interactions-all-customers-data', 'CustomerInteractionController@select_customer_data')->name('customer-interactions.select_customer_data');
        Route::get('/customer-interactions/list-customer-interactions/{identifier}', 'CustomerInteractionController@list_customer_interactions')->name('customer-interactions.list_customer_interactions');
        Route::get('/customer-interactions/list-customer-interactions-data/{identifier}', 'CustomerInteractionController@customer_interactions_data')->name('customer-interactions.customer_interactions_data');
        Route::get('/customer-interactions-report', 'CustomerInteractionController@interactions_report')->name('customer-interactions.interactions_report');
        Route::get('/customer-interactions-report-data', 'CustomerInteractionController@customer_interactions_report_data')->name('customer-interactions.customer_interactions_report_data');
        Route::get('/pre_interactions/{id}', 'CustomerInteractionController@pre_interactions')->name('pre_interactions');
        Route::get('/pre_interactions_data', 'CustomerInteractionController@pre_interactions_data')->name('pre_interactions_data');
        Route::get('/interaction_follow_up/{id}', 'CustomerInteractionController@interaction_follow_up')->name('interaction.follow_up');
        Route::post('/customer-interaction_followup_store', 'CustomerInteractionController@interaction_followup_store')->name('customer-interaction_followup.store');
        Route::get('/update_follow_up/{name}/{id}', 'CustomerInteractionController@update_follow_up')->name('update_follow_up');

        Route::get('/customer-location-details/{customer_identifier}', 'RegistryController@customer_location')->name('customer-location-details');
        Route::post('/customer-location-details/{customer_identifier}', 'RegistryController@update_customer_location')->name('customer-location-details.update');

        Route::get('/test_sms', 'CollectionOfficerController@sendsms')->name('sendsms')->middleware('role:admin|sector_manager');;
        Route::get('/update_loans_amount', 'HomeController@update_loans_amount')->name('update_loans_amount')->middleware('role:admin|sector_manager');;
        Route::get('/update_amount_paid', 'HomeController@update_amount_paid')->name('update_amount_paid')->middleware('role:admin|sector_manager');;
        Route::get('/test_fxn/{id}', 'HomeController@test_function')->name('test_function')->middleware('role:admin');
//    });
});

/***********************************mpesa routes**************************************/
Route::post('/mp/disbursement_result', 'DisbursementController@mpesa_disbursement_result')->name('mpesa_disbursement.result');
Route::post('/mp/disbursement_result_timeout', 'DisbursementController@mpesa_disbursement_result_timeout')->name('mpesa_disbursement.timeout');

Route::post('/mp/check-off-disbursement-result', 'CheckOffDisbursementController@result')->name('check-off-disbursement.result');
Route::post('/mp/check-off-disbursement-result-timeout', 'CheckOffDisbursementController@timeout')->name('check-off-disbursement.timeout');

Route::get('/mp/simulate', 'MpesaPaymentController@simulate')->name('mpesa.simulate');

Route::post('/mp/confirmation', 'MpesaPaymentController@confirmation')->name('mpesa.confirmation');
Route::post('/mp/validation_url', 'MpesaPaymentController@validation_url')->name('mpesa.validation_url');

//settlement results
Route::post('/mp/settlement_result', 'MpesaSettlementController@settlement_result')->name('settlement_result');
Route::post('/mp/settlement_timeout', 'MpesaSettlementController@settlement_timeout')->name('settlement_timeout');

/*mpesa balance*/
Route::post('/mp/balance_result', 'DisbursementController@mpesa_balance_result')->name('mpesa_balance.result');
Route::post('/mp/balance_timeout', 'DisbursementController@mpesa_balance_timeout')->name('mpesa_balance.timeout');









