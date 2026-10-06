<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddLeavesModuleToModuleAccess extends Migration
{
    /**
     * Add the Leaves module for all existing user roles.
     *
     * New permissions are disabled by default.
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
                ->where('module_name', 'leaves')
                ->exists();

            if (!$exists) {
                DB::table('module_access')->insert([
                    'role_id' => $role->id,
                    'module_name' => 'leaves',
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
     * Remove only the Leaves module permissions.
     *
     * @return void
     */
    public function down()
    {
        if (!Schema::hasTable('module_access')) {
            return;
        }

        DB::table('module_access')
            ->where('module_name', 'leaves')
            ->delete();
    }
}