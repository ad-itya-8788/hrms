<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MoveEmployeeUserLinkToUsersTable extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('users', 'employee_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedBigInteger('employee_id')->nullable();
            });
        }

        if (Schema::hasColumn('employees', 'user_id')) {
            $conflictingLinks = DB::table('employees')
                ->join('users', 'users.id', '=', 'employees.user_id')
                ->whereNotNull('employees.user_id')
                ->whereNotNull('users.employee_id')
                ->whereColumn('users.employee_id', '<>', 'employees.id')
                ->exists();
            if ($conflictingLinks) {
                throw new \RuntimeException('Existing user-to-employee links conflict; no links were changed.');
            }

            $links = DB::table('employees')->whereNotNull('user_id')->get(['id', 'user_id']);
            foreach ($links as $link) {
                DB::table('users')->where('id', $link->user_id)->whereNull('employee_id')->update([
                    'employee_id' => $link->id,
                ]);
            }
        }

        $this->addUniqueIndex('users', 'employee_id');
        $this->addForeignKey('users', 'employee_id', 'employees', 'id');

        if (Schema::hasColumn('employees', 'user_id')) {
            $this->dropForeignKeys('employees', 'user_id');
            $this->dropUniqueIndexes('employees', 'user_id');
            Schema::table('employees', function (Blueprint $table) {
                $table->dropColumn('user_id');
            });
        }
    }

    public function down()
    {
        if (!Schema::hasColumn('employees', 'user_id')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->nullable();
            });
        }

        DB::table('users')->whereNotNull('employee_id')->orderBy('id')->get(['id', 'employee_id'])
            ->each(function ($link) {
                DB::table('employees')->where('id', $link->employee_id)->update([
                    'user_id' => $link->id,
                ]);
            });

        $this->addUniqueIndex('employees', 'user_id');
        $this->addForeignKey('employees', 'user_id', 'users', 'id');

        $this->dropForeignKeys('users', 'employee_id');
        $this->dropUniqueIndexes('users', 'employee_id');
        if (Schema::hasColumn('users', 'employee_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('employee_id');
            });
        }
    }

    private function addUniqueIndex($tableName, $columnName)
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            $indexes = DB::select("PRAGMA index_list('" . str_replace("'", "''", $tableName) . "')");
            foreach ($indexes as $index) {
                if (empty($index->unique)) {
                    continue;
                }
                $columns = DB::select("PRAGMA index_info('" . str_replace("'", "''", $index->name) . "')");
                if (count($columns) === 1 && $columns[0]->name === $columnName) {
                    return;
                }
            }
            Schema::table($tableName, function (Blueprint $table) use ($columnName) {
                $table->unique($columnName);
            });
            return;
        }

        $indexes = DB::select(
            "SELECT INDEX_NAME FROM information_schema.STATISTICS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND NON_UNIQUE = 0
             GROUP BY INDEX_NAME
             HAVING GROUP_CONCAT(COLUMN_NAME ORDER BY SEQ_IN_INDEX SEPARATOR ',') = ?",
            [$tableName, $columnName]
        );
        if (!$indexes) {
            Schema::table($tableName, function (Blueprint $table) use ($columnName) {
                $table->unique($columnName);
            });
        }
    }

    private function addForeignKey($tableName, $columnName, $referencedTable, $referencedColumn)
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            $constraints = DB::select("PRAGMA foreign_key_list('" . str_replace("'", "''", $tableName) . "')");
            foreach ($constraints as $constraint) {
                if ($constraint->from === $columnName && $constraint->table === $referencedTable && $constraint->to === $referencedColumn) {
                    return;
                }
            }
            Schema::table($tableName, function (Blueprint $table) use ($columnName, $referencedTable, $referencedColumn) {
                $table->foreign($columnName)->references($referencedColumn)->on($referencedTable)->onDelete('set null');
            });
            return;
        }

        $constraints = DB::select(
            'SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?
             AND REFERENCED_TABLE_NAME = ? AND REFERENCED_COLUMN_NAME = ?',
            [$tableName, $columnName, $referencedTable, $referencedColumn]
        );
        if (!$constraints) {
            Schema::table($tableName, function (Blueprint $table) use ($columnName, $referencedTable, $referencedColumn) {
                $table->foreign($columnName)->references($referencedColumn)->on($referencedTable)->onDelete('set null');
            });
        }
    }

    private function dropForeignKeys($tableName, $columnName)
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            $constraints = DB::select("PRAGMA foreign_key_list('" . str_replace("'", "''", $tableName) . "')");
            foreach ($constraints as $constraint) {
                if ($constraint->from === $columnName) {
                    throw new \RuntimeException('SQLite cannot drop this existing foreign key without rebuilding the table.');
                }
            }
            return;
        }

        $constraints = DB::select(
            'SELECT DISTINCT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?
             AND REFERENCED_TABLE_NAME IS NOT NULL',
            [$tableName, $columnName]
        );
        foreach ($constraints as $constraint) {
            $name = str_replace('`', '``', $constraint->CONSTRAINT_NAME);
            DB::statement('ALTER TABLE `' . $tableName . '` DROP FOREIGN KEY `' . $name . '`');
        }
    }

    private function dropUniqueIndexes($tableName, $columnName)
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            $indexes = DB::select("PRAGMA index_list('" . str_replace("'", "''", $tableName) . "')");
            foreach ($indexes as $index) {
                if (empty($index->unique)) {
                    continue;
                }
                $columns = DB::select("PRAGMA index_info('" . str_replace("'", "''", $index->name) . "')");
                if (count($columns) === 1 && $columns[0]->name === $columnName && $index->name !== 'PRIMARY') {
                    $name = str_replace('"', '""', $index->name);
                    DB::statement('DROP INDEX "' . $name . '"');
                }
            }
            return;
        }

        $indexes = DB::select(
            "SELECT INDEX_NAME FROM information_schema.STATISTICS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND NON_UNIQUE = 0
             GROUP BY INDEX_NAME
             HAVING GROUP_CONCAT(COLUMN_NAME ORDER BY SEQ_IN_INDEX SEPARATOR ',') = ?",
            [$tableName, $columnName]
        );
        foreach ($indexes as $index) {
            if ($index->INDEX_NAME !== 'PRIMARY') {
                $name = str_replace('`', '``', $index->INDEX_NAME);
                DB::statement('ALTER TABLE `' . $tableName . '` DROP INDEX `' . $name . '`');
            }
        }
    }
}
