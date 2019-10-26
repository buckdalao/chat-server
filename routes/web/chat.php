<?php
/**
 *  web chat manage
 */
Route::prefix('chat')->name('chat.')->middleware(['auth'])->group(function (){
    Route::get('/', 'IndexController@index')->name('home');
    Route::post('/init', 'IndexController@getApiToken')->name('getApiToken');
});
Route::prefix('system')->name('system.')->middleware(['auth'])->group(function (){
    Route::get('/', 'SystemController@index')->name('system');
});
