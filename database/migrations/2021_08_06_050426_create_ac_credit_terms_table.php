<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAcCreditTermsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ac_credit_terms', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger("customerId");
            $table->string("displayTerm");
            $table->string("terms")->nullable();
            $table->string("termType")->nullable();
            $table->integer("termDays")->nullable();
            $table->integer("discountDays")->nullable();
            $table->string("discountPercent")->nullable();
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
        Schema::dropIfExists('ac_credit_terms');
    }
}
