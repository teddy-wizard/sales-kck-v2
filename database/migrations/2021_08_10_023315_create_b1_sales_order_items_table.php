<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateB1SalesOrderItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('b1_sales_order_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('soHeaderId');
            $table->bigInteger('companyId');
            $table->string("sold");
            $table->integer('soLineNum');
            $table->string("soDocNum");
            $table->string("soItemCode");
            $table->string("soItemDesc");
            $table->string("soItemUom");
            $table->string("soItemPrice")->nullable();
            $table->string("soItemQuantity")->nullable();
            $table->string("soTaxCode");
            $table->string("soTaxRate");
            $table->string("soLineTotalBeforeTax");
            $table->string("soLineTotalAfterTax");
            $table->string("soLineTaxAmount");
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
        Schema::dropIfExists('b1_sales_order_items');
    }
}
