<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAcPdcKnockoffDtlsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ac_pdc_knockoff_dtls', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('customerId');
            $table->bigInteger('pdcId');
            $table->bigInteger('pdcKnockOffId');
            $table->string('docType')->nullable();
            $table->bigInteger('docKey')->nullable();
            $table->string('paidAmount')->nullable();
            $table->string('discountAmount')->nullable();
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
        Schema::dropIfExists('ac_pdc_knockoff_dtls');
    }
}
