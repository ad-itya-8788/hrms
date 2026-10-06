<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeeExperiencesTable extends Migration
{
    public function up()
    {
        Schema::create('employee_experiences', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('employee_id');
            $table->string('company_name', 120);
            $table->string('job_title', 120);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->text('summary')->nullable();
            $table->timestamps();
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('restrict');
            $table->index(['employee_id', 'start_date']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('employee_experiences');
    }
}
