<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMsSalesPeopleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ms_sales_people', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger("userId");                  //users table id
            $table->string("remarks")->nullable();         //???
            $table->string("salesArea")->nullable();       // sales area table name
            $table->integer("managerId");                  // users table id (how to get -- ??)
            $table->string("monthTarget");                 // month sales target
            $table->string("code");                        //Document Code
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
        Schema::dropIfExists('ms_sales_people');
    }
}
