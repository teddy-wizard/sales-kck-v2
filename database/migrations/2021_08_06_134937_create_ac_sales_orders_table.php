<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAcSalesOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ac_sales_orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('customerId');
            $table->integer("customerSubId");
            $table->integer("docKey");
            $table->string("docNo");
            $table->datetime("docDate")->nullable();
            $table->string("debtorCode")->nullable();
            $table->string("debtorName")->nullable();
            $table->string("invAddr1")->nullable();
            $table->string("invAddr2")->nullable();
            $table->string("invAddr3")->nullable();
            $table->string("invAddr4")->nullable();
            $table->string("branchCode")->nullable();
            $table->string("salesLocation")->nullable();
            $table->string("displayTerm");
            $table->string("salesAgent")->nullable();
            $table->string("shipVia")->nullable();
            $table->string("shipInfo")->nullable();
            $table->integer("inclusiveTax")->default(0);
            $table->string("remark1")->nullable();
            $table->string("remark2")->nullable();
            $table->string("remark3")->nullable();
            $table->string("remark4")->nullable();
            $table->string("toDocType")->nullable();
            $table->bigInteger("toDocKey")->nullable();
            $table->bigInteger("toDtlKey")->nullable();
            $table->integer("cancelled")->default(0);
            $table->integer("lastUpdate")->default(0);
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
        Schema::dropIfExists('ac_sales_orders');
    }
}
