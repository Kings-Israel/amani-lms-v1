<?php

use Illuminate\Support\Facades\Route;

Route::post('/ajax_dashboard/{id}', 'AjaxController@dashboard')->name('ajax.dashboard');
Route::post('/ajax_dashboard/total_commission', 'AjaxController@totalCommission')->name('ajax.dashboard.total_commission');

Route::get('/ajax_interactions/{id}', 'AjaxController@interactions')->name('ajax.interactions');
Route::get('/ajax_loco_performance/{id}', 'LocoAjaxController@loco_performance')->name('ajax.loco_performance');
Route::get('/ajax_repayment_chart_data/', 'AjaxController@repayment_chart_data')->name('ajax.repayment_chart_data');
Route::get('/disbursement_chart_data/', 'AjaxController@disbursement_chart_data')->name('ajax.disbursement_chart_data');
Route::get('/ajax_send_token/', 'AjaxController@ajax_send_token')->name('ajax_send_token');
Route::get('/ajax_verify_token/{token}/{activity}', 'AjaxController@ajax_verify_token')->name('ajax_verify_token');
Route::get('/ajax/field_agents_performance', 'AjaxController@field_agents_performance')->name('ajax_field_agents_performance');
