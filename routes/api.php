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

$api = app('Dingo\Api\Routing\Router');

$api->version('v1', [
    'namespace' => 'App\Http\Controllers\Api\V1',
], function ($api) {
    $api->get('/', 'PostController@index');

    $api->group([
        'prefix' => '/post'
    ], function ($api) {
        $api->get('/{trade_id}', 'PostController@show');
        $api->post('/', 'PostController@store');
        $api->patch('/{post}', 'PostController@edit');
        $api->delete('/{post}', 'PostController@delete');
    });
});