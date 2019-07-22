<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            [
                'userName'       => 'admin',
                'email'          => 'admin@gmail.com',
                'password'       => bcrypt('123456'),
                'department'     => 1,
                'remember_token' => '',
                'created_at'     => Carbon::now()->format('Y-m-d H:i:s'),
                'updated_at'     => Carbon::now()->format('Y-m-d H:i:s'),
            ], [
                'userName'       => 'anhbbang',
                'email'          => 'anhbang@gmail.com',
                'password'       => bcrypt('123456'),
                'department'     => 2,
                'remember_token' => '',
                'created_at'     => Carbon::now()->format('Y-m-d H:i:s'),
                'updated_at'     => Carbon::now()->format('Y-m-d H:i:s'),
            ]
        ]);
    }
}
