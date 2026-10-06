<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateModuleAccessTable extends Migration
{
    public function up()
    {
        Schema::create('module_access', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('role_id');
            $table->string('module_name', 40);
            $table->boolean('can_view')->default(false);
            $table->boolean('can_create')->default(false);
            $table->boolean('can_edit')->default(false);
            $table->boolean('can_delete')->default(false);
            $table->timestamps();
            $table->foreign('role_id')->references('id')->on('user_roles')->onDelete('cascade');
            $table->unique(['role_id', 'module_name']);
        });

        $now = date('Y-m-d H:i:s');
        $roleIds = DB::table('user_roles')->pluck('id', 'name');
        $modules = [
            'dashboard',
            'employee_profile',
            'holidays',
            'employees',
            'departments',
            'employee_types',
            'employee_roles',
        ];
        $rolePermissions = [
            'hr' => [
                'dashboard' => ['view'],
                'holidays' => ['view'],
                'employees' => ['view', 'create', 'edit', 'delete'],
                'departments' => ['view', 'create', 'edit', 'delete'],
                'employee_types' => ['view', 'create', 'edit', 'delete'],
                'employee_roles' => ['view', 'create', 'edit', 'delete'],
            ],
            'employee' => [
                'dashboard' => ['view'],
                'employee_profile' => ['view'],
            ],
        ];

        foreach ($rolePermissions as $role => $grants) {
            if (!isset($roleIds[$role])) {
                continue;
            }
            foreach ($modules as $module) {
                $actions = $grants[$module] ?? [];
                DB::table('module_access')->insert([
                    'role_id' => $roleIds[$role],
                    'module_name' => $module,
                    'can_view' => in_array('view', $actions, true),
                    'can_create' => in_array('create', $actions, true),
                    'can_edit' => in_array('edit', $actions, true),
                    'can_delete' => in_array('delete', $actions, true),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                }
            }
    }

    public function down()
    {
        Schema::dropIfExists('module_access');
    }
}
