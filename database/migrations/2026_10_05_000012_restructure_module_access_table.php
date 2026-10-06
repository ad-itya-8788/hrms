<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RestructureModuleAccessTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('module_access')
            || (Schema::hasColumn('module_access', 'role_id') && !Schema::hasColumn('module_access', 'module_key'))) {
            return;
        }

        Schema::table('module_access', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id')->nullable();
            $table->string('module_name', 40)->nullable();
            $table->boolean('can_view')->default(false);
            $table->boolean('can_create')->default(false);
            $table->boolean('can_edit')->default(false);
            $table->boolean('can_delete')->default(false);
        });

        $groups = [];
        foreach (DB::table('module_access')->orderBy('id')->get() as $record) {
            $parts = explode('.', $record->module_key, 2);
            if (count($parts) !== 2) {
                throw new \RuntimeException('Cannot migrate module access row with an invalid module key.');
            }
            list($moduleName, $action) = $parts;
            if (!in_array($action, ['view', 'create', 'edit', 'delete'], true)) {
                throw new \RuntimeException('Cannot migrate module access row with an unsupported permission action.');
            }

            $key = $record->user_role_id . ':' . $moduleName;
            if (!isset($groups[$key])) {
                $groups[$key] = [
                    'id' => $record->id,
                    'role_id' => $record->user_role_id,
                    'module_name' => $moduleName,
                    'can_view' => false,
                    'can_create' => false,
                    'can_edit' => false,
                    'can_delete' => false,
                ];
            }
            $groups[$key]['can_' . $action] = (bool) $record->is_enabled;
        }

        foreach ($groups as $group) {
            DB::table('module_access')->where('id', $group['id'])->update([
                'role_id' => $group['role_id'],
                'module_name' => $group['module_name'],
                'can_view' => $group['can_view'],
                'can_create' => $group['can_create'],
                'can_edit' => $group['can_edit'],
                'can_delete' => $group['can_delete'],
            ]);
        }

        foreach ($groups as $group) {
            DB::table('module_access')
                ->where('user_role_id', $group['role_id'])
                ->where('id', '<>', $group['id'])
                ->where('module_key', 'like', $group['module_name'] . '.%')
                ->delete();
        }

        Schema::table('module_access', function (Blueprint $table) {
            $table->dropForeign(['user_role_id']);
            $table->dropUnique(['user_role_id', 'module_key']);
            $table->dropColumn(['user_role_id', 'module_key', 'is_enabled']);
        });

        DB::statement('ALTER TABLE module_access MODIFY role_id BIGINT UNSIGNED NOT NULL, MODIFY module_name VARCHAR(40) NOT NULL');
        Schema::table('module_access', function (Blueprint $table) {
            $table->foreign('role_id')->references('id')->on('user_roles')->onDelete('cascade');
            $table->unique(['role_id', 'module_name']);
        });
    }

    public function down()
    {
        if (!Schema::hasTable('module_access') || !Schema::hasColumn('module_access', 'role_id')) {
            return;
        }

        Schema::table('module_access', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropUnique(['role_id', 'module_name']);
            $table->unsignedBigInteger('user_role_id')->nullable();
            $table->string('module_key', 80)->nullable();
            $table->boolean('is_enabled')->default(false);
        });

        $rows = DB::table('module_access')->get();
        foreach ($rows as $row) {
            DB::table('module_access')->where('id', $row->id)->update([
                'user_role_id' => $row->role_id,
                'module_key' => $row->module_name . '.view',
                'is_enabled' => $row->can_view,
            ]);
            foreach (['create', 'edit', 'delete'] as $action) {
                DB::table('module_access')->insert([
                    'user_role_id' => $row->role_id,
                    'module_key' => $row->module_name . '.' . $action,
                    'is_enabled' => (bool) $row->{'can_' . $action},
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                ]);
            }
        }

        Schema::table('module_access', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropUnique(['role_id', 'module_name']);
            $table->dropColumn(['role_id', 'module_name', 'can_view', 'can_create', 'can_edit', 'can_delete']);
        });

        DB::statement('ALTER TABLE module_access MODIFY user_role_id BIGINT UNSIGNED NOT NULL, MODIFY module_key VARCHAR(80) NOT NULL');
        Schema::table('module_access', function (Blueprint $table) {
            $table->foreign('user_role_id')->references('id')->on('user_roles')->onDelete('cascade');
            $table->unique(['user_role_id', 'module_key']);
        });
    }
}
