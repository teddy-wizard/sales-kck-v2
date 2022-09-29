<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAcArInvoiceDtlsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ac_ar_invoice_dtls', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('customerId');
            $table->bigInteger('arInvoiceId');
            $table->bigInteger('dtlKey');
            $table->bigInteger('docKey')->nullable();
            $table->integer('seq');
            $table->string('accNo')->nullable();
            $table->string('toAccountRate');
            $table->string('description')->nullable();
            $table->string('projNo')->nullable();
            $table->string('deptNo')->nullable();
            $table->string('taxType')->nullable();
            $table->string('taxRate');
            $table->string('sourceDtlType')->nullable();
            $table->string('sourceDtlKey')->nullable();
            $table->string('supplyPurchase')->nullable();
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
        Schema::dropIfExists('ac_ar_invoice_dtls');
    }
}
