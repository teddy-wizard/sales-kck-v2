<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAcDebtorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ac_debtors', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('customerId');
            $table->string("accNo");
            $table->string("companyName")->nullable();
            $table->string("desc2")->nullable();
            $table->string("registrationNo")->nullable();
            $table->string("address1")->nullable();
            $table->string("address2")->nullable();
            $table->string("address3")->nullable();
            $table->string("address4")->nullable();
            $table->string("postCode")->nullable();
            $table->string("deliverAddr1")->nullable();
            $table->string("deliverAddr2")->nullable();
            $table->string("deliverAddr3")->nullable();
            $table->string("deliverAddr4")->nullable();
            $table->string("deliverPostCode")->nullable();
            $table->string("attention")->nullable();
            $table->string("phone1")->nullable();
            $table->string("phone2")->nullable();
            $table->string("fax1")->nullable();
            $table->string("fax2")->nullable();
            $table->string("areaCode")->nullable();
            $table->string("salesAgent")->nullable();
            $table->string("debtorType")->nullable();
            $table->string("displayTerm");
            $table->string("taxType")->nullable();
            $table->string("taxRegisterNo")->nullable();
            $table->string("priceCategory")->nullable();
            $table->integer("active")->default(1);
            $table->integer("apiStatus")->default(1);
            $table->integer("lastUpdate")->default(0);
            $table->integer("deleted")->default(0);
            $table->timestamp("lastSyncAt")->nullable();
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
        Schema::dropIfExists('ac_debtors');
    }
}
