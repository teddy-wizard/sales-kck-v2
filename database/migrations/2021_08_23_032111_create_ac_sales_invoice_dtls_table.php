<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAcSalesInvoiceDtlsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ac_sales_invoice_dtls', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('customerId');
            $table->bigInteger('invoiceId');
            $table->bigInteger('dtlKey');
            $table->bigInteger('focdtlkey')->nullable();
            $table->bigInteger('docKey');
            $table->string('itemCode')->nullable();
            $table->string('location')->nullable();
            $table->string('description')->nullable();
            $table->string('furtherDescription')->nullable();
            $table->string('uom')->nullable();
            $table->string('rate')->nullable();
            $table->string('qty')->nullable();
            $table->string('transferedQty')->nullable();
            $table->string('smallestUnitPrice')->nullable();
            $table->string('unitPrice')->nullable();
            $table->string('discount')->nullable();
            $table->string('discountAmt')->nullable();
            $table->string('taxType')->nullable();
            $table->string('taxRate')->nullable();
            $table->string('fromDocType')->nullable();
            $table->string('fromDocNo')->nullable();
            $table->bigInteger('fromDocTtlKey')->nullable();
            $table->string('tax')->nullable();
            $table->string('subTotal')->nullable();
            $table->string('localSubTotal')->nullable();
            $table->string('localTotalCost')->nullable();
            $table->string('localFocTotalCost')->nullable();
            $table->string('subTotalExTax')->nullable();
            $table->string('localTax')->nullable();
            $table->string('taxableAmt')->nullable();
            $table->string('localSubTotalExTax')->nullable();
            $table->string('localTaxableAmt')->nullable();
            $table->integer('deleted')->default(0);
            $table->timestamps();
            $table->datetime('lastSyncAt')->nullable();
            $table->string('focQty')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ac_sales_invoice_dtls');
    }
}
