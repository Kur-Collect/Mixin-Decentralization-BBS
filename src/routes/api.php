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


    $api->group([
        'prefix' => '/posts',
    ], function ($api) {
        $api->get('/', 'PostController@index')
            ->name('api.post.index');
        $api->get('/{traceId}', 'PostController@show')
            ->name('api.post.show');
        $api->post('/', 'PostController@store')
            ->name('api.post.store');
        $api->patch('/{traceId}', 'PostController@edit')
            ->name('api.post.edit');
        $api->delete('/{traceId}', 'PostController@delete')
            ->name('api.post.delete');

        $api->get('/{traceId}/comment', 'CommentController@show')
            ->name('api.comment.show');
        $api->patch('/{traceId}/comment', 'CommentController@store')
            ->name('api.comment.store');
    });
});
