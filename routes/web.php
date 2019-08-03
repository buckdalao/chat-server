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
})->name('home');
Route::get('auth/login', 'Auth\LoginController@loginPage')->name('login');
Route::post('auth/login', 'Auth\LoginController@login')->name('auth.login');
Route::post('auth/logout', 'Auth\LoginController@logout')->name('logout');
