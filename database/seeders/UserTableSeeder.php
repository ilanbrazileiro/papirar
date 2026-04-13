<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        DB::table('users')->insert([
            [
                'name' => 'Aluno',
                'email' => 'aluno@teste.com',
                'password' => bcrypt('12345678'),
                'role' => 'student',
                'is_active' => true
            ],
            [
                'name' => 'Admin',
                'email' => 'admin@teste.com',
                'password' => bcrypt('12345678'),
                'role' => 'admin',
                'is_active' => true
            ],
            [
                'name' => 'ilan',
                'email' => 'ilanbrazileiro@gmail.com',
                'password' => bcrypt('246135'),
                'role' => 'admin',
                'is_active' => true
            ]
        ]);
    }
}
