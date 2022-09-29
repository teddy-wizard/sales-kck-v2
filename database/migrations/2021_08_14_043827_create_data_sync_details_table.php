<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDataSyncDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('data_sync_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('data_sync_api_id');
            $table->string('data_key');
            $table->string('api_path_ref')->nullable();
            $table->integer('sync_mode')->default(0);
            $table->datetime('first_sync_ts')->nullable();
            $table->datetime('last_full_sync_ts')->nullable();
            $table->datetime('last_success_sync_ts')->nullable();
            $table->integer('sync_attempts')->default(0);
            $table->datetime('last_sync_ts')->nullable();
            $table->integer('sync_interval')->default(0);
            $table->datetime('next_run_ts')->nullable();
            $table->integer('job_status')->default(0);
            $table->integer('active')->default(1);
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
        Schema::dropIfExists('data_sync_details');
    }
}
