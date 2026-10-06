<?php

use App\Http\Controllers\Auth\PortalController as AuthPortalController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HolidayController;
use App\Http\Controllers\HR\EmployeeController;
use App\Http\Controllers\HR\LookupController;
use App\Http\Controllers\SuperAdmin\DepartmentController;
use App\Http\Controllers\SuperAdmin\EmployeeRoleController;
use App\Http\Controllers\SuperAdmin\EmployeeTypeController;
use App\Http\Controllers\SuperAdmin\ModuleAccessController;
use App\Http\Controllers\SuperAdmin\SystemUserController;
use App\Http\Controllers\SuperAdmin\UserRoleManagementController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AuthPortalController::class, 'entry'])->name('home');
Route::get('/login', [AuthPortalController::class, 'loginForm'])->name('login');
Route::post('/login', [AuthPortalController::class, 'login'])->middleware('login.throttle')->name('login.store');
Route::post('/logout', [AuthPortalController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->prefix('portal')->name('portal.')->group(function () {
    Route::get('/', [DashboardController::class, 'page'])->name('dashboard');
    Route::get('/account', [AccountController::class, 'edit'])->name('account.edit');
    Route::put('/account/password', [AccountController::class, 'updatePassword'])->name('account.password.update');
    Route::get('/employees', [EmployeeController::class, 'indexPage'])->middleware('permission:employees,view')->name('employees.index');
    Route::get('/employees/create', [EmployeeController::class, 'createOnboarding'])->middleware('permission:employees,create')->name('employees.create');
    Route::get('/employees/{employee}/edit', [EmployeeController::class, 'editOnboarding'])->middleware('permission:employees,edit')->name('employees.edit');
    Route::get('/employee-documents/{document}/preview', [EmployeeController::class, 'previewDocument'])->name('employee-documents.preview');
    Route::get('/employee-documents/{document}', [EmployeeController::class, 'downloadDocument'])->name('employee-documents.download');
    Route::get('/employees/{employee}', [EmployeeController::class, 'profilePage'])->name('employees.show');
    Route::get('/departments', [DepartmentController::class, 'index'])->middleware('permission:departments,view')->name('departments.index');
    Route::get('/holidays', [HolidayController::class, 'index'])->middleware('permission:holidays,view')->name('holidays.index');
    Route::post('/holidays', [HolidayController::class, 'store'])->middleware('permission:holidays,create')->name('holidays.store');
    Route::put('/holidays/{holiday}', [HolidayController::class, 'update'])->middleware('permission:holidays,edit')->name('holidays.update');
    Route::delete('/holidays/{holiday}', [HolidayController::class, 'destroy'])->middleware('permission:holidays,delete')->name('holidays.destroy');
    Route::middleware('superadmin')->group(function () {
        Route::get('/system-users', [SystemUserController::class, 'index'])->name('system-users.index');
        Route::get('/system-users/data', [SystemUserController::class, 'data'])->name('system-users.data');
        Route::patch('/system-users/{user}/status', [SystemUserController::class, 'updateStatus'])->name('system-users.status');
        Route::post('/system-users/{user}/password', [SystemUserController::class, 'updatePassword'])->name('system-users.password');
        Route::get('/module-access', [ModuleAccessController::class, 'index'])->name('module-access.index');
        Route::put('/module-access', [ModuleAccessController::class, 'update'])->name('module-access.update');
        Route::get('/user-roles', [UserRoleManagementController::class, 'index'])->name('user-roles.index');
        Route::post('/user-roles', [UserRoleManagementController::class, 'store'])->name('user-roles.store');
        Route::put('/user-roles/{userRole}', [UserRoleManagementController::class, 'update'])->name('user-roles.update');
        Route::patch('/user-roles/{userRole}/status', [UserRoleManagementController::class, 'updateStatus'])->name('user-roles.status');
    });
    Route::post('/data/employees/onboarding', [EmployeeController::class, 'storeOnboarding'])->middleware('permission:employees,create')->name('data.employees.onboarding.store');
    Route::put('/data/employees/{employee}/onboarding', [EmployeeController::class, 'updateOnboarding'])->middleware('permission:employees,edit')->name('data.employees.onboarding.update');
    Route::get('/data/employees/code', [EmployeeController::class, 'generateEmployeeCode'])->middleware('permission:employees,create')->name('data.employees.code');
    Route::get('/employee-types', [EmployeeTypeController::class, 'index'])->middleware('permission:employee_types,view')->name('employee-types.index');
    Route::get('/employee-roles', [EmployeeRoleController::class, 'index'])->middleware('permission:employee_roles,view')->name('employee-roles.index');

    Route::prefix('data')->name('data.')->group(function () {
        Route::get('/employees', [EmployeeController::class, 'index'])->middleware('permission:employees,view')->name('employees.index');
        Route::post('/employees', [EmployeeController::class, 'store'])->middleware('permission:employees,create')->name('employees.store');
        Route::get('/employees/{employee}', [EmployeeController::class, 'show'])->middleware('permission:employees,view')->name('employees.show');
        Route::put('/employees/{employee}', [EmployeeController::class, 'update'])->middleware('permission:employees,edit')->name('employees.update');
        Route::patch('/employees/{employee}/status', [EmployeeController::class, 'updateStatus'])->middleware('permission:employees,delete')->name('employees.status');
        Route::get('/departments', [DepartmentController::class, 'search'])->middleware('permission:departments,view')->name('departments.index');
        Route::post('/departments', [DepartmentController::class, 'store'])->middleware('permission:departments,create')->name('departments.store');
        Route::get('/departments/{department}', [DepartmentController::class, 'show'])->middleware('permission:departments,view')->name('departments.show');
        Route::put('/departments/{department}', [DepartmentController::class, 'update'])->middleware('permission:departments,edit')->name('departments.update');
        Route::patch('/departments/{department}/status', [DepartmentController::class, 'toggleStatus'])->middleware('permission:departments,delete')->name('departments.status');

        Route::get('/lookups/departments', [LookupController::class, 'departments'])->middleware('permission:employees,view')->name('lookups.departments');
        Route::get('/lookups/employee-types', [LookupController::class, 'employeeTypes'])->middleware('permission:employees,view')->name('lookups.employee-types');
        Route::get('/lookups/employee-roles', [LookupController::class, 'employeeRoles'])->middleware('permission:employees,view')->name('lookups.employee-roles');

        Route::post('/employee-types', [EmployeeTypeController::class, 'store'])->middleware('permission:employee_types,create')->name('employee-types.store');
        Route::put('/employee-types/{employeeType}', [EmployeeTypeController::class, 'update'])->middleware('permission:employee_types,edit')->name('employee-types.update');
        Route::patch('/employee-types/{employeeType}/status', [EmployeeTypeController::class, 'updateStatus'])->middleware('permission:employee_types,delete')->name('employee-types.status');
        Route::post('/employee-roles', [EmployeeRoleController::class, 'store'])->middleware('permission:employee_roles,create')->name('employee-roles.store');
        Route::put('/employee-roles/{employeeRole}', [EmployeeRoleController::class, 'update'])->middleware('permission:employee_roles,edit')->name('employee-roles.update');
        Route::patch('/employee-roles/{employeeRole}/status', [EmployeeRoleController::class, 'updateStatus'])->middleware('permission:employee_roles,delete')->name('employee-roles.status');
    });
});
