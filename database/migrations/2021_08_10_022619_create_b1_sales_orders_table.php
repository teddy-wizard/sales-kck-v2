<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateB1SalesOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('b1_sales_orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string("sold");
            $table->bigInteger('companyId');
            $table->string("soDocNum");
            $table->string("soCustomerCode");
            $table->string("soCustomerName");
            $table->datetime("soDocDate")->nullable();
            $table->string("soSalesPersonCode");
            $table->string("soSalesPerson");
            $table->string("soPaymentTermCode");
            $table->string("soPaymentTerm");
            $table->string("soBillToCode");
            $table->string("soBillAddress");
            $table->string("soShipToCode");
            $table->string("soShipAddress");
            $table->string("soStatus");
            $table->datetime("soDeliveryDate")->nullable();
            $table->string("soComment");
            $table->string("soTotalBeforeTax");
            $table->string("soTotalAfterTax");
            $table->string("soTaxAmount");
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
        Schema::dropIfExists('b1_sales_orders');
    }
}
