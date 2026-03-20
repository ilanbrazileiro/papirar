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
                'username' => 'user01@gmail.com',
                'password' => bcrypt('abcd1234'),
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'username' => 'user02@gmail.com',
                'password' => bcrypt('abcd1234'),
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'username' => 'user03@gmail.com',
                'password' => bcrypt('abcd1234'),
                'created_at' => date('Y-m-d H:i:s')
            ]
        ]);
    }
}
