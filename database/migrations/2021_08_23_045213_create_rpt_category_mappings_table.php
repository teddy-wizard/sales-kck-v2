<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRptCategoryMappingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rpt_category_mappings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('categoryId')->nullable();
            $table->string('rptCategory');
            $table->integer('companyId');
            $table->string('itemCode');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rpt_category_mappings');
    }
}
