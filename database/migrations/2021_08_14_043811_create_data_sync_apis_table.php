<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDataSyncApisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('data_sync_apis', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('reference_key');
            $table->string('ref2')->nullable();
            $table->string('remarks')->nullable();
            $table->string('ver_number')->nullable();
            $table->string('base_url');
            $table->string('login_name');
            $table->string('login_pass_enc');
            $table->datetime('expiry_date')->nullable();
            $table->integer('expiry_notification_sent');
            $table->integer('log_level');
            $table->integer('active');
            $table->string('version_prefix');
            $table->string('api_type');
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
        Schema::dropIfExists('data_sync_apis');
    }
}
