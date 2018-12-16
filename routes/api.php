<?php


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

Route::middleware('auth:api')->namespace('Api')->group(function () {
    Route::get('/', 'PostController@index');

    Route::prefix('/post')->group(function () {
        Route::get('/{trade_id}', 'PostController@show');
        Route::post('/', 'PostController@store');
        Route::patch('/{trade_id}', 'PostController@edit');
        Route::delete('/{trade_id}', 'PostController@delete');
    });

});
