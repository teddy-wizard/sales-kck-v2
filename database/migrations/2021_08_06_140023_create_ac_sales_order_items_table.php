<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAcSalesOrderItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ac_sales_order_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('customerId');
            $table->bigInteger("soId");
            $table->bigInteger("dtlKey");
            $table->bigInteger("docKey");
            $table->string("itemCode")->nullable();
            $table->string("location")->nullable();
            $table->string("description")->nullable();
            $table->string("furtherDescription")->nullable();
            $table->string("uom")->nullable();
            $table->string("rate")->nullable();
            $table->string("qty")->nullable();
            $table->string("transferedQty")->nullable();
            $table->string("smallestUnitPrice")->nullable();
            $table->string("unitPrice")->nullable();
            $table->string("discount")->nullable();
            $table->string("discountAmt")->nullable();
            $table->string("taxType")->default(0);
            $table->string("taxRate")->nullable();
            $table->string("fromDocType")->nullable();
            $table->string("fromDocNo")->nullable();
            $table->bigInteger("fromDocKey")->nullable();
            $table->bigInteger("fromDtlKey")->nullable();
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
        Schema::dropIfExists('ac_sales_order_items');
    }
}
