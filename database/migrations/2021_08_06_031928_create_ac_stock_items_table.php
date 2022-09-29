<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAcStockItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ac_stock_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger("customerId");
            $table->bigInteger("docKey");
            $table->string("itemCode");
            $table->string("description")->nullable();
            $table->string("desc2")->nullable();
            $table->string("itemGroup")->nullable();
            $table->string("itemType")->nullable();
            $table->string("taxType")->nullable();
            $table->string("purchaseTaxType")->nullable();
            $table->string("salesUOM");
            $table->string("purchaseUOM");
            $table->string("reportUOM");
            $table->string("baseUOM");
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
        Schema::dropIfExists('ac_stock_items');
    }
}
