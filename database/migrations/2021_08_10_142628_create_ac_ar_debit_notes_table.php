<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAcArDebitNotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ac_ar_debit_notes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('customerId');
            $table->bigInteger('customerSubId');
            $table->bigInteger('docKey');
            $table->string('docNo');
            $table->string('debtorCode');
            $table->string('journalType');
            $table->string('dnType')->nullable();
            $table->string('reference')->nullable();
            $table->datetime('docDate');
            $table->string('displayTerm');
            $table->datetime('dueDate');
            $table->string('description')->nullable();
            $table->string('salesAgent')->nullable();
            $table->string('currencyCode');
            $table->string('currencyRate');
            $table->string('outstanding')->nullable();
            $table->integer('cancelled');
            $table->integer('isJournal');
            $table->datetime('lastModified');
            $table->string('sourceType')->nullable();
            $table->bigInteger('sourceKey')->nullable();
            $table->string('branchCode')->nullable();
            $table->string('ourInvoiceNo')->nullable();
            $table->string('reason')->nullable();
            $table->integer('inclusiveTax');
            $table->integer('roundingMethod');
            $table->string('knockoffType')->nullable();
            $table->datetime('pdcDisDueDate')->nullable();
            $table->string('pdcDisAmt')->nullable();
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
        Schema::dropIfExists('ac_ar_debit_notes');
    }
}
