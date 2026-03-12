<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $param = [
            [
                'name' => '田中太郎',
                'email' => 'sample1@sample.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ],
            [
                'name' => '山田花子',
                'email' => 'sample2@sample.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ],
        ];
        DB::table('users')->insert($param);
    }
}
