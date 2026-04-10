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
                'name' => '西伶奈',
                'email' => 'reina.nishi@coachtech.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ],
            [
                'name' => '山田太郎',
                'email' => 'taro.yamada@coachtech.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ],
            [
                'name' => '増田一世',
                'email' => 'issei.masuda@coachtech.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ],
            [
                'name' => '山本敬吉',
                'email' => 'keikichi.yamamoto@coachtech.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ],
            [
                'name' => '秋田朋美',
                'email' => 'tomomi.akita@coachtech.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ],
            [
                'name' => '中西教夫',
                'email' => 'norio.nakanishi@coachtech.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ],
        ];
        DB::table('users')->insert($param);
    }
}
