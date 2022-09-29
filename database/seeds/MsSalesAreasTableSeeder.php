<?php

use App\MsSalesArea;
use Illuminate\Database\Seeder;

class MsSalesAreasTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $sales_areas = array(
            array('name' => "B'WORTH"),
            array('name' => 'KEDAH'),
            array('name' => 'PENANG'),
            array('name' => 'IPOH'),

        );
        foreach($sales_areas as $sales_area){
            MsSalesArea::create($sales_area);
        }
    }
}
