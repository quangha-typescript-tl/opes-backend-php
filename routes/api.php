<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('login', 'UserController@login');
Route::post('refreshToken', 'UserController@refreshToken');
Route::post('logout', 'UserController@logout');
Route::get('getListDepartment', 'DepartmentController@getListDepartment');


Route::group(array('prefix' => 're'), function () {

    Route::group(array('middleware' => 'jwt.auth'), function () {
        Route::get('getDetailUser/{userId}', 'UserController@getDetailUser');
        Route::get('getUsers', 'UserController@getUsers');
        Route::post('addUsers', 'UserController@addUsers');
        Route::post('registerUser', 'UserController@registerUser');
        Route::post('deleteUser', 'UserController@deleteUser');
        Route::post('updateUser', 'UserController@updateUser');

        Route::post('addDepartment', 'DepartmentController@addDepartment');
        Route::post('updateDepartment', 'DepartmentController@updateDepartment');
        Route::post('deleteDepartment', 'DepartmentController@deleteDepartment');
    });

    Route::group([], function () {
        Route::get('getDepartments', 'DepartmentController@getDepartments');
    });
});

