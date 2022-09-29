<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAcStockItemUomsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ac_stock_item_uoms', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger("customerId");
            $table->bigInteger("itemId");
            $table->string("itemCode");
            $table->string("uom");
            $table->string("barcode")->nullable();
            $table->string("rate");
            $table->string("price")->nullable();
            $table->string("cost")->nullable();
            $table->string("realCost")->nullable();
            $table->string("minSalePrice")->nullable();
            $table->string("maxSalePrice")->nullable();
            $table->string("balQty");
            $table->string("focLevel")->nullable();
            $table->string("focQty")->nullable();
            $table->string("weight")->nullable();
            $table->string("weightUom")->nullable();
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
        Schema::dropIfExists('ac_stock_item_uoms');
    }
}
