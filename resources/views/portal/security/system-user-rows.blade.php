@forelse ($users as $systemUser)
    @php
        $userInitials = collect(explode(' ', trim($systemUser->name)))->filter()->map(function ($part) { return mb_strtoupper(mb_substr($part, 0, 1)); })->take(2)->implode('');
        $isCurrentUser = (int) auth()->id() === (int) $systemUser->id;
    @endphp
    <tr>
        <td>
            <div class="su-user">
                <span class="su-avatar">{{ $userInitials ?: '?' }}</span>
                <span><strong>{{ $systemUser->name }}</strong><small>User #{{ $systemUser->id }} · {{ $systemUser->email }}</small></span>
            </div>
        </td>
        <td><div class="su-cell"><strong>{{ data_get($systemUser, 'userRole.name') ? ucwords(str_replace('_', ' ', data_get($systemUser, 'userRole.name'))) : 'Unassigned' }}</strong><small>Role ID {{ $systemUser->role_id }} · {{ data_get($systemUser, 'userRole.is_active') ? 'Role enabled' : 'Role disabled' }}</small></div></td>
        <td>
            @if ($systemUser->employee)
                <div class="su-cell"><strong>{{ $systemUser->employee->full_name }}</strong><small>Employee #{{ $systemUser->employee_id }} · {{ $systemUser->employee->employee_code }}</small></div>
            @else
                <span>Not linked</span>
            @endif
        </td>
        <td>{{ $systemUser->email_verified_at ? $systemUser->email_verified_at->format('d M Y') : 'Not verified' }}</td>
        <td><span class="su-status {{ $systemUser->is_active ? '' : 'is-off' }}"><i></i>{{ $systemUser->is_active ? 'Active' : 'Inactive' }}</span></td>
        <td>{{ $systemUser->created_at ? $systemUser->created_at->format('d M Y') : '—' }}</td>
        <td>{{ $systemUser->updated_at ? $systemUser->updated_at->format('d M Y') : '—' }}</td>
        <td>
            <div class="su-actions">
                <button class="su-action" type="button" data-password-user="{{ $systemUser->id }}" data-password-name="{{ $systemUser->name }}">Change password</button>
                <form method="POST" action="{{ route('portal.system-users.status', $systemUser) }}">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="is_active" value="{{ $systemUser->is_active ? 0 : 1 }}">
                    @if ($isCurrentUser && $systemUser->is_active)
                        <button class="su-action" type="button" disabled title="You cannot deactivate your own account">Current account</button>
                    @else
                        <button class="su-action {{ $systemUser->is_active ? 'su-action-danger' : '' }}" type="submit">{{ $systemUser->is_active ? 'Deactivate' : 'Activate' }}</button>
                    @endif
                </form>
            </div>
        </td>
    </tr>
@empty
    <tr><td colspan="8" class="su-empty">No {{ $selectedStatus }} accounts match your search.</td></tr>
@endforelse
