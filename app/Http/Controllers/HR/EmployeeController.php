<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeDocument;
use App\Models\EmployeeRole;
use App\Models\EmployeeType;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class EmployeeController extends Controller
{
    private $relations = ['department:id,name,code', 'employeeType:id,name', 'employeeRole:id,name'];

    public function createOnboarding()
    {
        return $this->renderOnboardingForm();
    }

    public function generateEmployeeCode(Request $request)
    {
        $attributes = $request->validate([
            'department_id' => 'required|integer|exists:departments,id',
        ]);
        $department = Department::where('is_active', true)->findOrFail($attributes['department_id']);

        return response()->json([
            'data' => [
                'employee_code' => $this->nextEmployeeCode($department),
            ],
        ]);
    }

    public function editOnboarding(Employee $employee)
    {
        $employee->load(['experiences', 'documents']);
        $bankDetails = $employee->bankDetails;
        $bank = $bankDetails ? [
            'account_holder' => $bankDetails->account_holder,
            'account_last_four' => substr(Crypt::decryptString($bankDetails->account_number_encrypted), -4),
            'bank_name' => $bankDetails->bank_name,
            'ifsc_code' => $bankDetails->ifsc_code,
            'branch' => $bankDetails->branch,
            'account_type' => $bankDetails->account_type,
        ] : [];

        return $this->renderOnboardingForm($employee, $bank);
    }

    private function renderOnboardingForm($employee = null, array $bank = [])
    {
        return view('portal.employee_management.add-update', [
            'employee' => $employee,
            'bank' => $bank,
            'departments' => Department::where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'employeeTypes' => EmployeeType::where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'employeeRoles' => EmployeeRole::where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'managers' => Employee::where('is_active', true)->when($employee, function ($query) use ($employee) {
                $query->where('id', '!=', $employee->id);
            })->orderBy('first_name')->orderBy('last_name')->get(['id', 'first_name', 'last_name', 'employee_code']),
            'pageTitle' => $employee ? 'Update employee onboarding' : 'Employee onboarding',
        ]);
    }

    public function indexPage(Request $request)
    {
        $response = $this->index($request);
        if ($response->getStatusCode() !== 200) {
            abort($response->getStatusCode(), 'The employee directory could not be loaded.');
        }

        $payload = $response->getData(true);

        $recordStatus = $request->input('record_status', 'active');

        return view('portal.employee_management.index', [
            'employees' => $payload['data'],
            'pagination' => $payload['meta'],
            'recordStatus' => $recordStatus,
            'activeEmployeeCount' => Employee::where('is_active', true)->count(),
            'inactiveEmployeeCount' => Employee::where('is_active', false)->count(),
            'departments' => Department::orderBy('name')->get(['id', 'name']),
            'employeeTypes' => EmployeeType::orderBy('name')->get(['id', 'name']),
            'employeeRoles' => EmployeeRole::orderBy('name')->get(['id', 'name']),
            'search' => $request->input('search', ''),
            'status' => $request->input('status', ''),
            'departmentId' => $request->input('department_id', ''),
            'employeeTypeId' => $request->input('employee_type_id', ''),
            'employeeRoleId' => $request->input('employee_role_id', ''),
            'joinedFrom' => $request->input('from', ''),
            'joinedTo' => $request->input('to', ''),
            'canCreateEmployees' => $request->user()->hasPermission('employees', 'create'),
            'canEditEmployees' => $request->user()->hasPermission('employees', 'edit'),
            'canDeleteEmployees' => $request->user()->hasPermission('employees', 'delete'),
            'employeeDataUrl' => route('portal.data.employees.index'),
            'pageTitle' => 'Employees',
        ]);
    }

    public function profilePage(Request $request, Employee $employee)
    {
        $user = $request->user();
        if ($user->isModuleEnabled('employee_profile', 'view')
            && $user->employee
            && (int) $user->employee_id === (int) $employee->id) {
            $view = 'portal.employee_management.my-profile';
        } else {
            abort_unless($user->hasPermission('employees', 'view'), 403);
            $view = 'portal.employee_management.show';
        }

        $relations = array_merge($this->relations, [
            'manager:id,first_name,last_name,employee_code',
            'createdBy:id,name',
            'documents',
            'experiences',
            'bankDetails',
        ]);
        $employee->load($relations);
        $bankDetails = null;
        if ($employee->bankDetails) {
            $bankDetails = [
                'account_holder' => $employee->bankDetails->account_holder,
                'account_number' => Crypt::decryptString($employee->bankDetails->account_number_encrypted),
                'bank_name' => $employee->bankDetails->bank_name,
                'ifsc_code' => $employee->bankDetails->ifsc_code,
                'branch' => $employee->bankDetails->branch,
                'account_type' => $employee->bankDetails->account_type,
            ];
        }

        return view($view, [
            'employee' => $employee,
            'bankDetails' => $bankDetails,
            'pageTitle' => $employee->full_name,
        ]);
    }

    public function index(Request $request)
    {
        $request->validate([
            'search' => 'nullable|string|max:100',
            'department_id' => 'nullable|integer|exists:departments,id',
            'employee_type_id' => 'nullable|integer|exists:employee_types,id',
            'employee_role_id' => 'nullable|integer|exists:employee_roles,id',
            'status' => 'nullable|in:active,on_leave,notice_period,inactive',
            'from' => 'nullable|date',
            'to' => 'nullable|date',
            'sort' => 'nullable|in:employee_code,first_name,last_name,email,joining_date,employment_status',
            'direction' => 'nullable|in:asc,desc',
            'page' => 'nullable|integer|min:1',
            'record_status' => 'nullable|in:active,inactive',
        ]);

        $recordStatus = $request->input('record_status', 'active');
        $query = Employee::with($this->relations)->where('is_active', $recordStatus === 'active');
        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $searchTerms = preg_split('/\s+/', $search, -1, PREG_SPLIT_NO_EMPTY);
            $query->where(function ($builder) use ($searchTerms) {
                foreach ($searchTerms as $term) {
                    $builder->where(function ($termQuery) use ($term) {
                        $termQuery->where('first_name', 'like', '%' . $term . '%')
                            ->orWhere('last_name', 'like', '%' . $term . '%')
                            ->orWhere('employee_code', 'like', '%' . $term . '%')
                            ->orWhere('email', 'like', '%' . $term . '%');
                    });
                }
            });
        }
        foreach (['department_id', 'employee_type_id', 'employee_role_id'] as $filter) {
            if ($request->filled($filter)) {
                $query->where($filter, $request->input($filter));
            }
        }
        if ($request->filled('status')) {
            $query->where('employment_status', $request->input('status'));
        }
        if ($request->filled('from')) {
            $query->where('joining_date', '>=', $request->input('from'));
        }
        if ($request->filled('to')) {
            $query->where('joining_date', '<=', $request->input('to'));
        }

        $sorts = ['employee_code', 'first_name', 'last_name', 'email', 'joining_date', 'employment_status'];
        $sort = in_array($request->input('sort'), $sorts, true) ? $request->input('sort') : 'last_name';
        $direction = strtolower($request->input('direction', 'asc')) === 'desc' ? 'desc' : 'asc';
        $employees = $query->orderBy($sort, $direction)->orderBy('id')->paginate(50);
        $html = view('portal.employee_management.table', [
            'employees' => $employees->items(),
            'actions' => $request->user()->hasPermission('employees', 'view')
                || $request->user()->hasPermission('employees', 'edit')
                || $request->user()->hasPermission('employees', 'delete'),
            'canView' => $request->user()->hasPermission('employees', 'view'),
            'canEdit' => $request->user()->hasPermission('employees', 'edit'),
            'canDelete' => $request->user()->hasPermission('employees', 'delete'),
        ])->render();

        return response()->json([
            'data' => $employees->items(),
            'meta' => [
                'current_page' => $employees->currentPage(),
                'last_page' => $employees->lastPage(),
                'total' => $employees->total(),
            ],
            'html' => $html,
        ]);
    }

    public function store(Request $request)
    {
        $attributes = $request->validate($this->rules());
        $attributes['city'] = 'Pune';
        $attributes['state'] = 'Maharashtra';
        $attributes['created_by'] = $request->user()->id;

        try {
            $employee = Employee::create($attributes)->load($this->relations);

            return response()->json(['data' => $employee], 201);
        } catch (\Throwable $exception) {
            Log::error('Employee could not be created.', [
                'user_id' => $request->user()->id,
                'exception' => get_class($exception),
            ]);

            return response()->json(['message' => 'The employee could not be saved. Please try again.'], 500);
        }
    }

    public function storeOnboarding(Request $request)
    {
        $validator = Validator::make($request->all(), $this->onboardingRules());
        $this->validateExperienceDateRanges($validator, $request);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput($request->except(['bank', 'documents', 'required_documents']));
        }
        $validated = $validator->validated();
        $storedFiles = [];

        try {
            DB::transaction(function () use ($request, $validated, &$storedFiles) {
                $department = Department::where('is_active', true)
                    ->lockForUpdate()
                    ->findOrFail($validated['department_id']);
                $validated['employee_code'] = $this->nextEmployeeCode($department);
                $employeeAttributes = $this->onboardingEmployeeAttributes($validated);
                $employeeAttributes['created_by'] = $request->user()->id;
                $employee = Employee::create($employeeAttributes);
                $employeeRole = UserRole::where('name', 'employee')
                    ->where('is_active', true)
                    ->lockForUpdate()
                    ->firstOrFail();
                User::create([
                    'name' => trim($employee->first_name . ' ' . $employee->last_name),
                    'email' => $employee->email,
                    'password' => Hash::make('password'),
                    'role_id' => $employeeRole->id,
                    'is_active' => true,
                    'employee_id' => $employee->id,
                ]);
                $this->saveOnboardingBankDetails($employee, $validated['bank'] ?? []);
                $this->savePreviousExperience($employee, $validated['experience'] ?? []);
                $this->saveOnboardingDocuments($employee, $validated['documents'] ?? [], $request, $storedFiles);

                return $employee;
            });

            return redirect()->route('portal.employees.index')
                ->with('status', 'Employee onboarding details saved. Login email: ' . $validated['email'] . '. Temporary password: password. Ask the employee to change it after signing in.');
        } catch (\Throwable $exception) {
            foreach ($storedFiles as $path) {
                Storage::disk('local')->delete($path);
            }
            Log::error('Employee onboarding could not be saved.', [
                'user_id' => $request->user()->id,
                'exception' => get_class($exception),
            ]);

            return back()->withInput($request->except(['bank', 'documents', 'required_documents']))->withErrors([
                'onboarding' => 'The employee onboarding details could not be saved. Please try again.',
            ]);
        }
    }

    public function updateOnboarding(Request $request, Employee $employee)
    {
        $validator = Validator::make($request->all(), $this->onboardingRules($employee));
        $this->validateExperienceDateRanges($validator, $request);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput($request->except(['bank', 'documents', 'required_documents']));
        }
        $validated = $validator->validated();
        foreach ($validated['experience'] ?? [] as $experience) {
            if (!empty($experience['id']) && !$employee->experiences()->where('id', $experience['id'])->exists()) {
                abort(404, 'Previous experience record not found.');
            }
        }
        $storedFiles = [];

        try {
            DB::transaction(function () use ($request, $validated, $employee, &$storedFiles) {
                if ((int) $validated['department_id'] !== (int) $employee->department_id) {
                    $department = Department::where('is_active', true)
                        ->lockForUpdate()
                        ->findOrFail($validated['department_id']);
                    $validated['employee_code'] = $this->nextEmployeeCode($department);
                } else {
                    $validated['employee_code'] = $employee->employee_code;
                }
                $employee->update($this->onboardingEmployeeAttributes($validated));
                if ($employee->user) {
                    $employee->user->update([
                        'name' => trim($employee->first_name . ' ' . $employee->last_name),
                        'email' => $employee->email,
                    ]);
                }
                $this->saveOnboardingBankDetails($employee, $validated['bank'] ?? []);
                $this->savePreviousExperience($employee, $validated['experience'] ?? [], true);
                $this->saveOnboardingDocuments($employee, $validated['documents'] ?? [], $request, $storedFiles);
            });

            return redirect()->route('portal.employees.index')
                ->with('status', 'Employee onboarding details updated successfully.');
        } catch (\Throwable $exception) {
            foreach ($storedFiles as $path) {
                Storage::disk('local')->delete($path);
            }
            Log::error('Employee onboarding could not be updated.', [
                'employee_id' => $employee->id,
                'user_id' => $request->user()->id,
                'exception' => get_class($exception),
            ]);

            return back()->withInput($request->except(['bank', 'documents', 'required_documents']))->withErrors([
                'onboarding' => 'The employee onboarding details could not be updated. Please try again.',
            ]);
        }
    }

    public function downloadDocument(Request $request, EmployeeDocument $document)
    {
        abort_unless($this->canAccessDocument($request->user(), $document), 403);
        abort_unless(Storage::disk('local')->exists($document->storage_path), 404, 'Employee document not found.');

        return Storage::disk('local')->download($document->storage_path, basename($document->original_name));
    }

    public function previewDocument(Request $request, EmployeeDocument $document)
    {
        abort_unless($this->canAccessDocument($request->user(), $document), 403);
        abort_unless(in_array(strtolower($document->mime_type), ['image/jpeg', 'image/png'], true), 404, 'Image preview is not available for this document.');
        abort_unless(Storage::disk('local')->exists($document->storage_path), 404, 'Employee document not found.');

        return response()->file(Storage::disk('local')->path($document->storage_path), [
            'Content-Type' => $document->mime_type,
            'Content-Disposition' => 'inline',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function canAccessDocument(User $user, EmployeeDocument $document)
    {
        if ($user->hasPermission('employees', 'view')) {
            return true;
        }

        return $user->hasPermission('employee_profile', 'view')
            && (int) $user->employee_id === (int) $document->employee_id;
    }

    public function show(Employee $employee)
    {
        return response()->json(['data' => $employee->load(array_merge($this->relations, ['manager:id,first_name,last_name']))]);
    }

    public function update(Request $request, Employee $employee)
    {
        $attributes = $request->validate($this->rules($employee));

        try {
            $employee->update($attributes);

            return response()->json(['data' => $employee->fresh($this->relations)]);
        } catch (\Throwable $exception) {
            Log::error('Employee record could not be updated.', [
                'employee_id' => $employee->id,
                'user_id' => $request->user()->id,
                'exception' => get_class($exception),
            ]);

            return response()->json(['message' => 'The employee record could not be updated. Please try again.'], 500);
        }
    }

    public function updateStatus(Request $request, Employee $employee)
    {
        $attributes = $request->validate(['is_active' => 'required|boolean']);

        try {
            DB::transaction(function () use ($employee, $attributes) {
                $employee->update($attributes);
                if ($employee->user) {
                    $employee->user->update(['is_active' => (bool) $attributes['is_active']]);
                }
            });

            return response()->json([
                'data' => $employee->fresh($this->relations),
                'message' => $employee->is_active ? 'Employee record activated.' : 'Employee record deactivated.',
            ]);
        } catch (\Throwable $exception) {
            Log::error('Employee record status could not be changed.', [
                'employee_id' => $employee->id,
                'user_id' => $request->user()->id,
                'exception' => get_class($exception),
            ]);

            return response()->json(['message' => 'Employee status could not be changed. Please try again.'], 500);
        }
    }

    private function rules($employee = null)
    {
        $id = $employee ? ',' . $employee->id : '';
        $personName = 'regex:/^[\p{L}\p{M}][\p{L}\p{M} .\'-]*$/u';
        $phone = [
            'nullable',
            'string',
            'max:20',
            'regex:/^\+?[0-9][0-9\s().-]{5,18}[0-9]$/',
            function ($attribute, $value, $fail) {
                $digits = preg_replace('/\D/', '', $value);
                if (strlen($digits) < 7 || strlen($digits) > 15) {
                    $fail('The ' . str_replace('_', ' ', $attribute) . ' must contain 7 to 15 digits.');
                }
            },
        ];
        $departmentRule = Rule::exists('departments', 'id')
            ->where(function ($query) use ($employee) {
                $query->where('is_active', true);
                if ($employee) {
                    $query->orWhere('id', $employee->department_id);
                }
            });
        $employeeTypeRule = Rule::exists('employee_types', 'id')
            ->where(function ($query) use ($employee) {
                $query->where('is_active', true);
                if ($employee) {
                    $query->orWhere('id', $employee->employee_type_id);
                }
            });
        $employeeRoleRule = Rule::exists('employee_roles', 'id')
            ->where(function ($query) use ($employee) {
                $query->where('is_active', true);
                if ($employee) {
                    $query->orWhere('id', $employee->employee_role_id);
                }
            });

        return [
            'employee_code' => ($employee ? 'sometimes|' : '') . 'required|string|min:2|max:20|regex:/^[A-Za-z0-9][A-Za-z0-9_-]*$/|unique:employees,employee_code' . $id,
            'first_name' => ($employee ? 'sometimes|' : '') . 'required|string|max:80|' . $personName,
            'last_name' => ($employee ? 'sometimes|' : '') . 'required|string|max:80|' . $personName,
            'email' => ($employee ? 'sometimes|' : '') . 'required|email|max:190|unique:employees,email' . $id,
            'phone' => $phone,
            'gender' => 'nullable|in:female,male,non_binary,prefer_not_to_say',
            'date_of_birth' => 'nullable|date_format:Y-m-d|before:today',
            'department_id' => array_merge(
                [$employee ? 'sometimes' : 'required', 'integer'],
                [$departmentRule]
            ),
            'employee_type_id' => array_merge(
                [$employee ? 'sometimes' : 'required', 'integer'],
                [$employeeTypeRule]
            ),
            'employee_role_id' => array_merge(
                [$employee ? 'sometimes' : 'required', 'integer'],
                [$employeeRoleRule]
            ),
            'manager_id' => 'nullable|exists:employees,id',
            'employment_status' => ($employee ? 'sometimes|' : '') . 'required|in:active,on_leave,notice_period,inactive',
            'joining_date' => ($employee ? 'sometimes|' : '') . 'required|date_format:Y-m-d',
            'address_line' => 'nullable|string|max:190',
            'postal_code' => 'nullable|regex:/^[1-9][0-9]{5}$/',
            'city' => 'nullable|string|max:80|' . $personName,
            'state' => 'nullable|string|max:80|' . $personName,
            'emergency_contact_name' => 'nullable|string|max:120|' . $personName,
            'emergency_contact_relationship' => 'nullable|string|max:60|' . $personName,
            'emergency_contact_phone' => $phone,
        ];
    }

    private function onboardingRules($employee = null)
    {
        $employeeCodeRule = 'nullable|string|max:20';
        $linkedUserId = $employee && $employee->user ? ',' . $employee->user->id : '';
        $emailRule = ($employee ? 'sometimes|' : '') . 'required|email|max:190|unique:employees,email';
        if ($employee) {
            $emailRule .= ',' . $employee->id;
        }
        $emailRule .= '|unique:users,email' . $linkedUserId;
        $hasExistingBankDetails = $employee && $employee->bankDetails()->exists();
        $accountNumberRequirements = $hasExistingBankDetails
            ? 'nullable|string|regex:/^[0-9]{9,18}$/'
            : 'nullable|required_with:bank.account_holder,bank.bank_name,bank.ifsc_code,bank.branch,bank.account_type|string|regex:/^[0-9]{9,18}$/';
        $rules = array_merge($this->rules($employee), [
            'employee_code' => $employeeCodeRule,
            'email' => $emailRule,
            'bank' => 'nullable|array',
            'bank.account_holder' => 'nullable|required_with:bank.account_number|string|max:120|regex:/^[\p{L}\p{M}][\p{L}\p{M} .\'-]*$/u',
            'bank.account_number' => $accountNumberRequirements,
            'bank.bank_name' => 'nullable|required_with:bank.account_number|string|max:120|regex:/^[\p{L}\p{M}0-9][\p{L}\p{M}0-9 .,&\'()-]*$/u',
            'bank.ifsc_code' => 'nullable|required_with:bank.account_number|string|regex:/^[A-Z]{4}0[A-Z0-9]{6}$/i',
            'bank.branch' => 'nullable|string|max:120|regex:/^[\p{L}\p{M}0-9][\p{L}\p{M}0-9 .,&\'()-]*$/u',
            'bank.account_type' => 'nullable|in:savings,current,salary,other',
            'experience' => 'nullable|array',
            'experience.*.id' => 'nullable|integer|distinct',
            'experience.*.company_name' => 'nullable|required_with:experience.*.job_title|string|max:120|regex:/^[\p{L}\p{M}0-9][\p{L}\p{M}0-9 .,&\'()-]*$/u',
            'experience.*.job_title' => 'nullable|required_with:experience.*.company_name|string|max:120|regex:/^[\p{L}\p{M}0-9][\p{L}\p{M}0-9 .,&\'()-]*$/u',
            'experience.*.start_date' => 'nullable|date_format:Y-m-d|before_or_equal:today',
            'experience.*.end_date' => 'nullable|date_format:Y-m-d|before_or_equal:today',
            'experience.*.summary' => 'nullable|string|max:2000',
            'required_documents' => 'nullable|array',
            'documents' => 'nullable|array|max:10',
            'documents.*.title' => 'required_with:documents.*.file|string|max:100|regex:/^[\p{L}\p{M}0-9][\p{L}\p{M}0-9 .,&()\'_-]*$/u',
            'documents.*.file' => 'required_with:documents.*.title|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
        ]);

        $existingDocuments = $employee ? $this->existingRequiredDocumentTypes($employee) : [];
        foreach ($this->requiredDocumentLabels() as $type => $label) {
            $required = isset($existingDocuments[$type]) ? 'nullable' : 'required';
            $fileTypes = $type === 'photo'
                ? 'mimes:jpg,jpeg,png'
                : 'mimes:pdf,jpg,jpeg,png';
            $rules['required_documents.' . $type] = $required . '|file|' . $fileTypes . '|max:10240';
        }

        return $rules;
    }

    private function nextEmployeeCode(Department $department)
    {
        $prefix = strtoupper(trim($department->code));
        $prefix = ltrim(preg_replace('/[^A-Z0-9_-]/', '', $prefix), '-_');
        if ($prefix === '') {
            throw new \RuntimeException('The department code cannot be used to generate an employee ID.');
        }

        $count = Employee::where('department_id', $department->id)->count();
        $sequence = $count + 1;
        $sequenceWidth = min(4, 19 - strlen($prefix));
        if ($sequenceWidth < 1) {
            throw new \RuntimeException('The department code is too long to generate an employee ID.');
        }

        do {
            if (strlen((string) $sequence) > $sequenceWidth) {
                throw new \RuntimeException('The department has reached its employee ID sequence limit.');
            }
            $employeeCode = $prefix . '-' . str_pad((string) $sequence, $sequenceWidth, '0', STR_PAD_LEFT);
            $sequence++;
        } while (Employee::where('employee_code', $employeeCode)->exists());

        return $employeeCode;
    }

    private function validateExperienceDateRanges($validator, Request $request)
    {
        foreach ($request->input('experience', []) as $index => $experience) {
            if (!is_array($experience)) {
                continue;
            }
            $start = $experience['start_date'] ?? null;
            $end = $experience['end_date'] ?? null;
            if (!$start || !$end
                || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $start)
                || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $end)) {
                continue;
            }

            if ($end < $start) {
                $validator->errors()->add(
                    'experience.' . $index . '.end_date',
                    'The end date must be on or after the start date.'
                );
            }
        }
    }

    private function requiredDocumentLabels()
    {
        return [
            'photo' => 'Photo',
            'aadhaar' => 'Aadhaar Card',
            'bank_passbook' => 'Bank Passbook',
        ];
    }

    private function existingRequiredDocumentTypes(Employee $employee)
    {
        $aliases = [
            'photo' => ['photo', 'employee photo', 'photograph', 'profile photo'],
            'aadhaar' => ['aadhaar', 'aadhaar card', 'aadhar', 'aadhar card'],
            'bank_passbook' => ['bank passbook', 'passbook', 'bank passbook copy'],
        ];
        $existing = [];

        foreach ($employee->documents()->get(['title']) as $document) {
            $title = strtolower(trim($document->title));
            foreach ($aliases as $type => $titles) {
                if (in_array($title, $titles, true)) {
                    $existing[$type] = true;
                }
            }
        }

        return $existing;
    }

    private function onboardingEmployeeAttributes(array $validated)
    {
        $attributes = $validated;
        unset($attributes['bank'], $attributes['experience'], $attributes['documents'], $attributes['required_documents']);
        $attributes['city'] = $attributes['city'] ?: 'Pune';
        $attributes['state'] = $attributes['state'] ?: 'Maharashtra';

        return $attributes;
    }

    private function saveOnboardingBankDetails(Employee $employee, array $bank)
    {
        if (empty($bank['account_number'])) {
            return;
        }

        $employee->bankDetails()->updateOrCreate([], [
            'account_holder' => $bank['account_holder'],
            'account_number_encrypted' => Crypt::encryptString($bank['account_number']),
            'bank_name' => $bank['bank_name'],
            'ifsc_code' => strtoupper($bank['ifsc_code']),
            'branch' => $bank['branch'] ?? null,
            'account_type' => $bank['account_type'] ?? null,
        ]);
    }

    private function savePreviousExperience(Employee $employee, array $experiences, $replace = false)
    {
        foreach ($experiences as $experience) {
            if (empty($experience['company_name']) && empty($experience['job_title'])) {
                continue;
            }

            $experienceId = $experience['id'] ?? null;
            unset($experience['id']);

            if ($experienceId && $replace) {
                $record = $employee->experiences()->where('id', $experienceId)->first();
                abort_if(!$record, 404, 'Previous experience record not found.');
                $record->update($experience);
            } else {
                $employee->experiences()->create($experience);
            }
        }
    }

    private function saveOnboardingDocuments(Employee $employee, array $documents, Request $request, array &$storedFiles)
    {
        $uploads = [];
        foreach ($this->requiredDocumentLabels() as $type => $label) {
            $file = $request->file('required_documents.' . $type);
            if ($file) {
                $uploads[] = ['title' => $label, 'file' => $file];
            }
        }

        foreach ($documents as $index => $document) {
            $file = $request->file('documents.' . $index . '.file');
            if (!$file) {
                continue;
            }

            $uploads[] = ['title' => $document['title'], 'file' => $file];
        }

        foreach ($uploads as $upload) {
            $file = $upload['file'];
            $path = $file->store('employee-documents', 'local');
            if (!$path) {
                throw new \RuntimeException('The employee document could not be stored.');
            }
            $storedFiles[] = $path;
            $employee->documents()->create([
                'title' => $upload['title'],
                'original_name' => $file->getClientOriginalName(),
                'storage_path' => $path,
                'mime_type' => $file->getClientMimeType(),
                'file_size' => $file->getSize(),
            ]);
        }
    }
}
