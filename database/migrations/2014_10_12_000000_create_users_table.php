<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('username')->unique();
            $table->string('email');
            $table->string('name');
            $table->string('password');
            $table->string('company_ids');
            $table->string("must_change")->default(0);
            $table->integer('status')->default(0);
            $table->datetime('last_login_dt')->nullable();
            $table->bigInteger("createdBy")->nullable();
            $table->bigInteger("updatedBy")->nullable();
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
        Schema::dropIfExists('users');
    }
}
