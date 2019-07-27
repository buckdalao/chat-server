<?php
/**
 *  web chat manage
 */
Route::prefix('chat')->name('chat.')->middleware(['auth'])->group(function (){
    Route::get('/', 'IndexController@index')->name('home');
});