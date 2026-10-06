@php
    $fullName = data_get($employee, 'full_name', $employee->first_name . ' ' . $employee->last_name);
    $initials = collect([$employee->first_name, $employee->last_name])->map(function ($part) { return mb_substr($part, 0, 1); })->implode('');
    $rawStatus = (string) ($employee->employment_status ?? 'unknown');
    $statusKey = strtolower(str_replace([' ', '-'], '_', $rawStatus));
    $positiveStatuses = ['active', 'approved', 'completed', 'present', 'processed', 'open', 'signed'];
    $warningStatuses = ['pending', 'scheduled', 'in_progress', 'screening', 'applied', 'shortlisted', 'notice_period', 'on_leave', 'assigned', 'processing'];
    $statusTone = in_array($statusKey, $positiveStatuses, true) ? 'active' : (in_array($statusKey, $warningStatuses, true) ? 'pending' : (in_array($statusKey, ['rejected', 'inactive', 'absent', 'closed'], true) ? 'negative' : 'neutral'));
    $statusLabel = ucwords(str_replace('_', ' ', $rawStatus));
    $profilePhoto = $employee->documents->first(function ($document) {
        return in_array(strtolower(trim($document->title)), ['photo', 'employee photo', 'photograph', 'profile photo'], true)
            && in_array(strtolower($document->mime_type), ['image/jpeg', 'image/png'], true);
    });
    $documentTitles = $employee->documents->map(function ($document) { return strtolower(trim($document->title)); })->all();
    $formatFileSize = function ($bytes) {
        return $bytes >= 1048576 ? number_format($bytes / 1048576, 1) . ' MB' : number_format($bytes / 1024, 1) . ' KB';
    };
    $nonce = function_exists('csp_nonce') ? csp_nonce() : null;
@endphp

<style @if ($nonce) nonce="{{ $nonce }}" @endif>
    .employee-report { max-width: 1240px; margin: 0 auto; }
    .employee-report-toolbar { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px; margin: 0 0 18px; }
    .employee-report-actions { display: flex; flex-wrap: wrap; gap: 10px; }
    .employee-report-button { display: inline-flex; align-items: center; justify-content: center; gap: 8px; min-height: 40px; padding: 8px 14px; border: 1px solid #d1d5db; border-radius: 9px; color: #1f2937; background: #fff; font: 600 14px/1.2 inherit; font-family: inherit; text-decoration: none; cursor: pointer; }
    .employee-report-button:hover { background: #f9fafb; }
    .employee-report .profile-hero { margin-bottom: 18px; }
    .employee-report .profile-large-avatar img { display: block; width: 100%; height: 100%; border-radius: inherit; object-fit: cover; }
    .employee-report .profile-grid { margin-bottom: 18px; }
    .employee-report .detail-list dd { overflow-wrap: anywhere; }
    .employee-report .profile-panel { min-width: 0; }
    .employee-report .report-document-checklist { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 10px; margin-bottom: 18px; }
    .employee-report .report-document-check { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 12px 14px; border: 1px solid #e5e7eb; border-radius: 10px; background: #fff; }
    .employee-report .report-document-check strong { font-size: 13px; }
    .employee-report .report-document-check span { color: #6b7280; font-size: 12px; font-weight: 700; }
    .employee-report .report-document-check .is-uploaded { color: #15803d; }
    .employee-report .report-document-list { display: grid; gap: 10px; }
    .employee-report .report-document-row { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px; padding: 14px; border: 1px solid #e5e7eb; border-radius: 10px; color: inherit; text-decoration: none; }
    .employee-report .report-document-row:hover { border-color: #86efac; background: #f8fff9; }
    .employee-report .report-document-row strong, .employee-report .report-document-row small { display: block; }
    .employee-report .report-document-row small { margin-top: 4px; color: #6b7280; overflow-wrap: anywhere; }
    .employee-report .report-document-meta { color: #6b7280; font-size: 12px; }
    .employee-report .report-document-preview { margin: 0 0 10px; padding: 12px; border: 1px solid #e5e7eb; border-radius: 10px; background: #f9fafb; }
    .employee-report .report-document-preview img { display: block; max-width: min(100%, 420px); max-height: 420px; width: auto; height: auto; object-fit: contain; border-radius: 7px; }
    .employee-report .report-document-preview figcaption { margin-top: 8px; color: #6b7280; font-size: 12px; overflow-wrap: anywhere; }
    .employee-report .report-record-note { margin: 0 0 14px; color: #6b7280; font-size: 13px; }
    @media print {
        body { background: #fff !important; }
        .employee-report-toolbar, .back-link, .report-document-download { display: none !important; }
        .employee-report { max-width: none; }
        .employee-report .panel, .employee-report .profile-hero { break-inside: avoid; box-shadow: none !important; }
        .employee-report .profile-grid { grid-template-columns: 1fr 1fr; }
        .employee-report .report-document-row { color: #111827; }
    }
</style>

<main class="employee-report">
    <div class="employee-report-toolbar">
        <a class="back-link" href="{{ auth()->user()->hasPermission('employees', 'view') ? route('portal.employees.index') : route('portal.dashboard') }}">← {{ auth()->user()->hasPermission('employees', 'view') ? 'Back to employee directory' : 'Back to dashboard' }}</a>
        <div class="employee-report-actions">
            @if (auth()->user()->hasPermission('employees', 'edit'))
                <a class="employee-report-button" href="{{ route('portal.employees.edit', $employee) }}">Edit employee</a>
            @endif
            <button class="employee-report-button" id="employee-report-print" type="button">Print / Save as PDF</button>
        </div>
    </div>

    <section class="profile-hero panel">
        <span class="profile-large-avatar">
            @if ($profilePhoto)
                <img src="{{ route('portal.employee-documents.preview', $profilePhoto) }}" alt="Profile photo of {{ $fullName }}">
            @else
                {{ $initials }}
            @endif
        </span>
        <div class="profile-hero-copy">
            <p class="eyebrow">{{ $employee->employee_code }} · {{ data_get($employee, 'department.name', 'Demo HRMS') }}</p>
            <h1>{{ $fullName }}</h1>
            <p>{{ data_get($employee, 'employeeRole.name', 'Team member') }} <span>·</span> {{ $employee->city ?: '—' }}, {{ $employee->state ?: '—' }}</p>
        </div>
        <div class="profile-hero-status">
            <span class="status status-{{ $statusTone }}"><i></i>{{ $statusLabel }}</span>
            <span>Joined {{ $employee->joining_date ? $employee->joining_date->format('d M Y') : 'Not recorded' }}</span>
        </div>
    </section>

    <div class="profile-grid">
        <section class="panel profile-panel">
            <div class="panel-heading"><div><h2>Personal information</h2><p>Contact, identity and home address.</p></div></div>
            <dl class="detail-list">
                <div><dt>First name</dt><dd>{{ $employee->first_name }}</dd></div>
                <div><dt>Last name</dt><dd>{{ $employee->last_name }}</dd></div>
                <div><dt>Work email</dt><dd>{{ $employee->email }}</dd></div>
                <div><dt>Phone</dt><dd>{{ $employee->phone ?: 'Not provided' }}</dd></div>
                <div><dt>Date of birth</dt><dd>{{ $employee->date_of_birth ? $employee->date_of_birth->format('d M Y') : 'Not provided' }}</dd></div>
                <div><dt>Gender</dt><dd>{{ $employee->gender ? ucwords(str_replace('_', ' ', $employee->gender)) : 'Not provided' }}</dd></div>
                <div><dt>Address</dt><dd>{{ $employee->address_line ?: 'Not provided' }}</dd></div>
                <div><dt>City</dt><dd>{{ $employee->city ?: 'Not provided' }}</dd></div>
                <div><dt>State</dt><dd>{{ $employee->state ?: 'Not provided' }}</dd></div>
                <div><dt>Postal code</dt><dd>{{ $employee->postal_code ?: 'Not provided' }}</dd></div>
                <div><dt>Emergency contact</dt><dd>{{ $employee->emergency_contact_name ?: 'Not provided' }}</dd></div>
                <div><dt>Relationship</dt><dd>{{ $employee->emergency_contact_relationship ?: 'Not provided' }}</dd></div>
                <div><dt>Emergency phone</dt><dd>{{ $employee->emergency_contact_phone ?: 'Not provided' }}</dd></div>
            </dl>
        </section>

        <section class="panel profile-panel">
            <div class="panel-heading"><div><h2>Employment details</h2><p>Position, department and record status.</p></div></div>
            <dl class="detail-list">
                <div><dt>Employee ID</dt><dd>{{ $employee->employee_code }}</dd></div>
                <div><dt>Department</dt><dd>{{ data_get($employee, 'department.name', 'Not assigned') }}{{ data_get($employee, 'department.code') ? ' (' . data_get($employee, 'department.code') . ')' : '' }}</dd></div>
                <div><dt>Employee role</dt><dd>{{ data_get($employee, 'employeeRole.name', 'Not assigned') }}</dd></div>
                <div><dt>Employee type</dt><dd>{{ data_get($employee, 'employeeType.name', 'Not assigned') }}</dd></div>
                <div><dt>Manager</dt><dd>{{ data_get($employee, 'manager.full_name', 'Not assigned') }}{{ data_get($employee, 'manager.employee_code') ? ' · ' . data_get($employee, 'manager.employee_code') : '' }}</dd></div>
                <div><dt>Employment status</dt><dd><span class="status status-{{ $statusTone }}"><i></i>{{ $statusLabel }}</span></dd></div>
                <div><dt>Employee record</dt><dd><span class="status status-{{ $employee->is_active ? 'active' : 'negative' }}"><i></i>{{ $employee->is_active ? 'Active' : 'Inactive' }}</span></dd></div>
                <div><dt>Joining date</dt><dd>{{ $employee->joining_date ? $employee->joining_date->format('d M Y') : 'Not recorded' }}</dd></div>
                <div><dt>Record created</dt><dd>{{ $employee->created_at ? $employee->created_at->format('d M Y, h:i A') : 'Not recorded' }}</dd></div>
                <div><dt>Created by</dt><dd>{{ data_get($employee, 'createdBy.name', 'Not recorded') }}</dd></div>
                <div><dt>Last updated</dt><dd>{{ $employee->updated_at ? $employee->updated_at->format('d M Y, h:i A') : 'Not recorded' }}</dd></div>
            </dl>
        </section>
    </div>

    <div class="profile-grid">
        <section class="panel profile-panel">
            <div class="panel-heading"><div><h2>Bank details</h2><p>Confidential payment information for authorized users.</p></div></div>
            @if ($bankDetails)
                <dl class="detail-list">
                    <div><dt>Account holder</dt><dd>{{ $bankDetails['account_holder'] }}</dd></div>
                    <div><dt>Bank</dt><dd>{{ $bankDetails['bank_name'] }}</dd></div>
                    <div><dt>Account number</dt><dd>{{ $bankDetails['account_number'] }}</dd></div>
                    <div><dt>IFSC code</dt><dd>{{ $bankDetails['ifsc_code'] }}</dd></div>
                    <div><dt>Branch</dt><dd>{{ $bankDetails['branch'] ?: 'Not provided' }}</dd></div>
                    <div><dt>Account type</dt><dd>{{ $bankDetails['account_type'] ? ucwords($bankDetails['account_type']) : 'Not provided' }}</dd></div>
                </dl>
            @else
                <p class="onboarding-profile-empty">No bank details are on file.</p>
            @endif
        </section>

        <section class="panel profile-panel">
            <div class="panel-heading"><div><h2>Previous experience</h2><p>Employment history provided during onboarding.</p></div></div>
            @forelse ($employee->experiences as $experience)
                <div class="onboarding-profile-item">
                    <strong>{{ $experience->job_title }} · {{ $experience->company_name }}</strong>
                    <small>{{ $experience->start_date ? $experience->start_date->format('d M Y') : 'Start date not provided' }} – {{ $experience->end_date ? $experience->end_date->format('d M Y') : 'Present / not provided' }}</small>
                    @if ($experience->summary)<p>{{ $experience->summary }}</p>@endif
                </div>
            @empty
                <p class="onboarding-profile-empty">No previous experience added.</p>
            @endforelse
        </section>
    </div>

    <section class="panel profile-panel">
        <div class="panel-heading"><div><h2>Employee documents</h2><p>Required onboarding files and any additional documents stored for this employee.</p></div></div>
        <div class="report-document-checklist">
            @foreach (['Photo', 'Aadhaar Card', 'Bank Passbook'] as $requiredDocument)
                @php $isUploaded = in_array(strtolower($requiredDocument), $documentTitles, true); @endphp
                <div class="report-document-check">
                    <strong>{{ $requiredDocument }}</strong>
                    <span class="{{ $isUploaded ? 'is-uploaded' : '' }}">{{ $isUploaded ? 'Uploaded' : 'Not on file' }}</span>
                </div>
            @endforeach
        </div>
        @forelse ($employee->documents as $document)
            <div class="report-document-list">
                @if (in_array(strtolower($document->mime_type), ['image/jpeg', 'image/png'], true))
                    <figure class="report-document-preview">
                        <img src="{{ route('portal.employee-documents.preview', $document) }}" alt="{{ $document->title }}: {{ $document->original_name }}" loading="lazy">
                        <figcaption>{{ $document->title }} · {{ $document->original_name }}</figcaption>
                    </figure>
                @endif
                <div class="report-document-row">
                    <span>
                        <strong>{{ $document->title }}</strong>
                        <small>{{ $document->original_name }}</small>
                        <small class="report-document-meta">{{ strtoupper($document->mime_type) }} · {{ $formatFileSize($document->file_size) }} · Uploaded {{ $document->created_at->format('d M Y, h:i A') }}</small>
                    </span>
                    <a class="report-document-download" href="{{ route('portal.employee-documents.download', $document) }}">Download file ↓</a>
                </div>
            </div>
        @empty
            <p class="onboarding-profile-empty">No onboarding documents uploaded.</p>
        @endforelse
    </section>
</main>

<script @if ($nonce) nonce="{{ $nonce }}" @endif>
    document.getElementById('employee-report-print').addEventListener('click', function () {
        window.print();
    });
</script>
