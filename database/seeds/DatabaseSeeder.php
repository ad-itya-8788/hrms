<?php

use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeRole;
use App\Models\EmployeeType;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $engineering = Department::firstOrCreate(
            ['code' => 'ENG'],
            [
                'name' => 'Engineering',
                'location' => 'Pune',
                'email' => 'engineering@hrms.local',
                'contact_no' => '0000000000',
                'head' => 'Engineering Head',
                'is_active' => true,
            ]
        );
        $people = Department::firstOrCreate(
            ['code' => 'PEO'],
            [
                'name' => 'People & Culture',
                'location' => 'Pune',
                'email' => 'people@hrms.local',
                'contact_no' => '0000000000',
                'head' => 'People Head',
                'is_active' => true,
            ]
        );
        $fullTime = EmployeeType::firstOrCreate(
            ['name' => 'Full-time'],
            ['description' => 'Standard full-time employment.', 'is_active' => true]
        );
        $engineer = EmployeeRole::firstOrCreate(
            ['name' => 'Software Engineer'],
            ['description' => 'Builds and maintains software.', 'is_active' => true]
        );
        EmployeeRole::firstOrCreate(
            ['name' => 'HR Business Partner'],
            ['description' => 'Supports people operations.', 'is_active' => true]
        );
        $userRoleIds = DB::table('user_roles')->pluck('id', 'name');

        $employee = Employee::updateOrCreate(['employee_code' => 'PUN-0001'], [
            'first_name' => 'Aarav',
            'last_name' => 'Kulkarni',
            'email' => 'emp@gmail.com',
            'phone' => '9822011401',
            'department_id' => $engineering->id,
            'employee_type_id' => $fullTime->id,
            'employee_role_id' => $engineer->id,
            'employment_status' => 'active',
            'is_active' => true,
            'joining_date' => '2020-05-18',
            'city' => 'Pune',
            'state' => 'Maharashtra',
        ]);

        foreach ([
            [
                'email' => 'admin@gmail.com',
                'name' => 'System Administrator',
                'role' => 'admin',
            ],
            [
                'email' => 'hr@gmail.com',
                'name' => 'HR Team',
                'role' => 'hr',
            ],
            [
                'email' => 'emp@gmail.com',
                'name' => $employee->full_name,
                'role' => 'employee',
            ],
        ] as $account) {
            $user = User::firstOrNew(['email' => $account['email']]);

            $user->name = $account['name'];
            $user->password = Hash::make('password');
            $user->role_id = $userRoleIds[$account['role']];
            $user->is_active = true;
            $user->employee_id = $account['role'] === 'employee' ? $employee->id : null;
            $user->save();

        }
    }
}
