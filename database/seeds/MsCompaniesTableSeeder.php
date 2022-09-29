<?php

use App\MsCompany;
use Illuminate\Database\Seeder;

class MsCompaniesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $companies = array(
            array('id' => '1','name' => 'CKSS (SIT)','code' => 'CKSS_SIT', 'extId' => '17', 'sysType' => 'ATCNT'),
            array('id' => '2','name' => 'CKSS (UAT)','code' => 'CKSS_UAT', 'extId' => '19','sysType' => 'ATCNT'),
            array('id' => '5','name' => 'Keluarga CKSS Sdn Bhd','code' => 'CKSS', 'extId' => '36','sysType' => 'ATCNT', 'displayName'=>'CAP KELUARGA Sales &\n Services SDN. BHD', 'address' => '286-A, Gurdwara Road (Brick Kiln Road),\n 10300 Penang Malaysia', 'phone' => '[604] 263 1687', 'fax' => '[604] 263 1260', 'email' => 'cksspg@yahoo.com', 'website' => 'www.capkeluarga.com', 'lbbNo' => '3361', 'gstRegNo' => '1851006976'),
            array('id' => '8','name' => 'Keluarga CTH','code' => 'CTH', 'extId' => '44','sysType' => 'ATCNT'),
            array('id' => '17','name' => 'Keluarga HARMONI','code' => 'HARMONI', 'extId' => '45','sysType' => 'ATCNT', 'displayName'=>'KELUARGA HARMONI\n SDN. BHD'),
            array('id' => '18','name' => 'Keluarga Utama','code' => 'UTAMA', 'extId' => '58','sysType' => 'ATCNT', 'displayName'=>'KELUARGA UTAMA\n SDN. BHD'),
            array('id' => '19','name' => 'Keluaga Skawan','code' => 'SKAWAN', 'extId' => '47','sysType' => 'ATCNT', 'displayName'=>'SKAWAN SDN. BHD'),
            array('id' => '20','name' => 'Keluarga Sentral','code' => 'SENTRAL', 'extId' => '93','sysType' => 'ATCNT', 'displayName'=>'KELUARGA SENTRAL\n SDN. BHD'),

        );
        foreach($companies as $company){
            MsCompany::create($company);
        }
    }
}
