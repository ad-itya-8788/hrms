<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateHolidaysTable extends Migration
{
    public function up()
    {
        Schema::create('holidays', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 120);
            $table->date('holiday_date')->index();
            $table->string('holiday_type', 20)->default('company');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        $now = now();
        $rolePermissions = [
            'hr' => ['can_view' => true, 'can_create' => true, 'can_edit' => true, 'can_delete' => true],
            'emp' => ['can_view' => true, 'can_create' => false, 'can_edit' => false, 'can_delete' => false],
        ];

        foreach (DB::table('user_roles')->get(['id', 'name']) as $role) {
            $permissions = $rolePermissions[$role->name] ?? [
                'can_view' => false,
                'can_create' => false,
                'can_edit' => false,
                'can_delete' => false,
            ];

            DB::table('module_access')->updateOrInsert(
                ['role_id' => $role->id, 'module_name' => 'holidays'],
                array_merge($permissions, ['created_at' => $now, 'updated_at' => $now])
            );
        }
    }

    public function down()
    {
        if (Schema::hasTable('module_access')) {
            DB::table('module_access')->where('module_name', 'holidays')->delete();
        }

        Schema::dropIfExists('holidays');
    }
}
