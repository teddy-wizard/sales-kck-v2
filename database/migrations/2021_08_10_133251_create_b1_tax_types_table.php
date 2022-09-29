<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateB1TaxTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('b1_tax_types', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('companyId');
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
        Schema::dropIfExists('b1_tax_types');
    }
}
