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

Route::get('/', function () {
    return view('welcome');
});
Route::get('resources/{one?}/{two?}/{three?}/{four?}/{five?}/{six?}/{seven?}/{eight?}/{nine?}', 'Util\ResourceController@getImage')->name('web.getImage');
Route::post('auth/login', 'Auth\LoginController@login')->name('auth.login');
Route::prefix('chat')->namespace('Chat')->group(function (){
    Route::get('/', 'IndexController@home')->middleware('guest')->name('chat.login');
    Route::group([
        'middleware' => ['auth']
    ], function (){
        Route::get('room', 'RoomController@index')->name('chat.room');
    });
});
