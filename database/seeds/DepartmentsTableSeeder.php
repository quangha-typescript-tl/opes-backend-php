<?php

use Illuminate\Database\Seeder;

class DepartmentsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('departments')->insert([[
            'departmentName' => 'BGD',
        ],[
            'departmentName' => 'HCNS',
        ],[
            'departmentName' => 'BU1',
        ],[
            'departmentName' => 'BU2',
        ],[
            'departmentName' => 'BU3',
        ],[
            'departmentName' => 'BU4',
        ]]);
    }
}
