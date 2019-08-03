<?php
/**
 *  web resource
 */
// 图片资源
Route::get('resources/{one?}/{two?}/{three?}/{four?}/{five?}/{six?}/{seven?}/{eight?}/{nine?}', 'Util\ResourceController@getImage')->name('resources.getImage');
// 录音媒体资源
Route::get('media/audio/{one?}/{two?}/{three?}/{four?}/{five?}/{six?}/{seven?}/{eight?}/{nine?}', 'Util\ResourceController@getRecorder')->name('media.getRecorder');