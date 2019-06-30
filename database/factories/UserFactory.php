<?php

use Illuminate\Support\Str;
use Faker\Generator as Faker;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(App\Models\Chat\User::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'email' => '704927525@qq.com',
        'email_verified_at' => now(),
        'password' => \Illuminate\Support\Facades\Hash::make('123123'), // secret
        'remember_token' => Str::random(10),
        'mb_prefix' => '+86',
        'phone' => 13026161010,
        'photo' => 'storage/photos/photo.jpg',
        'chat_number' => 1000001
    ];
});
