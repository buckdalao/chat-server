<?php
/**
 *  web resource
 */
Route::get('resources/{one?}/{two?}/{three?}/{four?}/{five?}/{six?}/{seven?}/{eight?}/{nine?}', 'Util\ResourceController@getImage')->name('web.getImage');
Route::get('media/audio/{one?}/{two?}/{three?}/{four?}/{five?}/{six?}/{seven?}/{eight?}/{nine?}', 'Util\ResourceController@getRecorder')->name('web.getRecorder');