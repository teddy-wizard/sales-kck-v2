<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateB1CustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('b1_customers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger("companyId");
            $table->string("customerCode");
            $table->string("customerName")->nullable();
            $table->string("phone1")->nullable();
            $table->string("phone2")->nullable();
            $table->string("fax")->nullable();
            $table->string("salesAgentCode")->nullable();
            $table->string("salesAgentName")->nullable();
            $table->string("pymntGroupID");
            $table->string("pymntGroup");
            $table->string("taxType")->nullable();
            $table->string("taxRate")->nullable();
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
        Schema::dropIfExists('b1_customers');
    }
}
