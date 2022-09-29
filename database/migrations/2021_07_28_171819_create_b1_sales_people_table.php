<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateB1SalesPeopleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('b1_sales_people', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger("companyId");
            $table->string("salesAgentCode");
            $table->string("salesAgentName")->nullable();
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
        Schema::dropIfExists('b1_sales_people');
    }
}
