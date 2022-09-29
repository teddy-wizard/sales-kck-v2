<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateB1CustomerAddressesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('b1_customer_addresses', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger("custId");
            $table->bigInteger("companyId");
            $table->string("customerCode");
            $table->string("addressID");
            $table->string("addressType");
            $table->string("fullAddress")->nullable();
            $table->integer("isPrimary");
            $table->string("street")->nullable();
            $table->string("streetNo")->nullable();
            $table->string("block")->nullable();
            $table->string("city")->nullable();
            $table->string("country")->nullable();
            $table->string("state")->nullable();
            $table->string("zipCode")->nullable();
            $table->string("country1")->nullable();
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
        Schema::dropIfExists('b1_customer_addresses');
    }
}
