<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeeEducationsTable extends Migration
{
    public function up()
    {
        Schema::create('employee_educations', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('employee_id');

            $table->string('level', 50);
            $table->string('degree', 120);
            $table->string('institution', 150);
            $table->string('board_university', 150)->nullable();

            $table->unsignedSmallInteger('year_of_passing');

            $table->string('grade', 20)->nullable();

            $table->string('certificate_path')->nullable();
            $table->string('certificate_original_name')->nullable();

            $table->timestamps();

            $table->foreign('employee_id')
                ->references('id')
                ->on('employees')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('employee_educations');
    }
}