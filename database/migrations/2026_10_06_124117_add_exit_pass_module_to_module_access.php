<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddExitPassModuleToModuleAccess extends Migration
{
    /**
     * Add Exit Pass module for all existing roles.
     *
     * Permissions are disabled by default.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('module_access') || !Schema::hasTable('user_roles')) {
            return;
        }

        $roles = DB::table('user_roles')
            ->select('id')
            ->get();

        foreach ($roles as $role) {
            $exists = DB::table('module_access')
                ->where('role_id', $role->id)
                ->where('module_name', 'exit_pass')
                ->exists();

            if (!$exists) {
                DB::table('module_access')->insert([
                    'role_id' => $role->id,
                    'module_name' => 'exit_pass',
                    'can_view' => false,
                    'can_create' => false,
                    'can_edit' => false,
                    'can_delete' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Remove only Exit Pass permissions.
     *
     * @return void
     */
    public function down()
    {
        if (!Schema::hasTable('module_access')) {
            return;
        }

        DB::table('module_access')
            ->where('module_name', 'exit_pass')
            ->delete();
    }
}