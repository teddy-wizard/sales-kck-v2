<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAcSalesCreditNotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ac_sales_credit_notes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('customerId');
            $table->bigInteger('customerSubId');
            $table->bigInteger('docKey');
            $table->string('docNo');
            $table->datetime('docDate');
            $table->string('debtorCode');
            $table->string('debtorName')->nullable();
            $table->string('invAddr1')->nullable();
            $table->string('invAddr2')->nullable();
            $table->string('invAddr3')->nullable();
            $table->string('invAddr4')->nullable();
            $table->string('branchCode')->nullable();
            $table->string('salesLocation')->nullable();
            $table->string('displayTerm');
            $table->string('salesAgent')->nullable();
            $table->integer('inclusiveTax');
            $table->string('cnType')->nullable();
            $table->string('ourInvoiceNo')->nullable();
            $table->string('reason')->nullable();
            $table->string('total')->nullable();
            $table->string('netTotal')->nullable();
            $table->string('localNetTotal')->nullable();
            $table->string('analysisNetTotal')->nullable();
            $table->string('localAnalysisNetTotal')->nullable();
            $table->string('localTotalCost')->nullable();
            $table->string('tax')->nullable();
            $table->string('localTax')->nullable();
            $table->string('exTax')->nullable();
            $table->string('localExTax')->nullable();
            $table->string('totalExtax')->nullable();
            $table->string('taxableAmt')->nullable();
            $table->string('finalTotal')->nullable();
            $table->string('localTaxableAmt')->nullable();
            $table->integer('syncStatus')->nullable();
            $table->integer('cancelled');
            $table->integer('lastUpdate')->default(0);
            $table->integer('deleted')->default(0);
            $table->datetime('lastSyncAt')->nullable();
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
        Schema::dropIfExists('ac_sales_credit_notes');
    }
}
