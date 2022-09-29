<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMsSalesPersonMappingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ms_sales_person_mappings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer("salespersonId");
            $table->integer("companyId");
            $table->integer("acSalesAgentId");
            $table->integer("b1SalespersonId");
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
        Schema::dropIfExists('ms_sales_person_mappings');
    }
}
