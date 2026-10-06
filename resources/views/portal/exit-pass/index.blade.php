@extends('layouts.portal')

@section('title', 'Exit Passes')

@section('content')
<style @if (isset($nonce) && $nonce) nonce="{{ $nonce }}" @endif>
.ep-list{--green:#16834a;--green-dark:#12683a;--green-soft:#effaf3;--ink:#17211b;--text:#46534b;--muted:#748078;--line:#e1e9e3;max-width:1180px;margin:0 auto;padding:32px 24px 56px;color:var(--text)}
.ep-list *{box-sizing:border-box}
.ep-list-head{display:flex;justify-content:space-between;align-items:flex-end;gap:18px;margin-bottom:25px}
.ep-list-eyebrow{margin:0 0 7px;color:var(--green);font-size:12px;font-weight:800;letter-spacing:.12em;text-transform:uppercase}
.ep-list h1{margin:0;color:var(--ink);font-size:clamp(30px,4vw,40px);line-height:1.1;font-weight:800;letter-spacing:-.035em}
.ep-list-sub{margin:9px 0 0;color:var(--muted);font-size:15px}
.ep-list-action{display:inline-flex;align-items:center;justify-content:center;gap:8px;min-height:45px;padding:0 17px;border:1px solid var(--green);border-radius:12px;background:var(--green);color:#fff;font-size:14px;font-weight:750;text-decoration:none;box-shadow:0 7px 17px rgba(22,131,74,.18);transition:.18s}
.ep-list-action:hover{background:var(--green-dark);border-color:var(--green-dark);color:#fff;text-decoration:none}
.ep-list-action svg{width:17px;height:17px;fill:none;stroke:currentColor;stroke-width:2;stroke-linecap:round;stroke-linejoin:round}
.ep-flash{margin-bottom:18px;padding:13px 16px;border:1px solid #b9e5c7;border-radius:12px;background:#f0fbf3;color:#17653a;font-size:14px}
.ep-list-items{display:grid;gap:16px}
.ep-pass{overflow:hidden;border:1px solid var(--line);border-radius:18px;background:#fff;box-shadow:0 10px 28px rgba(23,50,32,.055)}
.ep-pass-top{display:flex;align-items:center;justify-content:space-between;gap:16px;padding:16px 20px;border-bottom:1px solid var(--line);background:linear-gradient(105deg,#fbfefc,#f3faf5)}
.ep-pass-number{display:flex;align-items:center;gap:11px;min-width:0}
.ep-pass-mark{display:grid;width:38px;height:38px;flex:0 0 38px;place-items:center;border:1px solid #d5eadb;border-radius:12px;background:#eaf7ee;color:var(--green)}
.ep-pass-mark svg{width:19px;height:19px;fill:none;stroke:currentColor;stroke-width:1.8;stroke-linecap:round;stroke-linejoin:round}
.ep-pass-number small,.ep-pass-number strong{display:block}
.ep-pass-number small{margin-bottom:2px;color:var(--muted);font-size:11px;font-weight:750;letter-spacing:.08em;text-transform:uppercase}
.ep-pass-number strong{overflow:hidden;color:var(--ink);font-size:15px;font-weight:800;text-overflow:ellipsis;white-space:nowrap}
.ep-pass-body{display:grid;grid-template-columns:minmax(235px,.82fr) minmax(0,1.6fr)}
.ep-employee-card{position:relative;display:flex;align-items:center;gap:13px;min-width:0;margin:18px;padding:16px;border:1px solid #d9e7dd;border-radius:15px;background:linear-gradient(145deg,#f7fcf8,#edf7f0)}
.ep-employee-card:before{position:absolute;top:12px;right:13px;color:#74a986;font-size:9px;font-weight:800;letter-spacing:.12em;content:"EMPLOYEE"}
.ep-employee-avatar{display:grid;width:60px;height:60px;flex:0 0 60px;place-items:center;overflow:hidden;border:2px solid #fff;border-radius:17px;background:linear-gradient(145deg,#bde5c8,#e7f6eb);color:#17653a;font-size:17px;font-weight:850;box-shadow:0 3px 10px rgba(30,93,52,.12)}
.ep-employee-avatar img{display:block;width:100%;height:100%;object-fit:cover}
.ep-employee-info{min-width:0;padding-right:42px}
.ep-employee-info strong,.ep-employee-info span{display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.ep-employee-info strong{color:var(--ink);font-size:15px;font-weight:800}
.ep-employee-info span{margin-top:4px;color:var(--muted);font-size:12px}
.ep-employee-meta{display:grid;grid-template-columns:1fr 1fr;gap:12px 18px;align-content:center;padding:20px 22px}
.ep-meta-item{min-width:0}
.ep-meta-item small{display:block;margin-bottom:4px;color:var(--muted);font-size:11px;font-weight:750;letter-spacing:.06em;text-transform:uppercase}
.ep-meta-item strong{display:block;overflow-wrap:anywhere;color:var(--ink);font-size:13px;font-weight:700;line-height:1.45}
.ep-reason{grid-column:1/-1;padding-top:11px;border-top:1px dashed var(--line)}
.ep-review{display:flex;align-items:center;gap:8px;padding:0 20px 18px}
.ep-review button{min-height:35px;padding:0 12px;border:1px solid #b9e5c7;border-radius:9px;background:#f0fbf3;color:#17653a;font:inherit;font-size:12px;font-weight:750;cursor:pointer}
.ep-review button.reject{border-color:#f2c6c2;background:#fff5f4;color:#a12b22}
.ep-review button:hover{filter:brightness(.97)}
.ep-pass-status{display:inline-flex;align-items:center;gap:7px;padding:6px 10px;border:1px solid #fed7aa;border-radius:99px;background:#fff7ed;color:#b54708;font-size:12px;font-weight:750;white-space:nowrap}
.ep-pass-status:before{width:7px;height:7px;border-radius:50%;background:#f79009;content:""}
.ep-pass-status.is-approved,.ep-pass-status.is-completed{border-color:#b9e5c7;background:#f0fbf3;color:#17653a}
.ep-pass-status.is-approved:before,.ep-pass-status.is-completed:before{background:#22a05a}
.ep-pass-status.is-rejected{border-color:#f2c6c2;background:#fff5f4;color:#a12b22}
.ep-pass-status.is-rejected:before{background:#d4473e}
.ep-empty{padding:52px 20px;border:1px dashed #cbd9ce;border-radius:18px;background:#fbfdfb;text-align:center}
.ep-empty-icon{display:grid;width:54px;height:54px;margin:0 auto 14px;place-items:center;border-radius:17px;background:#eaf7ee;color:var(--green)}
.ep-empty-icon svg{width:25px;height:25px;fill:none;stroke:currentColor;stroke-width:1.8;stroke-linecap:round;stroke-linejoin:round}
.ep-empty h2{margin:0;color:var(--ink);font-size:18px;font-weight:800}
.ep-empty p{margin:7px 0 18px;color:var(--muted);font-size:14px}
.ep-pagination{margin-top:20px}
.ep-pagination nav{display:flex;justify-content:center}
@media(max-width:720px){.ep-list{padding:24px 15px 40px}.ep-pass-body{grid-template-columns:1fr}.ep-employee-card{margin:14px 14px 0}.ep-employee-meta{padding:18px}.ep-list-head{align-items:flex-start;flex-direction:column}.ep-list-action{width:100%}}
@media(max-width:420px){.ep-pass-top{padding:13px}.ep-employee-meta{grid-template-columns:1fr 1fr;gap:11px}.ep-meta-item strong{font-size:12px}}
@media print{.ep-list{max-width:none;padding:0}.ep-list-action,.ep-pagination{display:none!important}.ep-pass{break-inside:avoid;box-shadow:none}}
</style>

<main class="ep-list">
    <header class="ep-list-head">
        <div>
            <p class="ep-list-eyebrow">Employee services</p>
            <h1>{{ $canViewEmployees ? 'Exit pass requests' : 'My exit passes' }}</h1>
            <p class="ep-list-sub">{{ $canViewEmployees ? 'Review exit pass requests and employee details.' : 'View your submitted requests and their approval status.' }}</p>
        </div>
        @if ($canCreate)
            <a class="ep-list-action" href="{{ route('portal.exit-pass.create') }}">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                Apply for exit pass
            </a>
        @endif
    </header>

    @if (session('status'))
        <div class="ep-flash" role="status">{{ session('status') }}</div>
    @endif

    @if ($exitPasses->count())
        <section class="ep-list-items" aria-label="Exit pass requests">
            @foreach ($exitPasses as $exitPass)
                @php
                    $employee = $exitPass->employee;
                    $employeeName = $employee ? trim($employee->first_name . ' ' . $employee->last_name) : 'Employee record unavailable';
                    $initials = $employee ? collect([$employee->first_name, $employee->last_name])->filter()->map(function ($part) { return mb_substr($part, 0, 1); })->implode('') : '—';
                    $profilePhoto = $employee ? $employee->documents->first(function ($document) {
                        return in_array(strtolower(trim($document->title)), ['photo', 'employee photo', 'photograph', 'profile photo'], true)
                            && in_array(strtolower($document->mime_type), ['image/jpeg', 'image/png'], true);
                    }) : null;
                    $statusClass = strtolower($exitPass->status);
                    $passNumber = 'EP-' . str_pad($exitPass->id, 5, '0', STR_PAD_LEFT);
                @endphp
                <article class="ep-pass">
                    <header class="ep-pass-top">
                        <div class="ep-pass-number">
                            <span class="ep-pass-mark"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="3" width="16" height="18" rx="2"/><path d="M8 7h8M8 11h5M8 16h.01M12 16h.01M16 16h.01"/></svg></span>
                            <span><small>Exit pass ID</small><strong>{{ $passNumber }}</strong></span>
                        </div>
                        <span class="ep-pass-status is-{{ $statusClass }}">{{ $exitPass->status }}</span>
                    </header>

                    <div class="ep-pass-body">
                        <section class="ep-employee-card" aria-label="Employee ID card">
                            <span class="ep-employee-avatar">
                                @if ($profilePhoto)
                                    <img src="{{ route('portal.employee-documents.preview', $profilePhoto) }}" alt="Photo of {{ $employeeName }}" loading="lazy">
                                @else
                                    {{ mb_strtoupper($initials) }}
                                @endif
                            </span>
                            <div class="ep-employee-info">
                                <strong>{{ $employeeName }}</strong>
                                <span>{{ $employee ? ($employee->employee_code ?: 'Employee ID not assigned') : 'Employee' }}</span>
                                <span>{{ data_get($employee, 'department.name', 'Department not assigned') }}</span>
                            </div>
                        </section>

                        <div class="ep-employee-meta">
                            <div class="ep-meta-item">
                                <small>Exit date</small>
                                <strong>{{ $exitPass->exit_date ? $exitPass->exit_date->format('d M Y') : '—' }}</strong>
                            </div>
                            <div class="ep-meta-item">
                                <small>Leaving time</small>
                                <strong>{{ $exitPass->exit_time ? \Carbon\Carbon::parse($exitPass->exit_time)->format('g:i A') : '—' }}</strong>
                            </div>
                            <div class="ep-meta-item">
                                <small>Expected return</small>
                                <strong>{{ $exitPass->expected_return_time ? \Carbon\Carbon::parse($exitPass->expected_return_time)->format('g:i A') : 'Not specified' }}</strong>
                            </div>
                            <div class="ep-meta-item">
                                <small>Destination</small>
                                <strong>{{ $exitPass->destination ?: 'Not specified' }}</strong>
                            </div>
                            <div class="ep-meta-item ep-reason">
                                <small>Reason</small>
                                <strong>{{ $exitPass->reason }}</strong>
                            </div>
                        </div>
                    </div>
                    @if ($canReviewExitPasses && $exitPass->status === 'Pending')
                        @if (auth()->user()->isSuperAdmin() || (int) auth()->user()->employee_id !== (int) $exitPass->employee_id)
                            <form class="ep-review" method="POST" action="{{ route('portal.exit-pass.status', $exitPass->id) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" name="status" value="Approved">Approve request</button>
                                <button class="reject" type="submit" name="status" value="Rejected">Reject request</button>
                            </form>
                        @endif
                    @endif
                </article>
            @endforeach
        </section>

        @if ($exitPasses->hasPages())
            <div class="ep-pagination">{{ $exitPasses->links() }}</div>
        @endif
    @else
        <section class="ep-empty">
            <span class="ep-empty-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="3" width="16" height="18" rx="2"/><path d="M8 7h8M8 11h5M8 16h.01M12 16h.01M16 16h.01"/></svg></span>
            <h2>No exit pass requests yet</h2>
            <p>Your submitted exit pass requests will appear here.</p>
            @if ($canCreate)
                <a class="ep-list-action" href="{{ route('portal.exit-pass.create') }}">Apply for exit pass</a>
            @endif
        </section>
    @endif
</main>
@endsection
