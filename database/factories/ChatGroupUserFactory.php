<?php

use Faker\Generator as Faker;

$factory->define(App\Models\Chat\ChatGroupUser::class, function (Faker $faker) {
    return [
        'group_id'        => 1,
        'user_id'         => 1,
        'group_user_name' => $faker->name()
    ];
});
