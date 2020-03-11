<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::post('register', ['uses' => 'Api\AuthController@register', 'as' => 'api.register.superadmins']);
Route::post('login', ['uses' => 'Api\AuthController@authenticate', 'as' => 'api.login']);

Route::group(['prefix' => 'v1', 'middleware' => ['jwt.verify']], function () {
    Route::post('company/create', ['uses' => 'Api\CompanyController@create', 'as' => 'api.comapny.create']);
    Route::post('company/edit', ['uses' => 'Api\CompanyController@edit', 'as' => 'api.comapny.edit']);
    Route::get('company/browse', ['uses' => 'Api\CompanyController@browse', 'as' => 'api.comapny.browse']);
    Route::get('company/view', ['uses' => 'Api\CompanyController@view', 'as' => 'api.comapny.view']);
    Route::post('company/delete', ['uses' => 'Api\CompanyController@delete', 'as' => 'api.comapny.delete']);


    // Employee 

    Route::post('employee/create', ['uses' => 'Api\EmployeeController@create', 'as' => 'api.employee.create']);
    Route::post('employee/edit', ['uses' => 'Api\EmployeeController@edit', 'as' => 'api.employee.edit']);
    Route::get('employee/browse', ['uses' => 'Api\EmployeeController@browse', 'as' => 'api.employee.browse']);
    Route::get('employee/view', ['uses' => 'Api\EmployeeController@view', 'as' => 'api.employee.view']);
    Route::post('employee/delete', ['uses' => 'Api\EmployeeController@delete', 'as' => 'api.employee.delete']);

    // Positions

    Route::post('positions/create', ['uses' => 'Api\PositionsController@create', 'as' => 'api.positions.create']);
    Route::post('positions/edit', ['uses' => 'Api\PositionsController@edit', 'as' => 'api.positions.edit']);
    Route::get('positions/browse', ['uses' => 'Api\PositionsController@browse', 'as' => 'api.positions.browse']);
    Route::get('positions/view', ['uses' => 'Api\PositionsController@view', 'as' => 'api.positions.view']);
    Route::post('positions/delete', ['uses' => 'Api\PositionsController@delete', 'as' => 'api.positions.delete']);
});
