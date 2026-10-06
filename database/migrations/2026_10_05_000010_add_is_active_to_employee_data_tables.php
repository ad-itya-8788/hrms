<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsActiveToEmployeeDataTables extends Migration
{
    public function up()
    {
        foreach (['employees', 'employee_types', 'employee_roles'] as $tableName) {
            if (!Schema::hasColumn($tableName, 'is_active')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->boolean('is_active')->default(true);
                });
            }
        }
    }

    public function down()
    {
        foreach (['employee_roles', 'employee_types', 'employees'] as $tableName) {
            if (Schema::hasColumn($tableName, 'is_active')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropColumn('is_active');
                });
            }
        }
    }
}
