<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('T3stabcde#'),
            'is_admin' => true,
        ]);

        User::create([
            'name' => 'User Test',
            'email' => 'user@example.com',
            'password' => Hash::make('T3stabcde#'),
        ]);
    }
}
