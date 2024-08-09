<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();

        // Create Users
        User::create([
            'name'      => 'Developer ID',
            'username'  => 'developer',
            'email'     => 'developer.id@gmail.test',
            'password'  => bcrypt('rahasia')
        ]);

        User::create([
            'name'      => 'Seira Az-zahra',
            'username'  => 'seira',
            'email'     => 'seira.az@gmail.test',
            'password'  => bcrypt('rahasia')
        ]);
    }
}
