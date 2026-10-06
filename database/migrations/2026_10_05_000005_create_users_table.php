<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 160);
            $table->string('email', 190)->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->unsignedBigInteger('role_id')->index();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->foreign('role_id')->references('id')->on('user_roles')->onDelete('restrict');
            $table->index(['role_id', 'is_active'], 'users_role_active_idx');
        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
}
