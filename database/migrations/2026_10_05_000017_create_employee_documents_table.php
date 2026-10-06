<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeeDocumentsTable extends Migration
{
    public function up()
    {
        Schema::create('employee_documents', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('employee_id');
            $table->string('title', 100);
            $table->string('original_name', 255);
            $table->string('storage_path', 255)->unique();
            $table->string('mime_type', 120);
            $table->unsignedInteger('file_size');
            $table->timestamps();
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('restrict');
            $table->index('employee_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('employee_documents');
    }
}
