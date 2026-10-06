@extends('layouts.portal')

@section('content')

@php
    $user = auth()->user();

    $canCreate = $user->hasPermission('departments', 'create');
    $canView   = $user->hasPermission('departments', 'view');
    $canEdit   = $user->hasPermission('departments', 'edit');

    $activeCount = (int) $activeDepartmentCount;
    $inactiveCount = (int) $inactiveDepartmentCount;
    $totalCount = $activeCount + $inactiveCount;

    $activePercent = $totalCount
        ? round(($activeCount / $totalCount) * 100)
        : 0;
@endphp

<style>
.dept-page{max-width:1400px;margin:auto;padding:28px;color:#172033}
.dept-header{display:flex;justify-content:space-between;align-items:flex-end;gap:20px;margin-bottom:24px}
.dept-eyebrow{margin:0 0 6px;font-size:12px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#64748b}
.dept-header h1{margin:0;font-size:30px;font-weight:800;color:#111827}
.dept-subtitle{margin:7px 0 0;color:#64748b;font-size:14px}

.dept-btn{border:0;border-radius:8px;padding:11px 17px;font-weight:700;cursor:pointer;font-size:13px}
.dept-btn-primary{background:#111827;color:#fff}
.dept-btn-primary:hover{background:#000}
.dept-btn-light{background:#f1f5f9;color:#334155}

.dept-summary{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:20px}
.dept-stat{background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:18px}
.dept-stat-label{font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.05em}
.dept-stat-value{margin-top:7px;font-size:27px;font-weight:800;color:#111827}
.dept-stat-note{margin-top:4px;font-size:12px;color:#64748b}

.dept-card{background:#fff;border:1px solid #e5e7eb;border-radius:13px;overflow:hidden}
.dept-status-tabs{display:flex;gap:7px;padding:14px 20px 0}
.dept-status-tabs a{text-decoration:none;padding:8px 13px;border-radius:7px;font-size:13px;font-weight:700;color:#64748b;background:#f8fafc}
.dept-status-tabs a.active{background:#111827;color:#fff}

.dept-toolbar{display:flex;justify-content:space-between;align-items:center;gap:16px;padding:17px 20px;border-bottom:1px solid #e5e7eb}
.dept-toolbar h2{margin:0;font-size:17px;font-weight:800;color:#111827}
.dept-toolbar p{margin:4px 0 0;font-size:12px;color:#64748b}
.dept-search{width:270px}
.dept-search input{width:100%;height:40px;padding:0 13px;border:1px solid #d7dde5;border-radius:8px;outline:0;font-size:13px;box-sizing:border-box}
.dept-search input:focus{border-color:#64748b}

.dept-table-wrap{overflow-x:auto}
.dept-table{width:100%;border-collapse:collapse;min-width:950px}
.dept-table th{padding:13px 20px;text-align:left;font-size:11px;text-transform:uppercase;letter-spacing:.05em;color:#64748b;background:#f8fafc;border-bottom:1px solid #e5e7eb;white-space:nowrap}
.dept-table td{padding:15px 20px;border-bottom:1px solid #edf0f3;font-size:13px;color:#475569;vertical-align:middle}
.dept-table tbody tr:hover{background:#fafafa}
.dept-code{display:inline-block;padding:5px 8px;border-radius:6px;background:#f1f5f9;color:#334155;font-size:11px;font-weight:800}
.dept-name{color:#111827;font-weight:750}

.dept-head{display:flex;align-items:center;gap:9px;min-width:180px}
.dept-avatar{width:34px;height:34px;border-radius:50%;background:#f1f5f9;display:flex;align-items:center;justify-content:center;color:#334155;font-weight:800;font-size:11px}
.dept-head-name{font-weight:700;color:#1f2937}
.dept-head-code{display:block;margin-top:2px;font-size:11px;color:#94a3b8}

.dept-status{display:inline-flex;align-items:center;gap:6px;font-size:12px;font-weight:700}
.dept-status-dot{width:7px;height:7px;border-radius:50%;background:#16a34a}
.dept-status.inactive .dept-status-dot{background:#94a3b8}

.dept-actions{display:flex;gap:6px;white-space:nowrap}
.dept-action{border:1px solid #e2e8f0;background:#fff;color:#475569;border-radius:7px;padding:7px 10px;cursor:pointer;font-size:12px;font-weight:700}
.dept-action:hover{background:#f8fafc}

.dept-empty{text-align:center;padding:55px 20px!important;color:#94a3b8!important}
.dept-pagination{padding:15px 20px}
.dept-pagination nav{display:flex;justify-content:center}

/* MODAL */
.dept-modal-backdrop{
    display:none;
    position:fixed;
    inset:0;
    z-index:9999;
    background:rgba(15,23,42,.48);
    padding:20px;
    overflow:auto;
}
.dept-modal-backdrop.show{display:flex;align-items:center;justify-content:center}

.dept-modal{
    width:100%;
    max-width:650px;
    background:#fff;
    border-radius:14px;
    box-shadow:0 25px 70px rgba(0,0,0,.2);
    animation:deptModal .18s ease;
}
@keyframes deptModal{
    from{opacity:0;transform:translateY(10px) scale(.98)}
    to{opacity:1;transform:none}
}

.dept-modal-head{
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:19px 22px;
    border-bottom:1px solid #e5e7eb;
}
.dept-modal-head h3{margin:0;font-size:19px;color:#111827}
.dept-modal-close{border:0;background:transparent;font-size:25px;color:#64748b;cursor:pointer}

.dept-form{padding:22px}
.dept-form-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.dept-field{display:flex;flex-direction:column;gap:6px}
.dept-field.full{grid-column:1/-1}
.dept-field label{font-size:12px;font-weight:750;color:#334155}
.dept-field input{width:100%;height:42px;padding:0 11px;border:1px solid #d7dde5;border-radius:8px;outline:0;font-size:13px;box-sizing:border-box}
.dept-field input:focus{border-color:#64748b}
.dept-error{font-size:11px;color:#dc2626;min-height:13px}

/* SEARCHABLE EMPLOYEE */
.employee-picker{position:relative}
.employee-search-wrap{position:relative}
.employee-search{
    width:100%;
    height:42px;
    padding:0 38px 0 12px;
    border:1px solid #d7dde5;
    border-radius:8px;
    outline:0;
    font-size:13px;
    box-sizing:border-box;
}
.employee-search:focus{border-color:#64748b}
.employee-search-icon{
    position:absolute;
    right:12px;
    top:12px;
    color:#94a3b8;
    font-size:15px;
}
.employee-selected{
    display:none;
    margin-top:7px;
    padding:8px 10px;
    border:1px solid #e2e8f0;
    border-radius:8px;
    background:#f8fafc;
    align-items:center;
    justify-content:space-between;
}
.employee-selected.show{display:flex}
.employee-selected-info strong{display:block;font-size:12px;color:#1f2937}
.employee-selected-info span{font-size:10px;color:#94a3b8}
.employee-remove{
    border:0;
    background:transparent;
    color:#64748b;
    cursor:pointer;
    font-size:16px;
}

.employee-results{
    display:none;
    position:absolute;
    left:0;
    right:0;
    top:49px;
    z-index:20;
    background:#fff;
    border:1px solid #dfe4ea;
    border-radius:9px;
    box-shadow:0 12px 30px rgba(15,23,42,.12);
    max-height:220px;
    overflow-y:auto;
}
.employee-results.show{display:block}

.employee-result{
    width:100%;
    display:flex;
    align-items:center;
    gap:10px;
    padding:10px 12px;
    border:0;
    background:#fff;
    text-align:left;
    cursor:pointer;
}
.employee-result:hover{background:#f8fafc}
.employee-result-avatar{
    width:30px;
    height:30px;
    border-radius:50%;
    background:#f1f5f9;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:10px;
    font-weight:800;
    color:#334155;
}
.employee-result strong{display:block;font-size:12px;color:#1f2937}
.employee-result span{font-size:10px;color:#94a3b8}
.employee-no-result{padding:14px;text-align:center;color:#94a3b8;font-size:12px}

/* VIEW MODAL */
.view-modal{max-width:560px}
.view-body{padding:22px}
.view-title{display:flex;align-items:center;gap:13px;margin-bottom:20px}
.view-avatar{width:52px;height:52px;border-radius:50%;background:#f1f5f9;display:flex;align-items:center;justify-content:center;font-size:16px;font-weight:800;color:#334155}
.view-title h4{margin:0;font-size:20px;color:#111827}
.view-title p{margin:4px 0 0;color:#64748b;font-size:12px}
.view-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.view-item{border:1px solid #edf0f3;border-radius:9px;padding:12px}
.view-item label{display:block;font-size:10px;text-transform:uppercase;letter-spacing:.05em;color:#94a3b8;font-weight:700;margin-bottom:5px}
.view-item strong{font-size:13px;color:#1f2937}
.view-status{display:inline-flex;align-items:center;gap:5px}
.view-status i{width:7px;height:7px;border-radius:50%;background:#16a34a}
.view-status.off i{background:#94a3b8}

.dept-modal-foot{
    display:flex;
    justify-content:flex-end;
    gap:9px;
    padding:16px 22px;
    border-top:1px solid #e5e7eb;
}

.dept-alert{display:none;margin-bottom:15px;padding:10px 12px;border-radius:8px;font-size:12px}
.dept-alert.show{display:block}
.dept-alert.error{background:#fef2f2;color:#991b1b;border:1px solid #fecaca}
.dept-alert.success{background:#f0fdf4;color:#166534;border:1px solid #bbf7d0}

@media(max-width:800px){
    .dept-page{padding:18px}
    .dept-header{align-items:flex-start;flex-direction:column}
    .dept-summary{grid-template-columns:1fr}
    .dept-toolbar{align-items:flex-start;flex-direction:column}
    .dept-search{width:100%}
    .dept-form-grid,.view-grid{grid-template-columns:1fr}
    .dept-field.full{grid-column:auto}
}
</style>


<div class="dept-page">

    <header class="dept-header">
        <div>
            <p class="dept-eyebrow">Super Admin · Organisation</p>
            <h1>Departments</h1>
            <p class="dept-subtitle">
                Manage departments and their assigned department heads.
            </p>
        </div>

        @if ($canCreate)
            <button type="button"
                    class="dept-btn dept-btn-primary"
                    onclick="openDepartmentModal()">
                + Add Department
            </button>
        @endif
    </header>

    <div id="dept-alert" class="dept-alert"></div>

    <section class="dept-summary">

        <div class="dept-stat">
            <div class="dept-stat-label">Total Departments</div>
            <div class="dept-stat-value">{{ $totalCount }}</div>
            <div class="dept-stat-note">All departments</div>
        </div>

        <div class="dept-stat">
            <div class="dept-stat-label">Active</div>
            <div class="dept-stat-value">{{ $activeCount }}</div>
            <div class="dept-stat-note">{{ $activePercent }}% currently active</div>
        </div>

        <div class="dept-stat">
            <div class="dept-stat-label">Inactive</div>
            <div class="dept-stat-value">{{ $inactiveCount }}</div>
            <div class="dept-stat-note">Currently disabled</div>
        </div>

    </section>

    <section class="dept-card">

        <div class="dept-status-tabs">
            <a href="{{ route('portal.departments.index', ['status'=>'active']) }}"
               class="{{ $selectedStatus === 'active' ? 'active' : '' }}">
                Active {{ $activeCount }}
            </a>

            <a href="{{ route('portal.departments.index', ['status'=>'inactive']) }}"
               class="{{ $selectedStatus === 'inactive' ? 'active' : '' }}">
                Inactive {{ $inactiveCount }}
            </a>
        </div>

        <div class="dept-toolbar">

            <div>
                <h2>Department List</h2>
                <p>{{ $departments->total() }} {{ $selectedStatus }} departments</p>
            </div>

            <div class="dept-search">
                <input type="search"
                       id="department-search"
                       placeholder="Search department, code or head...">
            </div>

        </div>

        <div class="dept-table-wrap">

            <table class="dept-table">

                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Department</th>
                        <th>Location</th>
                        <th>Email</th>
                        <th>Contact</th>
                        <th>Department Head</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>

                @forelse ($departments as $department)

                    @php
                        $head = $department->headEmployee;

                        $headName = $head
                            ? trim($head->first_name . ' ' . $head->last_name)
                            : 'Not assigned';

                        $initials = $head
                            ? strtoupper(
                                substr($head->first_name,0,1) .
                                substr($head->last_name,0,1)
                              )
                            : '--';
                    @endphp

                    <tr data-department-row
                        data-search="{{ strtolower($department->code.' '.$department->name.' '.$department->location.' '.$department->email.' '.$headName) }}">

                        <td>
                            <span class="dept-code">
                                {{ $department->code }}
                            </span>
                        </td>

                        <td>
                            <span class="dept-name">
                                {{ $department->name }}
                            </span>
                        </td>

                        <td>{{ $department->location }}</td>

                        <td>{{ $department->email }}</td>

                        <td>{{ $department->contact_no }}</td>

                        <td>
                            <div class="dept-head">

                                <div class="dept-avatar">
                                    {{ $initials }}
                                </div>

                                <div>
                                    <span class="dept-head-name">
                                        {{ $headName }}
                                    </span>

                                    @if ($head && $head->employee_code)
                                        <span class="dept-head-code">
                                            {{ $head->employee_code }}
                                        </span>
                                    @endif
                                </div>

                            </div>
                        </td>

                        <td>
                            <span class="dept-status {{ !$department->is_active ? 'inactive' : '' }}">
                                <span class="dept-status-dot"></span>
                                {{ $department->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>

                        <td>
                            <div class="dept-actions">

                                @if ($canView)
                                    <button class="dept-action"
                                            type="button"
                                            onclick="viewDepartment({{ $department->id }})">
                                        View
                                    </button>
                                @endif

                                @if ($canEdit)
                                    <button class="dept-action"
                                            type="button"
                                            onclick="editDepartment({{ $department->id }})">
                                        Edit
                                    </button>

                                    <button class="dept-action"
                                            type="button"
                                            onclick="toggleDepartment(
                                                {{ $department->id }},
                                                {{ $department->is_active ? 0 : 1 }}
                                            )">
                                        {{ $department->is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                @endif

                            </div>
                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="8" class="dept-empty">
                            No {{ $selectedStatus }} departments found.
                        </td>
                    </tr>

                @endforelse

                </tbody>

            </table>

        </div>

        <div class="dept-pagination">
            {{ $departments->links() }}
        </div>

    </section>

</div>


{{-- ADD / EDIT MODAL --}}
<div class="dept-modal-backdrop" id="department-modal">

    <div class="dept-modal">

        <div class="dept-modal-head">
            <h3 id="department-modal-title">Add Department</h3>

            <button type="button"
                    class="dept-modal-close"
                    onclick="closeDepartmentModal()">
                &times;
            </button>
        </div>

        <form id="department-form">

            <div class="dept-form">

                <div id="form-error" class="dept-alert error"></div>

                <div class="dept-form-grid">

                    <div class="dept-field">
                        <label>Department Code *</label>

                        <input id="department-code"
                               name="code"
                               maxlength="16"
                               placeholder="e.g. ENG">

                        <small id="error-code" class="dept-error"></small>
                    </div>

                    <div class="dept-field">
                        <label>Department Name *</label>

                        <input id="department-name"
                               name="name"
                               maxlength="100"
                               placeholder="e.g. Engineering">

                        <small id="error-name" class="dept-error"></small>
                    </div>

                    <div class="dept-field">
                        <label>Location *</label>

                        <input id="department-location"
                               name="location"
                               maxlength="100"
                               placeholder="e.g. Pune">

                        <small id="error-location" class="dept-error"></small>
                    </div>

                    <div class="dept-field">
                        <label>Department Email *</label>

                        <input id="department-email"
                               type="email"
                               name="email"
                               maxlength="190"
                               placeholder="engineering@company.com">

                        <small id="error-email" class="dept-error"></small>
                    </div>

                    <div class="dept-field">
                        <label>Contact Number *</label>

                        <input id="department-contact"
                               name="contact_no"
                               maxlength="20"
                               placeholder="+91 9876543210">

                        <small id="error-contact_no" class="dept-error"></small>
                    </div>

                    {{-- SEARCHABLE HEAD --}}
                    <div class="dept-field">
                        <label>Department Head *</label>

                        <div class="employee-picker">

                            <div class="employee-search-wrap">

                                <input type="text"
                                       id="employee-search"
                                       class="employee-search"
                                       autocomplete="off"
                                       placeholder="Search employee by name or ID">

                                <span class="employee-search-icon">⌕</span>

                            </div>

                            <div id="employee-results"
                                 class="employee-results">

                                @foreach ($employees as $employee)

                                    @php
                                        $employeeName = trim(
                                            $employee->first_name . ' ' .
                                            $employee->last_name
                                        );

                                        $employeeInitials = strtoupper(
                                            substr($employee->first_name,0,1) .
                                            substr($employee->last_name,0,1)
                                        );
                                    @endphp

                                    <button type="button"
                                            class="employee-result"
                                            data-id="{{ $employee->id }}"
                                            data-name="{{ $employeeName }}"
                                            data-code="{{ $employee->employee_code }}"
                                            data-search="{{ strtolower($employeeName.' '.$employee->employee_code) }}">

                                        <span class="employee-result-avatar">
                                            {{ $employeeInitials }}
                                        </span>

                                        <span>
                                            <strong>{{ $employeeName }}</strong>
                                            <span>{{ $employee->employee_code }}</span>
                                        </span>

                                    </button>

                                @endforeach

                            </div>

                            <div id="employee-selected"
                                 class="employee-selected">

                                <div class="employee-selected-info">
                                    <strong id="selected-employee-name"></strong>
                                    <span id="selected-employee-code"></span>
                                </div>

                                <button type="button"
                                        class="employee-remove"
                                        onclick="clearEmployee()">
                                    &times;
                                </button>

                            </div>

                            <input type="hidden"
                                   id="department-head"
                                   name="head_employee_id">

                        </div>

                        <small id="error-head_employee_id"
                               class="dept-error"></small>
                    </div>

                </div>

            </div>

            <div class="dept-modal-foot">

                <button type="button"
                        class="dept-btn dept-btn-light"
                        onclick="closeDepartmentModal()">
                    Cancel
                </button>

                <button type="submit"
                        class="dept-btn dept-btn-primary"
                        id="department-save">
                    Save Department
                </button>

            </div>

        </form>

    </div>
</div>


{{-- VIEW MODAL --}}
<div class="dept-modal-backdrop" id="view-department-modal">

    <div class="dept-modal view-modal">

        <div class="dept-modal-head">
            <h3>Department Details</h3>

            <button type="button"
                    class="dept-modal-close"
                    onclick="closeViewModal()">
                &times;
            </button>
        </div>

        <div class="view-body">

            <div class="view-title">

                <div class="view-avatar"
                     id="view-initials">
                    --
                </div>

                <div>
                    <h4 id="view-name">Department</h4>
                    <p id="view-code">Department code</p>
                </div>

            </div>

            <div class="view-grid">

                <div class="view-item">
                    <label>Location</label>
                    <strong id="view-location">-</strong>
                </div>

                <div class="view-item">
                    <label>Contact</label>
                    <strong id="view-contact">-</strong>
                </div>

                <div class="view-item">
                    <label>Email</label>
                    <strong id="view-email">-</strong>
                </div>

                <div class="view-item">
                    <label>Status</label>
                    <strong id="view-status" class="view-status">
                        <i></i>
                        Active
                    </strong>
                </div>

                <div class="view-item"
                     style="grid-column:1/-1">

                    <label>Department Head</label>

                    <strong id="view-head">
                        Not assigned
                    </strong>

                </div>

            </div>

        </div>

        <div class="dept-modal-foot">

            <button type="button"
                    class="dept-btn dept-btn-light"
                    onclick="closeViewModal()">
                Close
            </button>

        </div>

    </div>
</div>


<script>
(function () {

    var modal = document.getElementById('department-modal');
    var viewModal = document.getElementById('view-department-modal');
    var form = document.getElementById('department-form');

    var currentId = null;

    var createUrl = "{{ route('portal.data.departments.store') }}";
    var showUrl = "{{ route('portal.data.departments.show', '__department__') }}";
    var updateUrl = "{{ route('portal.data.departments.update', '__department__') }}";
    var statusUrl = "{{ route('portal.data.departments.status', '__department__') }}";

    var csrf = "{{ csrf_token() }}";


    /* =========================
       ADD DEPARTMENT
    ========================= */

    window.openDepartmentModal = function () {

        currentId = null;

        form.reset();
        clearErrors();
        clearEmployee();

        document.getElementById('department-modal-title').innerText =
            'Add Department';

        document.getElementById('department-save').innerText =
            'Save Department';

        modal.classList.add('show');
    };


    window.closeDepartmentModal = function () {

        modal.classList.remove('show');
        clearEmployee();
        clearErrors();
    };


    /* =========================
       EMPLOYEE SEARCH
    ========================= */

    var employeeSearch =
        document.getElementById('employee-search');

    var employeeResults =
        document.getElementById('employee-results');

    var employeeSelected =
        document.getElementById('employee-selected');

    var employeeButtons =
        document.querySelectorAll('.employee-result');


    employeeSearch.addEventListener('focus', function () {

        employeeResults.classList.add('show');

        filterEmployees(this.value);
    });


    employeeSearch.addEventListener('input', function () {

        filterEmployees(this.value);
        employeeResults.classList.add('show');
    });


    function filterEmployees(value) {

        var search = value.toLowerCase().trim();
        var found = 0;

        for (var i = 0; i < employeeButtons.length; i++) {

            var button = employeeButtons[i];

            var text =
                button.getAttribute('data-search') || '';

            var match =
                !search || text.indexOf(search) !== -1;

            button.style.display = match ? 'flex' : 'none';

            if (match) {
                found++;
            }
        }

        if (!found) {

            employeeResults.innerHTML =
                '<div class="employee-no-result">' +
                'No employee found' +
                '</div>';

        } else if (
            employeeResults.querySelector('.employee-no-result')
        ) {

            employeeResults.innerHTML = '';

            for (var j = 0; j < employeeButtons.length; j++) {
                employeeResults.appendChild(employeeButtons[j]);
            }
        }
    }


    for (var i = 0; i < employeeButtons.length; i++) {

        employeeButtons[i].addEventListener('click', function () {

            selectEmployee(
                this.getAttribute('data-id'),
                this.getAttribute('data-name'),
                this.getAttribute('data-code')
            );

        });
    }


    function selectEmployee(id, name, code) {

        document.getElementById('department-head').value = id;

        document.getElementById('selected-employee-name').innerText =
            name;

        document.getElementById('selected-employee-code').innerText =
            code;

        employeeSelected.classList.add('show');

        employeeSearch.value = name;

        employeeResults.classList.remove('show');
    }


    window.clearEmployee = function () {

        document.getElementById('department-head').value = '';

        document.getElementById('selected-employee-name').innerText = '';
        document.getElementById('selected-employee-code').innerText = '';

        employeeSelected.classList.remove('show');

        employeeSearch.value = '';

        employeeResults.classList.remove('show');
    };


    document.addEventListener('click', function (event) {

        var picker = document.querySelector('.employee-picker');

        if (picker && !picker.contains(event.target)) {
            employeeResults.classList.remove('show');
        }
    });


    /* =========================
       EDIT
    ========================= */

    window.editDepartment = function (id) {

        currentId = id;

        clearErrors();

        fetch(showUrl.replace('__department__', id), {
            headers: {
                'Accept': 'application/json'
            }
        })
        .then(function (response) {
            return response.json();
        })
        .then(function (result) {

            var d = result.data;
            var head = d.head_employee;

            document.getElementById('department-code').value =
                d.code || '';

            document.getElementById('department-name').value =
                d.name || '';

            document.getElementById('department-location').value =
                d.location || '';

            document.getElementById('department-email').value =
                d.email || '';

            document.getElementById('department-contact').value =
                d.contact_no || '';

            clearEmployee();

            if (head) {

                var name =
                    (head.first_name || '') + ' ' +
                    (head.last_name || '');

                selectEmployee(
                    head.id,
                    name.trim(),
                    head.employee_code || ''
                );
            }

            document.getElementById('department-modal-title').innerText =
                'Edit Department';

            document.getElementById('department-save').innerText =
                'Update Department';

            modal.classList.add('show');
        })
        .catch(function () {
            showAlert('Unable to load department.', 'error');
        });
    };


    /* =========================
       VIEW POPUP
    ========================= */

    window.viewDepartment = function (id) {

        fetch(showUrl.replace('__department__', id), {
            headers: {
                'Accept': 'application/json'
            }
        })
        .then(function (response) {
            return response.json();
        })
        .then(function (result) {

            var d = result.data;
            var head = d.head_employee;

            var name = d.name || 'Department';
            var code = d.code || '-';

            document.getElementById('view-name').innerText =
                name;

            document.getElementById('view-code').innerText =
                code;

            document.getElementById('view-location').innerText =
                d.location || '-';

            document.getElementById('view-contact').innerText =
                d.contact_no || '-';

            document.getElementById('view-email').innerText =
                d.email || '-';

            document.getElementById('view-head').innerText =
                head
                    ? (head.first_name + ' ' + head.last_name +
                       (head.employee_code
                           ? ' · ' + head.employee_code
                           : ''))
                    : 'Not assigned';

            var initials = '--';

            if (head) {
                initials = (
                    (head.first_name || '').charAt(0) +
                    (head.last_name || '').charAt(0)
                ).toUpperCase();
            }

            document.getElementById('view-initials').innerText =
                initials;

            var status =
                document.getElementById('view-status');

            status.className =
                'view-status' +
                (d.is_active ? '' : ' off');

            status.innerHTML =
                '<i></i>' +
                (d.is_active ? 'Active' : 'Inactive');

            viewModal.classList.add('show');
        })
        .catch(function () {
            showAlert('Unable to load department.', 'error');
        });
    };


    window.closeViewModal = function () {
        viewModal.classList.remove('show');
    };


    /* =========================
       STATUS
    ========================= */

    window.toggleDepartment = function (id, status) {

        var message = status
            ? 'Activate this department?'
            : 'Deactivate this department?';

        if (!confirm(message)) {
            return;
        }

        fetch(statusUrl.replace('__department__', id), {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf
            },
            body: JSON.stringify({
                is_active: status
            })
        })
        .then(function (response) {
            return response.json();
        })
        .then(function (result) {

            showAlert(
                result.message ||
                'Department status updated.',
                'success'
            );

            setTimeout(function () {
                window.location.reload();
            }, 600);
        })
        .catch(function () {
            showAlert(
                'Unable to update department status.',
                'error'
            );
        });
    };


    /* =========================
       SAVE
    ========================= */

    form.addEventListener('submit', function (event) {

        event.preventDefault();

        clearErrors();

        var data = {
            code:
                document.getElementById('department-code').value.trim(),

            name:
                document.getElementById('department-name').value.trim(),

            location:
                document.getElementById('department-location').value.trim(),

            email:
                document.getElementById('department-email').value.trim(),

            contact_no:
                document.getElementById('department-contact').value.trim(),

            head_employee_id:
                document.getElementById('department-head').value
        };

        var url = currentId
            ? updateUrl.replace('__department__', currentId)
            : createUrl;

        var method = currentId ? 'PUT' : 'POST';

        var button =
            document.getElementById('department-save');

        button.disabled = true;
        button.innerText = 'Saving...';

        fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf
            },
            body: JSON.stringify(data)
        })
        .then(function (response) {

            return response.json().then(function (json) {
                return {
                    status: response.status,
                    data: json
                };
            });
        })
        .then(function (result) {

            button.disabled = false;

            button.innerText =
                currentId
                    ? 'Update Department'
                    : 'Save Department';

            if (result.status >= 400) {

                if (result.data.errors) {
                    showValidationErrors(
                        result.data.errors
                    );
                } else {
                    showFormError(
                        result.data.message ||
                        'Unable to save department.'
                    );
                }

                return;
            }

            closeDepartmentModal();

            showAlert(
                result.data.message ||
                'Department saved successfully.',
                'success'
            );

            setTimeout(function () {
                window.location.reload();
            }, 600);
        })
        .catch(function () {

            button.disabled = false;

            button.innerText =
                currentId
                    ? 'Update Department'
                    : 'Save Department';

            showFormError(
                'Something went wrong. Please try again.'
            );
        });
    });


    /* =========================
       TABLE SEARCH
    ========================= */

    document.getElementById('department-search')
        .addEventListener('input', function () {

            var search =
                this.value.toLowerCase().trim();

            var rows =
                document.querySelectorAll(
                    '[data-department-row]'
                );

            for (var i = 0; i < rows.length; i++) {

                var text =
                    rows[i].getAttribute('data-search') || '';

                rows[i].style.display =
                    !search ||
                    text.indexOf(search) !== -1
                        ? ''
                        : 'none';
            }
        });


    /* =========================
       HELPERS
    ========================= */

    function clearErrors() {

        var errors =
            document.querySelectorAll('.dept-error');

        for (var i = 0; i < errors.length; i++) {
            errors[i].innerText = '';
        }

        document.getElementById('form-error')
            .classList.remove('show');
    }


    function showValidationErrors(errors) {

        for (var field in errors) {

            if (!errors.hasOwnProperty(field)) {
                continue;
            }

            var element =
                document.getElementById('error-' + field);

            if (element) {
                element.innerText =
                    errors[field][0];
            }
        }
    }


    function showFormError(message) {

        var error =
            document.getElementById('form-error');

        error.innerText = message;
        error.classList.add('show');
    }


    function showAlert(message, type) {

        var alert =
            document.getElementById('dept-alert');

        alert.innerText = message;
        alert.className =
            'dept-alert show ' + type;

        setTimeout(function () {
            alert.classList.remove('show');
        }, 3500);
    }


    /* CLOSE MODALS WITH BACKDROP */

    modal.addEventListener('click', function (event) {

        if (event.target === modal) {
            closeDepartmentModal();
        }
    });

    viewModal.addEventListener('click', function (event) {

        if (event.target === viewModal) {
            closeViewModal();
        }
    });

})();
</script>

@endsection