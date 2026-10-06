@if (!empty($employees) && count($employees))
    <div class="table-scroll">
        <table class="{{ !empty($compact) ? 'compact-table' : '' }}">
            <thead><tr>
                @if (empty($compact))<th><button class="table-sort" type="button" data-directory-sort="employee_code">Employee ID</button></th>@endif
                <th><button class="table-sort" type="button" data-directory-sort="last_name">Team member</button></th><th>Department</th><th>Employee role</th><th>Employment type</th><th><button class="table-sort" type="button" data-directory-sort="employment_status">Employment status</button></th>@if (empty($compact))<th>Record status</th>@endif<th><button class="table-sort" type="button" data-directory-sort="joining_date">Joined</button></th>
                @if (!empty($actions))<th>ACTIONS</th>@endif
            </tr></thead>
            <tbody>
                @foreach ($employees as $employee)
                    <tr data-employee-row="{{ $employee['id'] }}" data-active="{{ !empty($employee['is_active']) ? '1' : '0' }}">
                        @if (empty($compact))<td><span class="employee-code">{{ $employee['employee_code'] }}</span></td>@endif
                        <td>
                            @if (!empty($canView))
                                <a class="employee-cell employee-profile-link" href="{{ route('portal.employees.show', $employee['id']) }}">
                            @else
                                <span class="employee-cell">
                            @endif
                                <span class="avatar">{{ collect([data_get($employee, 'first_name'), data_get($employee, 'last_name')])->filter()->map(function ($part) { return mb_substr($part, 0, 1); })->implode('') }}</span><span><strong>{{ $employee['full_name'] ?? $employee['first_name'] . ' ' . $employee['last_name'] }}</strong><small>{{ $employee['email'] }}</small></span>
                            @if (!empty($canView))
                                </a>
                            @else
                                </span>
                            @endif
                        </td>
                        <td>{{ data_get($employee, 'department.name', '—') }}</td>
                        <td>{{ data_get($employee, 'employee_role.name', data_get($employee, 'employeeRole.name', '—')) }}</td>
                        <td>{{ data_get($employee, 'employee_type.name', data_get($employee, 'employeeType.name', '—')) }}</td>
                        @php
                            $rawStatus = (string) ($employee['employment_status'] ?? 'unknown');
                            $statusKey = strtolower(str_replace([' ', '-'], '_', $rawStatus));
                            $positiveStatuses = ['active', 'approved', 'completed', 'present', 'processed', 'open', 'signed'];
                            $warningStatuses = ['pending', 'scheduled', 'in_progress', 'screening', 'applied', 'shortlisted', 'notice_period', 'on_leave', 'assigned', 'processing'];
                            $statusTone = in_array($statusKey, $positiveStatuses, true) ? 'active' : (in_array($statusKey, $warningStatuses, true) ? 'pending' : (in_array($statusKey, ['rejected', 'inactive', 'absent', 'closed'], true) ? 'negative' : 'neutral'));
                            $statusLabel = ucwords(str_replace('_', ' ', $rawStatus));
                        @endphp
                        <td><span class="status status-{{ $statusTone }}"><i></i>{{ $statusLabel }}</span></td>
                        @if (empty($compact))<td><span class="status status-{{ !empty($employee['is_active']) ? 'active' : 'negative' }}"><i></i>{{ !empty($employee['is_active']) ? 'Active' : 'Inactive' }}</span></td>@endif
                        <td>{{ \Carbon\Carbon::parse($employee['joining_date'])->format('d M Y') }}</td>
                        @if (!empty($actions))
                            <td class="row-actions">
                                @if (!empty($canView))
                                    <a class="icon-button" href="{{ route('portal.employees.show', $employee['id']) }}" aria-label="View full report for {{ $employee['full_name'] ?? 'employee' }}" title="View full employee report">
                                        <svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2.5 12S6 5.5 12 5.5 21.5 12 21.5 12 18 18.5 12 18.5 2.5 12 2.5 12z"/><circle cx="12" cy="12" r="2.8"/></svg>
                                    </a>
                                @endif
                                @if (!empty($canEdit))<a class="icon-button" href="{{ route('portal.employees.edit', $employee['id']) }}" aria-label="Edit {{ $employee['full_name'] ?? 'employee' }}" title="Edit employee">Edit</a>@endif
                                @if (!empty($canDelete))
                                    <button class="icon-button employee-status-toggle {{ !empty($employee['is_active']) ? 'icon-button-danger' : '' }}" type="button" data-status-url="{{ route('portal.data.employees.status', $employee['id']) }}" data-next-status="{{ !empty($employee['is_active']) ? 0 : 1 }}" data-employee-name="{{ $employee['full_name'] ?? '' }}" aria-label="{{ !empty($employee['is_active']) ? 'Deactivate' : 'Activate' }} {{ $employee['full_name'] ?? 'employee' }}" title="{{ !empty($employee['is_active']) ? 'Deactivate employee record' : 'Activate employee record' }}">{{ !empty($employee['is_active']) ? 'Deactivate' : 'Activate' }}</button>
                                @endif
                            </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    <div class="empty-state">
        <span class="empty-mark" aria-hidden="true">▧</span>
        <strong>No employees match</strong>
        <p>Try adjusting the search or filters, or add a teammate to your directory.</p>
    </div>
@endif
