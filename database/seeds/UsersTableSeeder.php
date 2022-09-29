<?php

use App\User;
use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'username'=>'admin',
            'email'=>'admin@admin.com',
            'name'=>'admin',
            'company_ids'=>'1,2',
            'status'=>'1',
            'password'=>  bcrypt('admin!@#123')
        ]);

        User::create([
            'username'=>'salesman',
            'email'=>'salesman@test.com',
            'name'=>'salesman',
            'company_ids'=>'1',
            'status'=>'1',
            'password'=>  bcrypt('admin!@#123')
        ]);

        User::create([
            'username'=>'sales_manager',
            'email'=>'sales_manager@test.com',
            'name'=>'sales_manager',
            'company_ids'=>'2',
            'status'=>'1',
            'password'=>  bcrypt('admin!@#123')
        ]);
    }
}
