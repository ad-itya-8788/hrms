<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHeadEmployeeIdToDepartmentsTable extends Migration
{
    public function up()
    {
        Schema::table('departments', function (Blueprint $table) {

            $table->unsignedBigInteger('head_employee_id')
                ->nullable()
                ->after('contact_no');

            $table->foreign('head_employee_id')
                ->references('id')
                ->on('employees')
                ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('departments', function (Blueprint $table) {

            $table->dropForeign(['head_employee_id']);
            $table->dropColumn('head_employee_id');
        });
    }
}