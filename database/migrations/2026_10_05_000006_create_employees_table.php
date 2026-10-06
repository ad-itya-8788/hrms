<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeesTable extends Migration
{
    public function up()
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('employee_code', 20)->unique();
            $table->string('first_name', 80);
            $table->string('last_name', 80);
            $table->string('email', 190)->unique();
            $table->string('phone', 20)->nullable();
            $table->string('gender', 20)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->unsignedBigInteger('department_id');
            $table->unsignedBigInteger('employee_type_id');
            $table->unsignedBigInteger('employee_role_id');
            $table->unsignedBigInteger('manager_id')->nullable();
            $table->string('employment_status', 20)->default('active');
            $table->date('joining_date');
            $table->string('address_line', 190)->nullable();
            $table->string('city', 80)->default('Pune');
            $table->string('state', 80)->default('Maharashtra');
            $table->string('postal_code', 10)->nullable();
            $table->timestamps();
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('restrict');
            $table->foreign('employee_type_id')->references('id')->on('employee_types')->onDelete('restrict');
            $table->foreign('employee_role_id')->references('id')->on('employee_roles')->onDelete('restrict');
            $table->foreign('manager_id')->references('id')->on('employees')->onDelete('set null');
            $table->index(['department_id', 'employment_status']);
            $table->index(['last_name', 'first_name'], 'employees_name_sort_idx');
            $table->index(['employment_status', 'joining_date'], 'employees_status_joined_idx');
        });
    }

    public function down()
    {
        Schema::dropIfExists('employees');
    }
}
