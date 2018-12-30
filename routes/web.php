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

Route::get('/', 'PostController@index')->name('post.index');

Route::prefix('post')->group(function () {
    Route::get('/{traceId}', 'PostController@show')->name('post.show');
    Route::post('/', 'PostController@store')->name('post.store');
    Route::patch('/{traceId}', 'PostController@edit')->name('post.edit');
    Route::delete('/{traceId}', 'PostController@delete')->name('post.delete');

    Route::get('/{traceId}/comment', 'CommentController@show')->name('comment.show');
    Route::patch('/{traceId}/comment', 'CommentController@store')->name('comment.store');
});