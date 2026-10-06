<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class SeedSampleHrmsRecords extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('employees', 'created_by')) {
            if (DB::connection()->getDriverName() === 'sqlite') {
                DB::statement('ALTER TABLE employees ADD COLUMN created_by INTEGER NULL REFERENCES users(id) ON DELETE SET NULL');
            } else {
                Schema::table('employees', function (Blueprint $table) {
                    $table->unsignedBigInteger('created_by')->nullable();
                    $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
                });
            }
        }

        $now = date('Y-m-d H:i:s');
        $data = $this->sampleData();

        DB::transaction(function () use ($data, $now) {
            foreach ($data['departments'] as $department) {
                DB::table('departments')->updateOrInsert(
                    ['code' => $department['code']],
                    [
                        'name' => $department['name'],
                        'location' => 'Pune',
                        'email' => $department['email'],
                        'contact_no' => $department['contact_no'],
                        'head' => $department['head'],
                        'is_active' => true,
                        'updated_at' => $now,
                        'created_at' => $now,
                    ]
                );
            }

            foreach ($data['employee_types'] as $name => $description) {
                DB::table('employee_types')->updateOrInsert(
                    ['name' => $name],
                    [
                        'description' => $description,
                        'is_active' => true,
                        'updated_at' => $now,
                        'created_at' => $now,
                    ]
                );
            }

            foreach ($data['employee_roles'] as $name => $description) {
                DB::table('employee_roles')->updateOrInsert(
                    ['name' => $name],
                    [
                        'description' => $description,
                        'is_active' => true,
                        'updated_at' => $now,
                        'created_at' => $now,
                    ]
                );
            }

            $employeeRoleId = DB::table('user_roles')->where('name', 'employee')->value('id');
            if (!$employeeRoleId) {
                throw new \RuntimeException('The employee user role is required before sample HRMS records can be seeded.');
            }
            $hrRoleId = DB::table('user_roles')->where('name', 'hr')->value('id');
            if (!$hrRoleId) {
                throw new \RuntimeException('The HR user role is required before sample HRMS records can be seeded.');
            }

            $password = Hash::make('password');
            DB::table('users')->updateOrInsert(
                ['email' => 'seed.hr@gmail.com'],
                [
                    'name' => 'Sample HR Onboarding User',
                    'password' => $password,
                    'role_id' => $hrRoleId,
                    'employee_id' => null,
                    'is_active' => true,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
            $creatorUserId = DB::table('users')->where('email', 'seed.hr@gmail.com')->value('id');
            $superAdminRoleId = DB::table('user_roles')->where('name', 'super_admin')->value('id');
            if (!$superAdminRoleId) {
                throw new \RuntimeException('The super_admin user role is required before sample HRMS records can be seeded.');
            }
            DB::table('users')->updateOrInsert(
                ['email' => 'super@gmail.com'],
                [
                    'name' => 'System Super Administrator',
                    'password' => $password,
                    'role_id' => $superAdminRoleId,
                    'employee_id' => null,
                    'is_active' => true,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
            $employeeNumber = 0;

            foreach ($data['departments'] as $department) {
                $departmentId = DB::table('departments')
                    ->where('code', $department['code'])
                    ->value('id');
                $managerId = null;
                $departmentEmployeeNumber = 0;

                foreach ($department['employees'] as $employee) {
                    $employeeNumber++;
                    $departmentEmployeeNumber++;
                    $firstName = $employee[0];
                    $lastName = $employee[1];
                    $fullName = $firstName . ' ' . $lastName;
                    $employeeCode = $department['code'] . '-' . str_pad($departmentEmployeeNumber, 4, '0', STR_PAD_LEFT);
                    $email = strtolower($firstName . '.' . $lastName) . '@gmail.com';

                    $employeeTypeId = DB::table('employee_types')
                        ->where('name', $employee[5])
                        ->value('id');
                    $roleId = DB::table('employee_roles')
                        ->where('name', $employee[2])
                        ->value('id');

                    $employeeAttributes = [
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'email' => $email,
                        'phone' => '98220' . str_pad($employeeNumber, 5, '0', STR_PAD_LEFT),
                        'gender' => $employee[4],
                        'date_of_birth' => $employee[3],
                        'department_id' => $departmentId,
                        'employee_type_id' => $employeeTypeId,
                        'employee_role_id' => $roleId,
                        'manager_id' => $managerId,
                        'employment_status' => 'active',
                        'is_active' => true,
                        'joining_date' => $employee[6],
                        'address_line' => (100 + $employeeNumber) . ' Sample Road',
                        'city' => 'Pune',
                        'state' => 'Maharashtra',
                        'postal_code' => '4110' . str_pad((string) $employeeNumber, 2, '0', STR_PAD_LEFT),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                    $employeeAttributes['created_by'] = $creatorUserId;

                    DB::table('employees')->updateOrInsert(
                        ['employee_code' => $employeeCode],
                        $employeeAttributes
                    );

                    $employeeId = DB::table('employees')
                        ->where('employee_code', $employeeCode)
                        ->value('id');
                    if ($managerId === null) {
                        $managerId = $employeeId;
                    }

                    DB::table('users')->updateOrInsert(
                        ['email' => $email],
                        [
                            'name' => $fullName,
                            'password' => $password,
                            'role_id' => $employeeRoleId,
                            'employee_id' => $employeeId,
                            'is_active' => true,
                            'updated_at' => $now,
                            'created_at' => $now,
                        ]
                    );
                }
            }
        });
    }

    public function down()
    {
        $data = $this->sampleData();
        $employeeCodes = [];
        $employeeEmails = [];
        foreach ($data['departments'] as $department) {
            foreach ($department['employees'] as $index => $employee) {
                $employeeCodes[] = $department['code'] . '-' . str_pad($index + 1, 4, '0', STR_PAD_LEFT);
                $employeeEmails[] = strtolower($employee[0] . '.' . $employee[1]) . '@gmail.com';
            }
        }

        DB::transaction(function () use ($data, $employeeCodes, $employeeEmails) {
            $sampleEmployeeIds = DB::table('employees')
                ->whereIn('employee_code', $employeeCodes)
                ->pluck('id');
            DB::table('users')
                ->whereIn('email', $employeeEmails)
                ->orWhereIn('employee_id', $sampleEmployeeIds)
                ->delete();
            DB::table('users')->where('email', 'super@gmail.com')->delete();
            DB::table('employees')->whereIn('employee_code', $employeeCodes)->delete();
            $creatorQuery = DB::table('users')->whereIn('email', ['seed.hr@gmail.com', 'seed.hr@example.test']);
            if (Schema::hasColumn('employees', 'created_by')) {
                $creatorQuery->whereNotIn('id', DB::table('employees')->whereNotNull('created_by')->select('created_by'));
            }
            $creatorQuery->delete();

            if (Schema::hasColumn('employees', 'created_by')) {
                Schema::table('employees', function (Blueprint $table) {
                    $table->dropForeign(['created_by']);
                    $table->dropColumn('created_by');
                });
            }

            DB::table('employee_roles')
                ->whereIn('name', array_keys($data['employee_roles']))
                ->whereNotIn('id', DB::table('employees')->select('employee_role_id'))
                ->delete();
            DB::table('employee_types')
                ->whereIn('name', array_keys($data['employee_types']))
                ->whereNotIn('id', DB::table('employees')->select('employee_type_id'))
                ->delete();
            DB::table('departments')
                ->whereIn('code', array_column($data['departments'], 'code'))
                ->whereNotIn('id', DB::table('employees')->select('department_id'))
                ->delete();
        });
    }

    private function sampleData()
    {
        return [
            'employee_types' => [
                'Full-time' => 'Regular full-time employment.',
                'Contract' => 'Fixed-term contract employment.',
            ],
            'employee_roles' => [
                'Engineering Manager' => 'Leads engineering delivery and team development.',
                'Software Engineering Lead' => 'Coordinates technical design and engineering standards.',
                'Senior Backend Engineer' => 'Builds and maintains server-side applications.',
                'Frontend Engineer' => 'Develops accessible, responsive user interfaces.',
                'Full Stack Engineer' => 'Delivers features across application and service layers.',
                'QA Automation Engineer' => 'Builds automated quality and regression checks.',
                'DevOps Engineer' => 'Maintains deployment pipelines and cloud infrastructure.',
                'Data Engineer' => 'Develops reliable data pipelines and reporting datasets.',
                'UX Designer' => 'Researches and designs employee-focused digital experiences.',
                'Technical Support Engineer' => 'Resolves technical issues and supports internal users.',
                'People Operations Manager' => 'Leads HR operations, policy, and employee services.',
                'HR Business Partner' => 'Advises managers on workforce and employee matters.',
                'Talent Acquisition Specialist' => 'Sources and coordinates qualified candidates.',
                'Learning and Development Specialist' => 'Plans employee training and career development.',
                'Compensation Analyst' => 'Supports fair compensation planning and analysis.',
                'HR Operations Specialist' => 'Maintains accurate employee records and HR processes.',
                'Employee Relations Specialist' => 'Supports consistent and respectful workplace practices.',
                'Recruiter' => 'Coordinates hiring pipelines and candidate communication.',
                'Benefits Coordinator' => 'Administers employee benefits and enrollment support.',
                'People Data Analyst' => 'Prepares workforce reports and people analytics.',
                'Finance Manager' => 'Leads financial planning, controls, and reporting.',
                'Accounting Lead' => 'Coordinates accurate accounting and month-end close.',
                'Senior Accountant' => 'Prepares reconciliations and financial statements.',
                'Accounts Payable Specialist' => 'Processes supplier invoices and payment records.',
                'Accounts Receivable Specialist' => 'Tracks customer billing and incoming payments.',
                'Financial Analyst' => 'Builds budgets, forecasts, and financial analysis.',
                'Payroll Specialist' => 'Processes payroll data and related reconciliations.',
                'Tax Associate' => 'Supports tax filings and statutory documentation.',
                'Procurement Analyst' => 'Reviews purchasing data and supplier performance.',
                'Audit Associate' => 'Supports internal controls and audit evidence reviews.',
            ],
            'departments' => [
                [
                    'code' => 'ENG',
                    'name' => 'Engineering',
                    'email' => 'engineering.hrms@gmail.com',
                    'contact_no' => '02041001001',
                    'head' => 'Aarav Kulkarni',
                    'employees' => [
                        ['Aarav', 'Kulkarni', 'Engineering Manager', '1986-04-12', 'Male', 'Full-time', '2017-06-12'],
                        ['Ananya', 'Deshmukh', 'Software Engineering Lead', '1989-08-23', 'Female', 'Full-time', '2018-02-05'],
                        ['Rohan', 'Patil', 'Senior Backend Engineer', '1990-03-14', 'Male', 'Full-time', '2019-01-14'],
                        ['Mira', 'Shah', 'Frontend Engineer', '1994-11-02', 'Female', 'Full-time', '2020-07-20'],
                        ['Ishaan', 'Joshi', 'Full Stack Engineer', '1992-06-17', 'Male', 'Full-time', '2021-03-08'],
                        ['Kavya', 'Nair', 'QA Automation Engineer', '1993-02-26', 'Female', 'Full-time', '2021-09-13'],
                        ['Aditya', 'Rao', 'DevOps Engineer', '1991-12-09', 'Male', 'Full-time', '2022-04-18'],
                        ['Neha', 'Bose', 'Data Engineer', '1995-05-30', 'Female', 'Full-time', '2022-11-07'],
                        ['Vikram', 'Sethi', 'UX Designer', '1994-09-11', 'Male', 'Contract', '2023-05-15'],
                        ['Sana', 'Khan', 'Technical Support Engineer', '1997-01-19', 'Female', 'Contract', '2024-02-12'],
                    ],
                ],
                [
                    'code' => 'PEO',
                    'name' => 'People & Culture',
                    'email' => 'people.hrms@gmail.com',
                    'contact_no' => '02041001002',
                    'head' => 'Diya Mehta',
                    'employees' => [
                        ['Diya', 'Mehta', 'People Operations Manager', '1985-07-04', 'Female', 'Full-time', '2016-10-03'],
                        ['Kabir', 'Chopra', 'HR Business Partner', '1988-01-27', 'Male', 'Full-time', '2018-04-16'],
                        ['Priya', 'Menon', 'Talent Acquisition Specialist', '1992-05-08', 'Female', 'Full-time', '2019-08-19'],
                        ['Arjun', 'Desai', 'Learning and Development Specialist', '1991-10-15', 'Male', 'Full-time', '2020-02-10'],
                        ['Ira', 'Kapoor', 'Compensation Analyst', '1993-03-22', 'Female', 'Full-time', '2020-11-02'],
                        ['Nikhil', 'Jain', 'HR Operations Specialist', '1990-12-01', 'Male', 'Full-time', '2021-06-21'],
                        ['Tara', 'Iyer', 'Employee Relations Specialist', '1994-08-16', 'Female', 'Full-time', '2022-01-17'],
                        ['Sameer', 'Malhotra', 'Recruiter', '1995-04-09', 'Male', 'Full-time', '2022-08-08'],
                        ['Riya', 'Fernandes', 'Benefits Coordinator', '1996-06-28', 'Female', 'Contract', '2023-03-06'],
                        ['Dev', 'Pillai', 'People Data Analyst', '1997-09-13', 'Male', 'Contract', '2024-01-22'],
                    ],
                ],
                [
                    'code' => 'FIN',
                    'name' => 'Finance',
                    'email' => 'finance.hrms@gmail.com',
                    'contact_no' => '02041001003',
                    'head' => 'Meera Sane',
                    'employees' => [
                        ['Meera', 'Sane', 'Finance Manager', '1984-02-18', 'Female', 'Full-time', '2015-04-06'],
                        ['Rahul', 'Bhat', 'Accounting Lead', '1987-11-24', 'Male', 'Full-time', '2017-09-11'],
                        ['Aditi', 'Gokhale', 'Senior Accountant', '1989-06-03', 'Female', 'Full-time', '2018-12-03'],
                        ['Karan', 'Arora', 'Accounts Payable Specialist', '1992-09-20', 'Male', 'Full-time', '2019-05-27'],
                        ['Pooja', 'Reddy', 'Accounts Receivable Specialist', '1993-12-14', 'Female', 'Full-time', '2020-08-17'],
                        ['Manav', 'Singh', 'Financial Analyst', '1991-04-29', 'Male', 'Full-time', '2021-02-01'],
                        ['Simran', 'Gill', 'Payroll Specialist', '1994-07-07', 'Female', 'Full-time', '2021-10-25'],
                        ['Om', 'Wagh', 'Tax Associate', '1995-10-31', 'Male', 'Full-time', '2022-06-13'],
                        ['Leena', 'Dutta', 'Procurement Analyst', '1996-01-05', 'Female', 'Contract', '2023-02-20'],
                        ['Yash', 'Mistry', 'Audit Associate', '1997-08-26', 'Male', 'Contract', '2024-03-11'],
                    ],
                ],
            ],
        ];
    }
}
