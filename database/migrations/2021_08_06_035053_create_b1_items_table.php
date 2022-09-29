<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateB1ItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('b1_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger("companyId");
            $table->string("itemCode");
            $table->string("itemName")->nullable();
            $table->string("itemGroupCode")->nullable();
            $table->string("itemGroupName")->nullable();
            $table->string("taxType")->nullable();
            $table->string("taxRate")->nullable();
            $table->string("uom")->nullable();
            $table->string("sellingPrice")->nullable();
            $table->string("minSellingPrice")->nullable();
            $table->integer("active")->default(1);
            $table->integer("lastUpdate")->default(0);
            $table->integer("deleted")->default(0);
            $table->datetime("lastSyncAt")->nullable();
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
        Schema::dropIfExists('b1_items');
    }
}
