<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Employee;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index(Request $request)
    {
        $request->validate(['status' => 'nullable|in:active,inactive']);

        $status = $request->input('status', 'active');

        $departments = Department::with('headEmployee')
            ->where('is_active', $status === 'active')
            ->orderBy('name')
            ->paginate(10);

        return view('portal.departments.index', [
            'departments' => $departments,
            'employees' => Employee::where('is_active', true)
                ->orderBy('first_name')
                ->orderBy('last_name')
                ->get(['id', 'first_name', 'last_name', 'employee_code']),
            'selectedStatus' => $status,
            'activeDepartmentCount' => Department::where('is_active', true)->count(),
            'inactiveDepartmentCount' => Department::where('is_active', false)->count(),
            'pageTitle' => 'Departments',
        ]);
    }

    public function search(Request $request)
    {
        $validated = $request->validate([
            'search' => 'nullable|string|max:100',
            'page' => 'nullable|integer|min:1',
            'status' => 'nullable|in:active,inactive',
        ]);

        $query = Department::with('headEmployee')
            ->where(
                'is_active',
                ($validated['status'] ?? 'active') === 'active'
            );

        if (!empty($validated['search'])) {

            $search = trim($validated['search']);

            $query->where(function ($departments) use ($search) {

                $departments->where('code', 'like', '%' . $search . '%')
                    ->orWhere('name', 'like', '%' . $search . '%')
                    ->orWhere('location', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhereHas('headEmployee', function ($employeeQuery) use ($search) {
                        $employeeQuery->where('first_name', 'like', '%' . $search . '%')
                            ->orWhere('last_name', 'like', '%' . $search . '%')
                            ->orWhere('employee_code', 'like', '%' . $search . '%');
                    });
            });
        }

        $departments = $query->orderBy('name')->paginate(10);

        return response()->json([
            'data' => $departments->items(),
            'meta' => [
                'current_page' => $departments->currentPage(),
                'last_page' => $departments->lastPage(),
                'total' => $departments->total(),
            ],
        ]);
    }

    public function show(Department $department)
    {
        $department->load('headEmployee');

        return response()->json([
            'data' => $department,
        ]);
    }

    public function store(Request $request)
    {
        $department = Department::create(
            $this->validatedAttributes($request)
        );

        $department->load('headEmployee');

        return response()->json([
            'message' => 'Department created successfully.',
            'data' => $department,
        ], 201);
    }

    public function update(Request $request, Department $department)
    {
        $department->update(
            $this->validatedAttributes($request, $department)
        );

        $department->load('headEmployee');

        return response()->json([
            'message' => 'Department updated successfully.',
            'data' => $department,
        ]);
    }

    public function toggleStatus(Request $request, Department $department)
    {
        $attributes = $request->validate([
            'is_active' => 'required|boolean',
        ]);

        $department->is_active = $attributes['is_active'];
        $department->save();

        return response()->json([
            'message' => 'Department status updated successfully.',
            'data' => $department,
        ]);
    }

    private function validatedAttributes(Request $request, Department $department = null)
    {
        $id = $department ? ',' . $department->id : '';

        return $request->validate([
            'code' => 'required|string|max:16|unique:departments,code' . $id,

            'name' => 'required|string|max:100',

            'location' => 'required|string|max:100',

            'email' => 'required|email|max:190|unique:departments,email' . $id,

            'contact_no' => [
                'required',
                'string',
                'max:20',
                'regex:/^\+?(?=(?:\D*\d){7,15}\D*$)[0-9().\s-]+$/'
            ],

            'head_employee_id' => [
                'required',
                'integer',
                'exists:employees,id'
            ],
        ]);
    }
}