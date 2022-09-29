<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAcPdcHeadersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ac_pdc_headers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('customerId');
            $table->bigInteger('customerSubId');
            $table->bigInteger('pdcHeaderId');
            $table->bigInteger('docFormatId');
            $table->string('docFormat');
            $table->bigInteger('docFormatNo');
            $table->string('debtorCode')->nullable();
            $table->string('projectCode')->nullable();
            $table->string('departmentCode')->nullable();
            $table->string('currencyCode')->nullable();
            $table->string('docNo')->nullable();
            $table->string('description')->nullable();
            $table->datetime('receivedDate')->nullable();
            $table->datetime('chequeDate')->nullable();
            $table->string('secondReceiptNo')->nullable();
            $table->bigInteger('aRPaymentDocKey')->nullable();
            $table->string('createdBy')->nullable();
            $table->datetime('createdDate')->nullable();
            $table->string('modifiedBy')->nullable();
            $table->datetime('modifiedDate')->nullable();
            $table->integer('cancelled')->default(0);
            $table->integer('lastUpdate')->default(0);
            $table->integer('deleted')->default(0);
            $table->timestamps();
            $table->datetime('lastSyncAt')->nullable();
            $table->string('salesAgent')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ac_pdc_headers');
    }
}
