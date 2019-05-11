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
    }
}
