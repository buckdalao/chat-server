<?php

use Illuminate\Database\Seeder;

class ChatGroupTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(App\Models\Chat\ChatGroup::class, 1)->create();
        factory(App\Models\Chat\User::class, 1)->create();
        factory(App\Models\Chat\ChatGroupUser::class, 1)->create();
    }
}
