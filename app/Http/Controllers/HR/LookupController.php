<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\EmployeeRole;
use App\Models\EmployeeType;

class LookupController extends Controller
{
    public function departments()
    {
        return response()->json([
            'data' => Department::query()
                ->orderBy('name')
                ->where('is_active', true)
                ->get(['id', 'code', 'name', 'location']),
        ]);
    }

    public function employeeTypes()
    {
        return response()->json([
            'data' => EmployeeType::where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function employeeRoles()
    {
        return response()->json([
            'data' => EmployeeRole::where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }
}
