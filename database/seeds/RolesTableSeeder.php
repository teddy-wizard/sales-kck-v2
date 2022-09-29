<?php

use App\Roles;
use Illuminate\Database\Seeder;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $roles = array(
            array('id' => '1','name' => 'System Admin','description' => 'User account with System Administrator Role shall be able to configure the Mobile Sales application and system settings'),
            array('id' => '2','name' => 'User Admin','description' => 'User account with User Administrator Role shall be able to create and manage user accounts for the Sales Admin Portal'),
            array('id' => '3','name' => 'Ops','description' => 'User account with Operations Role shall be able to generate reports'),
            array('id' => '4','name' => 'Salesman','description' => 'User account with Sales Role shall be able to use the Mobile Sales App.  The Sales Role shall not have Admin Portal access right, only Mobile Sales App access right.'),
            array('id' => '5','name' => 'Sales Manager','description' => 'User with this role shall be able to user the Mobile Sales App, in addition, he/she can have salespersons assigned to him/her.')
        );
        foreach($roles as $role){
            Roles::create($role);
        }
    }
}
