<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAcPdcDtlsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ac_pdc_dtls', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('customerId');
            $table->bigInteger('pdcId');
            $table->bigInteger('pdcDetailsId');
            $table->string('paymentMethod')->nullable();
            $table->string('chequeNo')->nullable();
            $table->string('paymentAmount')->nullable();
            $table->integer('isRCHQ')->default(0);
            $table->datetime('rchqDate')->nullable();
            $table->string('bankCharge')->nullable();
            $table->string('toBankRate')->nullable();
            $table->string('bankChargeTaxCode')->nullable();
            $table->string('bankChargeTax')->nullable();
            $table->string('bankChargeBillNoGST')->nullable();
            $table->string('paymentBy')->nullable();
            $table->string('bankChargeTaxRate')->nullable();
            $table->string('bankChargeProjNo')->nullable();
            $table->bigInteger('pdcHeaderId')->nullable();
            $table->integer('deleted')->default(0);
            $table->timestamps();
            $table->datetime('lastSyncAt')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ac_pdc_dtls');
    }
}
