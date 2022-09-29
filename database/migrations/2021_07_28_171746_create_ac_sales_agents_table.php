<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAcSalesAgentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ac_sales_agents', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger("customerId");
            $table->string("salesAgent");
            $table->string("description");
            $table->string("desc2")->nullable();
            $table->integer("lastUpdate")->default(0);
            $table->integer("active")->default(0);
            $table->integer("deleted")->default(0);
            $table->string("apiStatus")->nullable();
            $table->integer("subscrit")->default(0);
            $table->timestamp("lastSyncAt");
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
        Schema::dropIfExists('ac_sales_agents');
    }
}
