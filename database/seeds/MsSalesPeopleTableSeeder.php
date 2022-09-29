<?php

use App\MsCompany;
use App\MsSalesPeople;
use Illuminate\Database\Seeder;

class MsSalesPeopleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $rows = array(
            array('id' => '1','userId' => '2','salesArea' => '2','managerId' => '3','monthTarget' => '100'),
            array('id' => '2','userId' => '3','salesArea' => '3','managerId' => '3','monthTarget' => '300')
        );
        foreach($rows as $row){
            MsSalesPeople::create($row);
        }
    }
}
