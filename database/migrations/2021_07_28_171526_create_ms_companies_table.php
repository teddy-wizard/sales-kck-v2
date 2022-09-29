<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMsCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ms_companies', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string("name");
            $table->string("code");
            $table->string("sysType");              //connected external site ATCNT - accountcount, B
            $table->string("extId")->nullable();    //Customer Id
            $table->string("extRef1")->nullable();
            $table->string("extRef2")->nullable();
            $table->string("displayName")->nullable();
            $table->string("address")->nullable();
            $table->string("phone")->nullable();
            $table->string("fax")->nullable();
            $table->string("email")->nullable();
            $table->string("website")->nullable();
            $table->string("lbbNo")->nullable();
            $table->string("gstRegNo")->nullable();
            $table->string("addtionalInfo")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ms_companies');
    }
}
