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

Route::group(array('prefix' => 're'), function () {

    Route::group(array('middleware' => 'jwt.auth'), function () {

        Route::get('getUserSession', 'UserController@getUserSession');
        Route::post('changePassword', 'UserController@changePassword');
        Route::get('getDetailUser/{userId}', 'UserController@getDetailUser');
        Route::get('getUsers', 'UserController@getUsers');
        Route::post('addUsers', 'UserController@addUsers');
        Route::post('registerUser', 'UserController@registerUser');
        Route::post('deleteUser', 'UserController@deleteUser');
        Route::post('blockUser', 'UserController@blockUser');
        Route::post('setUserStatus', 'UserController@setUserStatus');
        Route::post('updateUser', 'UserController@updateUser');

        Route::post('addDepartment', 'DepartmentController@addDepartment');
        Route::post('updateDepartment', 'DepartmentController@updateDepartment');
        Route::post('deleteDepartment', 'DepartmentController@deleteDepartment');
    });

    Route::group([], function () {
        Route::get('getDepartments', 'DepartmentController@getDepartments');
    });
});

Route::group(array('prefix' => 'co'), function () {

    Route::group(array('middleware' => 'jwt.auth'), function () {

        Route::get('getContents', 'ContentController@getContents');
        Route::post('addContent', 'ContentController@addContent');
        Route::post('editContent', 'ContentController@editContent');
        Route::post('deleteContent', 'ContentController@deleteContent');
        Route::post('uploadImageContent', 'ContentController@uploadImageContent');
        Route::get('getDetailContent/{contentId}', 'ContentController@getDetailContent');
        Route::get('getTopContentRelated', 'ContentController@getTopContentRelated');
        Route::get('getListHashTag', 'HashTagController@getListHashTag');
    });

    Route::group([], function () {
        //
    });
});
