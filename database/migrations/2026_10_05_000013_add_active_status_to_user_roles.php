<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddActiveStatusToUserRoles extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('user_roles', 'is_active')) {
            Schema::table('user_roles', function (Blueprint $table) {
                $table->boolean('is_active')->default(true);
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('user_roles', 'is_active')) {
            Schema::table('user_roles', function (Blueprint $table) {
                $table->dropColumn('is_active');
            });
        }
    }
}
