<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRptProductMappingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rpt_product_mappings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('productId')->nullable();
            $table->string('productName');
            $table->integer('customerId');
            $table->string('itemCode');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rpt_product_mappings');
    }
}
