@php
    $user = auth()->user();

    $roleKey = $user->isSuperAdmin() ? 'super-admin' : $user->role;
    $roleLabel = $user->isSuperAdmin()
        ? 'Super Admin'
        : ($user->role === 'hr'
            ? 'Human Resources'
            : ($user->role === 'employee'
                ? 'Employee'
                : ucwords(str_replace('_', ' ', $user->role))));

    $initials = collect(explode(' ', trim($user->name)))
        ->filter()
        ->map(function ($p) {
            return mb_strtoupper(mb_substr($p, 0, 1));
        })
        ->take(2)
        ->implode('');

    $icons = [
        'dashboard' => '<path d="M4 11.5L12 4l8 7.5M6 10.5V20h12v-9.5M10 20v-5h4v5"/>',
        'profile' => '<circle cx="12" cy="8" r="3.6"/><path d="M5 20c.8-3.8 3.6-6 7-6s6.2 2.2 7 6"/>',
        'people' => '<circle cx="9" cy="8" r="3.2"/><path d="M3 19c.6-3.2 3-5 6-5s5.4 1.8 6 5M16 5.2a3 3 0 010 5.6M18 14.3c1.7.6 2.7 2.2 3 4.7"/>',
        'departments' => '<rect x="4" y="4" width="7" height="7" rx="2"/><rect x="13" y="4" width="7" height="7" rx="2"/><rect x="4" y="13" width="7" height="7" rx="2"/><rect x="13" y="13" width="7" height="7" rx="2"/>',
        'holidays' => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 10h18M8 14h.01M12 14h.01M16 14h.01M8 17h.01M12 17h.01"/>',
        'shield' => '<path d="M12 3l7 3v5c0 4.5-3 8-7 10-4-2-7-5.5-7-10V6l7-3z"/><path d="M9.5 12l2 2 3.5-4"/>',
        'system-users' => '<circle cx="9" cy="8" r="3.2"/><path d="M3 19c.6-3.2 3-5 6-5s5.4 1.8 6 5M16 8h5M18.5 5.5v5"/>',
        'types' => '<path d="M4 7l8-4 8 4-8 4-8-4zM4 12l8 4 8-4M4 17l8 4 8-4"/>',
        'roles' => '<rect x="3.5" y="7" width="17" height="12" rx="3"/><path d="M9 7V5.5A1.5 1.5 0 0110.5 4h3A1.5 1.5 0 0115 5.5V7M3.5 12.5h17"/>',
    ];

    $navigation = [
        'dashboard' => ['portal.dashboard', null, 'dashboard'],
        'employee_profile' => ['portal.employees.show', 'employee', 'profile'],
        'employees' => ['portal.employees.index', null, 'people'],
        'departments' => ['portal.departments.index', null, 'departments'],
        'holidays' => ['portal.holidays.index', null, 'holidays'],
        'employee_types' => ['portal.employee-types.index', null, 'types'],
        'employee_roles' => ['portal.employee-roles.index', null, 'roles'],
    ];

    $permissions = array_values(array_filter(array_keys($navigation), function ($module) use ($user) {
        return $user->hasPermission($module, 'view');
    }));

    $groups = ['Modules' => []];
    $homeHref = route('portal.account.edit');

    foreach ($permissions as $module) {
        if (!isset($navigation[$module])) continue;

        [$route, $param, $icon] = $navigation[$module];

        if ($param === 'employee') {
            if (!$user->employee) continue;
            $href = route($route, $user->employee->id);
        } else {
            $href = route($route);
        }

        if ($groups['Modules'] === []) {
            $homeHref = $href;
        }

        $groups['Modules'][] = [
            $module === 'employee_profile'
                ? 'My Profile'
                : ucwords(str_replace('_', ' ', $module)),
            $href,
            request()->routeIs($route),
            $icon
        ];
    }

    if ($user->isSuperAdmin()) {
        $groups['Security'] = [
            [
                'System Users',
                route('portal.system-users.index'),
                request()->routeIs('portal.system-users.*'),
                'system-users'
            ],
            [
                'User Roles',
                route('portal.user-roles.index'),
                request()->routeIs('portal.user-roles.*'),
                'roles'
            ],
            [
                'Module Access',
                route('portal.module-access.index'),
                request()->routeIs('portal.module-access.*'),
                'shield'
            ],
        ];
    }

    $groups['Account'] = [
        [
            'My Account',
            route('portal.account.edit'),
            request()->routeIs('portal.account.*'),
            'profile'
        ],
    ];

    $groups = array_filter($groups);
@endphp

<div class="sidebar-scrim" data-menu-close></div>

<aside class="sidebar" id="portal-sidebar" aria-label="Main navigation">

    <div class="sidebar-header">
        <a href="{{ $homeHref }}" class="brand">
            <span class="brand-logo">HR</span>
            <span class="brand-text">
                <strong>HRMS</strong>
                <small>Human Resource Management</small>
            </span>
        </a>

        <button type="button" class="sidebar-close" data-menu-close aria-label="Close navigation">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path d="M6 6l12 12M18 6L6 18"/>
            </svg>
        </button>
    </div>

    <div class="workspace">
        <span class="workspace-logo">D</span>
        <span>
            <strong>Demo HRMS</strong>
            <small>People Workspace</small>
        </span>
        <i></i>
    </div>

    <div class="role">
        <i class="role-dot role-{{ $roleKey }}"></i>
        {{ $roleLabel }}
    </div>

    <nav class="side-nav">
        @foreach ($groups as $label => $items)
            <section class="nav-group">
                @if ($label !== 'Modules')
                    <div class="nav-label">{{ $label }}</div>
                @endif

                @foreach ($items as [$text, $href, $active, $icon])
                    <a href="{{ $href }}"
                       class="nav-link {{ $active ? 'active' : '' }}"
                       @if($active) aria-current="page" @endif>

                        <span class="nav-icon icon-{{ $icon }}">
                            <svg viewBox="0 0 24 24" fill="none"
                                 stroke="currentColor" stroke-width="1.8"
                                 stroke-linecap="round" stroke-linejoin="round">
                                {!! $icons[$icon] !!}
                            </svg>
                        </span>

                        <span>{{ $text }}</span>

                        @if($active)
                            <b></b>
                        @endif
                    </a>
                @endforeach
            </section>
        @endforeach
    </nav>

    <div class="sidebar-footer">
        <form method="POST" action="{{ route('logout') }}" id="logoutForm">
            @csrf

            <button type="submit" class="user-card">
                <span class="avatar">{{ $initials }}</span>

                <span class="user-info">
                    <strong>{{ $user->name }}</strong>
                    <small>{{ $roleLabel }}</small>
                </span>

                <span class="logout">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M10 4H6.5A2.5 2.5 0 004 6.5v11A2.5 2.5 0 006.5 20H10"/>
                        <path d="M15 8l4 4-4 4M19 12H9.5"/>
                    </svg>
                </span>
            </button>
        </form>
    </div>
</aside>

<style>
:root {
    --sb-width: 270px;
    --green: #15803d;
    --green-dark: #166534;
    --green-bg: #f0fdf4;
    --green-border: #dcfce7;
    --border: #e8e8e8;
    --text: #262626;
    --muted: #8a8a8a;
}

.sidebar {
    position: fixed;
    inset: 0 auto 0 0;
    z-index: 100;
    width: var(--sb-width);
    height: 100dvh;
    display: flex;
    flex-direction: column;
    padding: 16px 13px 12px;
    overflow: hidden;
    background: #fff !important;
    color: var(--text);
    border-right: 1px solid var(--border);
    font-family: Inter,system-ui,-apple-system,"Segoe UI",sans-serif;
    -webkit-font-smoothing: antialiased;
}

.sidebar::before,
.sidebar::after { display:none !important; }

.sidebar * { box-sizing:border-box; }
.sidebar a { color:inherit;text-decoration:none; }

.sidebar-header {
    display:flex;
    align-items:center;
    justify-content:space-between;
    padding:0 3px 13px;
    border-bottom:1px solid #eee;
}

.brand {
    display:flex;
    align-items:center;
    gap:10px;
    min-width:0;
}

.brand-logo {
    display:grid;
    place-items:center;
    width:38px;
    height:38px;
    border-radius:10px;
    color:#fff;
    background:var(--green);
    font-size:12px;
    font-weight:800;
    box-shadow:0 5px 14px rgba(21,128,61,.16);
}

.brand-text { min-width:0; }
.brand-text strong,
.brand-text small { display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis; }

.brand-text strong { font-size:15px;font-weight:800; }
.brand-text small { margin-top:2px;color:#999;font-size:9.5px; }

.sidebar-close {
    display:none;
    place-items:center;
    width:34px;
    height:34px;
    border:1px solid var(--border);
    border-radius:9px;
    background:#fff;
    color:#555;
}

.sidebar-close svg { width:17px;height:17px; }

.workspace {
    display:flex;
    align-items:center;
    gap:10px;
    margin-top:14px;
    padding:9px;
    border:1px solid var(--border);
    border-radius:11px;
    background:#fafafa;
}

.workspace-logo {
    display:grid;
    place-items:center;
    width:34px;
    height:34px;
    flex:none;
    border-radius:9px;
    color:var(--green-dark);
    background:var(--green-bg);
    border:1px solid var(--green-border);
    font-size:13px;
    font-weight:800;
}

.workspace span:not(.workspace-logo) {
    min-width:0;
    flex:1;
}

.workspace strong,
.workspace small {
    display:block;
    overflow:hidden;
    white-space:nowrap;
    text-overflow:ellipsis;
}

.workspace strong { font-size:12px; }
.workspace small { margin-top:1px;color:#999;font-size:10px; }

.workspace i {
    width:7px;
    height:7px;
    flex:none;
    border-radius:50%;
    background:#22c55e;
}

.role {
    display:inline-flex;
    align-items:center;
    gap:7px;
    align-self:flex-start;
    margin:11px 4px 3px;
    padding:5px 9px;
    border:1px solid var(--border);
    border-radius:999px;
    color:#666;
    background:#fff;
    font-size:10px;
    font-weight:600;
}

.role-dot {
    width:7px;
    height:7px;
    border-radius:50%;
    background:#22c55e;
}

.role-super-admin { background:var(--green); }
.role-hr { background:#2563eb; }
.role-employee { background:#16a34a; }

.side-nav {
    flex:1;
    min-height:0;
    margin-top:5px;
    overflow-y:auto;
    scrollbar-width:thin;
    scrollbar-color:#d5d5d5 transparent;
}

.nav-group + .nav-group { margin-top:13px; }

.nav-label {
    margin:11px 10px 5px;
    color:#a0a0a0;
    font-size:10px;
    font-weight:700;
    text-transform:uppercase;
    letter-spacing:.07em;
}

.nav-link {
    position:relative;
    display:flex;
    align-items:center;
    gap:10px;
    min-height:41px;
    margin-bottom:3px;
    padding:5px 9px;
    border:1px solid transparent;
    border-radius:9px;
    color:#555;
    font-size:13px;
    font-weight:500;
    transition:.18s ease;
}

.nav-link:hover {
    color:#262626;
    background:#fafafa;
    border-color:#f0f0f0;
}

.nav-icon {
    display:grid;
    place-items:center;
    width:31px;
    height:31px;
    flex:none;
    border-radius:8px;
}

.nav-icon svg { width:16px;height:16px; }

.icon-dashboard { color:#2563eb;background:#eff6ff; }
.icon-profile { color:#7c3aed;background:#f5f3ff; }
.icon-people { color:#ea580c;background:#fff7ed; }
.icon-departments { color:#15803d;background:#f0fdf4; }
.icon-types { color:#0891b2;background:#ecfeff; }
.icon-roles { color:#4f46e5;background:#eef2ff; }
.icon-shield { color:#059669;background:#ecfdf5; }
.icon-system-users { color:#7c3aed;background:#f5f3ff; }

.nav-link.active {
    color:var(--green-dark);
    background:var(--green-bg);
    border-color:var(--green-border);
    font-weight:700;
}

.nav-link.active .nav-icon {
    color:#fff;
    background:var(--green);
}

.nav-link b {
    width:4px;
    height:20px;
    margin-left:auto;
    border-radius:4px;
    background:var(--green);
}

.sidebar-footer {
    margin-top:9px;
    padding-top:10px;
    border-top:1px solid var(--border);
}

.user-card {
    display:flex;
    align-items:center;
    width:100%;
    gap:9px;
    padding:8px;
    border:1px solid var(--border);
    border-radius:11px;
    background:#fff;
    color:#262626;
    text-align:left;
    cursor:pointer;
}

.user-card:hover { background:#fafafa; }

.avatar {
    display:grid;
    place-items:center;
    width:36px;
    height:36px;
    flex:none;
    border-radius:9px;
    color:#fff;
    background:var(--green);
    font-size:11px;
    font-weight:800;
}

.user-info {
    min-width:0;
    flex:1;
}

.user-info strong,
.user-info small {
    display:block;
    overflow:hidden;
    white-space:nowrap;
    text-overflow:ellipsis;
}

.user-info strong { font-size:12px; }
.user-info small { margin-top:1px;color:#999;font-size:10px; }

.logout {
    display:grid;
    place-items:center;
    width:30px;
    height:30px;
    border-radius:8px;
    color:#aaa;
}

.logout svg { width:16px;height:16px; }

.user-card:hover .logout {
    color:var(--green);
    background:var(--green-bg);
}

.nav-link:focus-visible,
.user-card:focus-visible,
.sidebar-close:focus-visible {
    outline:2px solid #22c55e;
    outline-offset:2px;
}

.sidebar-scrim {
    position:fixed;
    inset:0;
    z-index:90;
    visibility:hidden;
    opacity:0;
    background:rgba(15,23,42,.18);
    backdrop-filter:blur(2px);
    transition:.25s ease;
}

@media (max-width:980px) {
    .sidebar {
        transform:translateX(-105%);
        box-shadow:20px 0 50px rgba(15,23,42,.12);
        transition:transform .3s cubic-bezier(.22,1,.36,1);
    }

    .sidebar-close { display:grid; }

    body.menu-open .sidebar { transform:translateX(0); }

    body.menu-open .sidebar-scrim {
        visibility:visible;
        opacity:1;
    }

    body.menu-open { overflow:hidden; }
}

@media (max-width:480px) {
    :root { --sb-width:min(290px,88vw); }
}

@media (prefers-reduced-motion:reduce) {
    .sidebar,
    .sidebar *,
    .sidebar-scrim { transition:none !important; }
}
</style>

<script>
(function () {
    'use strict';

    const body = document.body;
    const toggles = document.querySelectorAll('[data-menu-toggle]');
    const closeButtons = document.querySelectorAll('[data-menu-close]');

    function setMenu(open) {
        body.classList.toggle('menu-open', open);
        toggles.forEach(btn => btn.setAttribute('aria-expanded', String(open)));
    }

    toggles.forEach(btn => btn.addEventListener('click', () =>
        setMenu(!body.classList.contains('menu-open'))
    ));

    closeButtons.forEach(el =>
        el.addEventListener('click', () => setMenu(false))
    );

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') setMenu(false);
    });

    window.addEventListener('resize', () => {
        if (window.innerWidth > 980) setMenu(false);
    });

    document.querySelectorAll('.sidebar .nav-link').forEach(link => {
        link.addEventListener('click', () => {
            if (window.innerWidth <= 980) setMenu(false);
        });
    });

    const form = document.getElementById('logoutForm');
    let confirmed = false;

    if (form) {
        form.addEventListener('submit', e => {
            if (confirmed || typeof Swal === 'undefined') return;

            e.preventDefault();

            Swal.fire({
                icon: 'question',
                title: 'Sign out?',
                text: 'You will need to sign in again to continue.',
                showCancelButton: true,
                confirmButtonText: 'Sign out',
                cancelButtonText: 'Cancel',
                reverseButtons: true,
                confirmButtonColor: '#15803d'
            }).then(result => {
                if (result.isConfirmed) {
                    confirmed = true;
                    form.submit();
                }
            });
        });
    }
})();
</script>
