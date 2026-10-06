<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeeBankDetailsTable extends Migration
{
    public function up()
    {
        Schema::create('employee_bank_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('employee_id')->unique();
            $table->string('account_holder', 120);
            $table->text('account_number_encrypted');
            $table->string('bank_name', 120);
            $table->string('ifsc_code', 20);
            $table->string('branch', 120)->nullable();
            $table->string('account_type', 30)->nullable();
            $table->timestamps();
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('restrict');
        });
    }

    public function down()
    {
        Schema::dropIfExists('employee_bank_details');
    }
}
