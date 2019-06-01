<?php

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

$factory->define(App\Models\Chat\ChatGroup::class, function (Faker $faker) {
    return [
        'group_name'   => '公共群',
        'user_id'      => 1,
        'photo'        => 'storage/photos/group_photo.jpg',
        'group_number' => 100000 + 1
    ];
});
