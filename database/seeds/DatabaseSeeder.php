<?php

use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeRole;
use App\Models\EmployeeType;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        DB::transaction(function () {
            $this->resetSeededHrmsRecords();
            $roleIds = $this->synchronizeUserRoles();
            $this->seedModuleAccess($roleIds);

            $fullTime = EmployeeType::updateOrCreate(
                ['name' => 'Full-time'],
                ['description' => 'Standard full-time employment.', 'is_active' => true]
            );
            $contract = EmployeeType::updateOrCreate(
                ['name' => 'Contract'],
                ['description' => 'Fixed-term contract employment.', 'is_active' => true]
            );
            $hrEmployeeRole = EmployeeRole::updateOrCreate(
                ['name' => 'hr'],
                ['description' => 'Human Resources team member and department head.', 'is_active' => true]
            );
            $employeeRole = EmployeeRole::updateOrCreate(
                ['name' => 'emp'],
                ['description' => 'Employee account.', 'is_active' => true]
            );

            $departments = [
                ['code' => 'ENG', 'name' => 'Engineering', 'location' => 'Pune', 'email' => 'engineering@gmail.com', 'contact_no' => '9822000001'],
                ['code' => 'PEO', 'name' => 'People & Culture', 'location' => 'Pune', 'email' => 'people@gmail.com', 'contact_no' => '9822000002'],
                ['code' => 'FIN', 'name' => 'Finance', 'location' => 'Pune', 'email' => 'finance@gmail.com', 'contact_no' => '9822000003'],
            ];
            foreach ($departments as &$departmentData) {
                $departmentData['head'] = 'Department Head';
                $departmentData['head_employee_id'] = null;
                $departmentData['is_active'] = true;
                $department = Department::updateOrCreate(
                    ['code' => $departmentData['code']],
                    $departmentData
                );
                $departmentData['id'] = $department->id;
            }
            unset($departmentData);

            $superAdmin = $this->saveUser(
                'superadmin@gmail.com',
                'Super Admin',
                $roleIds['superadmin'],
                null
            );

            $hrPeople = [
                ['first_name' => 'Aarav', 'last_name' => 'Sharma'],
                ['first_name' => 'Diya', 'last_name' => 'Patel'],
                ['first_name' => 'Meera', 'last_name' => 'Shah'],
            ];
            $firstNames = ['Ishaan', 'Ananya', 'Rohan', 'Mira', 'Kavya', 'Aditya', 'Neha', 'Vikram', 'Sana', 'Kabir'];
            $lastNames = ['Kulkarni', 'Deshmukh', 'Joshi', 'Nair', 'Rao', 'Bose', 'Sethi', 'Khan', 'Chopra', 'Menon'];

            for ($number = 1; $number <= 100; $number++) {
                $departmentIndex = ($number - 1) % count($departments);
                $department = $departments[$departmentIndex];
                $isHead = $number <= 3;
                if ($isHead) {
                    $person = $hrPeople[$number - 1];
                    $role = $hrEmployeeRole;
                    $userRoleId = $roleIds['hr'];
                } else {
                    $nameIndex = $number - 4;
                    $person = [
                        'first_name' => $firstNames[$nameIndex % count($firstNames)],
                        'last_name' => $lastNames[(int) floor($nameIndex / count($firstNames))],
                    ];
                    $role = $employeeRole;
                    $userRoleId = $roleIds['emp'];
                }

                $fullName = $person['first_name'] . ' ' . $person['last_name'];
                $email = strtolower($person['first_name'] . '.' . $person['last_name']) . '@gmail.com';
                $employee = Employee::create([
                    'employee_code' => sprintf('DEMO-%04d', $number),
                    'first_name' => $person['first_name'],
                    'last_name' => $person['last_name'],
                    'email' => $email,
                    'phone' => '900' . str_pad((string) $number, 7, '0', STR_PAD_LEFT),
                    'gender' => null,
                    'department_id' => $department['id'],
                    'employee_type_id' => $number % 5 === 0 ? $contract->id : $fullTime->id,
                    'employee_role_id' => $role->id,
                    'manager_id' => $isHead ? null : $departments[$departmentIndex]['head_employee_id'],
                    'created_by' => $superAdmin->id,
                    'employment_status' => 'active',
                    'is_active' => true,
                    'joining_date' => now()->subDays(($number * 11) % 900)->toDateString(),
                    'city' => 'Pune',
                    'state' => 'Maharashtra',
                ]);

                $this->saveUser($email, $fullName, $userRoleId, $employee->id);
                if ($isHead) {
                    DB::table('departments')
                        ->where('id', $department['id'])
                        ->update(['head' => $fullName, 'head_employee_id' => $employee->id, 'updated_at' => now()]);
                    $departments[$departmentIndex]['head_employee_id'] = $employee->id;
                }
            }
        });
    }

    private function resetSeededHrmsRecords()
    {
        $creatorIds = DB::table('users')
            ->whereIn('email', ['seed.hr@gmail.com', 'hr@gmail.com'])
            ->pluck('id');
        $employeeQuery = DB::table('employees');
        if (Schema::hasColumn('employees', 'created_by') && $creatorIds->isNotEmpty()) {
            $employeeQuery->whereIn('created_by', $creatorIds);
        } else {
            $employeeQuery->whereRaw('1 = 0');
        }
        $employeeQuery->orWhereIn('employee_code', ['PUN-0001'])
            ->orWhere('employee_code', 'like', 'DEMO-%');
        $employeeIds = $employeeQuery->pluck('id');

        $userIds = DB::table('users')
            ->whereIn('employee_id', $employeeIds)
            ->orWhereIn('email', [
                'seed.hr@gmail.com',
                'hr@gmail.com',
                'admin@gmail.com',
                'emp@gmail.com',
                'super@gmail.com',
                'superadmin@gmail.com',
            ])
            ->pluck('id');

        foreach (['employee_documents', 'employee_educations', 'employee_experiences', 'employee_bank_details', 'leave_applications', 'exit_passes'] as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->whereIn('employee_id', $employeeIds)->delete();
                if (in_array('approved_by', Schema::getColumnListing($table), true)) {
                    DB::table($table)->whereIn('approved_by', $userIds)->delete();
                }
            }
        }
        if ($employeeIds->isNotEmpty()) {
            DB::table('users')->whereIn('employee_id', $employeeIds)->delete();
            DB::table('employees')->whereIn('id', $employeeIds)->delete();
        }
        DB::table('users')->whereIn('id', $userIds)->delete();
    }

    private function synchronizeUserRoles()
    {
        $canonical = [];
        foreach (['superadmin', 'hr', 'emp'] as $name) {
            $role = UserRole::firstOrCreate(['name' => $name], ['is_active' => true]);
            if (!$role->is_active) {
                $role->update(['is_active' => true]);
            }
            $canonical[$name] = $role->id;
        }

        $legacyMap = [
            'admin' => 'superadmin',
            'super_admin' => 'superadmin',
            'employee' => 'emp',
        ];
        foreach ($legacyMap as $legacyName => $targetName) {
            $legacy = UserRole::where('name', $legacyName)->first();
            if (!$legacy || $legacy->id === $canonical[$targetName]) {
                continue;
            }
            if (DB::table('users')->where('role_id', $legacy->id)->exists()) {
                throw new \RuntimeException(
                    "Cannot seed demo roles: the legacy '{$legacyName}' role is still assigned to existing users. "
                    . 'Move those accounts to superadmin, hr, or emp before running this seeder.'
                );
            }
            DB::table('module_access')->where('role_id', $legacy->id)->delete();
            $legacy->delete();
        }

        $extraRoles = UserRole::whereNotIn('name', ['superadmin', 'hr', 'emp'])->pluck('name');
        if ($extraRoles->isNotEmpty()) {
            throw new \RuntimeException(
                'Cannot seed demo roles while additional user roles exist: '
                . $extraRoles->implode(', ')
                . '. Remove or migrate these roles explicitly before running this seeder.'
            );
        }

        return $canonical;
    }

    private function seedModuleAccess(array $roleIds)
    {
        $modules = [
            'dashboard',
            'employee_profile',
            'employees',
            'departments',
            'employee_types',
            'employee_roles',
            'holidays',
            'leaves',
            'exit_pass',
        ];
        $grants = [
            'hr' => [
                'dashboard' => ['view'],
                'employee_profile' => ['view'],
                'employees' => ['view'],
                'departments' => ['view'],
                'holidays' => ['view'],
                'leaves' => ['view', 'create'],
                'exit_pass' => ['view', 'create'],
            ],
            'emp' => [
                'dashboard' => ['view'],
                'employee_profile' => ['view'],
                'holidays' => ['view'],
                'leaves' => ['view', 'create'],
                'exit_pass' => ['view', 'create'],
            ],
        ];

        foreach (['superadmin', 'hr', 'emp'] as $roleName) {
            foreach ($modules as $module) {
                $actions = $grants[$roleName][$module] ?? [];
                DB::table('module_access')->updateOrInsert(
                    ['role_id' => $roleIds[$roleName], 'module_name' => $module],
                    [
                        'can_view' => in_array('view', $actions, true),
                        'can_create' => in_array('create', $actions, true),
                        'can_edit' => in_array('edit', $actions, true),
                        'can_delete' => in_array('delete', $actions, true),
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            }
        }
    }

    private function saveUser($email, $name, $roleId, $employeeId)
    {
        $user = User::firstOrNew(['email' => $email]);
        $user->name = $name;
        $user->password = Hash::make('password');
        $user->role_id = $roleId;
        $user->employee_id = $employeeId;
        $user->is_active = true;
        $user->save();

        return $user;
    }
}
