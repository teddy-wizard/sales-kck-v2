<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAcTaxTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ac_tax_types', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('customerId');
            $table->string('code');
            $table->string('description')->nullable();
            $table->string('taxRate');
            $table->integer('inclusive');
            $table->string('taxTypeCategory')->nullable();
            $table->string('irasTaxCode')->nullable();
            $table->string('supplyPurchase');
            $table->string('taxAccNo')->nullable();
            $table->integer('taxDefault');
            $table->integer('zeroRate');
            $table->integer('useTrxTaxAccNo');
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
        Schema::dropIfExists('ac_tax_types');
    }
}
