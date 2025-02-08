<?php

use Illuminate\Support\Facades\Route;
Route::get('/password_request', 'CheckOffController@password_request')->name('check-off.password.request');
Route::post('/update_password', 'CheckOffController@update_password_request')->name('check-off.password.update');
Route::get('/reset/{id}', 'CheckOffController@reset')->name('check-off.password.reset');
Route::post('/update_password_confirm', 'CheckOffController@update_password_confirm')->name('check-off.update_password_confirm');





Route::get('/login', 'CheckOffController@login')->name('check-off.employer.login');
Route::post('/login_post', 'CheckOffController@login_post')->name('checkoff.login_post');
Route::post('/logout', 'CheckOffController@logout')->name('check-off.employer.logout');

Route::get('/2fa', 'CheckOffController@otp')->name('check-off.employer.2fa');
Route::post('/post_2fa', 'CheckOffController@post_2fa')->name('checkoff.post.2fa');

Route::group(['middleware' => ['auth:employers']], function() {


    Route::get('/dashboard', 'CheckOffController@dashboard')->name('check-off.employer.dashboard');
    Route::get('/approve_loans_data', 'CheckOffController@approve_loans_data')->name('check_off.approve_loans.data');
    Route::get('/check-off-loans-mark-as-settled/{loanID}', 'CheckOffController@mark_as_settled')->name('check_off_employer.loans.mark_as_settled');
    Route::get('/check-off-loans-mark-as-approved/{loanID}', 'CheckOffController@mark_as_approved')->name('check_off_employer.loans.mark_as_approved');
    Route::get('/check-off-loans-mark-as-rejected/{loanID}', 'CheckOffController@mark_as_rejected')->name('check_off_employer.loans.mark_as_rejected');
    Route::get('/check-off-loans-destroy/{loanID}', 'CheckOffController@destroy_loan')->name('check_off_employer_loans.destroy');
    Route::get('/checkoffloans', 'CheckOffController@loans')->name('check-off.employer.loans');
    Route::get('/check-off-loans-data', 'CheckOffController@loans_data')->name('check-off.employer.loans_data');

});











