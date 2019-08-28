<?php

use Illuminate\Database\Seeder;

class AuthorityTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('authority')->insert([
            [
                'code'              => 1,
                'name'              => 'AU_1',
                'description'       => 'admin content',
            ]
        ]);
    }
}
