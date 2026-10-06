@extends('layouts.portal')

@section('title', 'Apply for Leave')

@section('content')

<style>
:root{--g:#15803d;--gd:#166534;--gs:#f0fdf4;--gl:#bbf7d0;--ink:#111827;--tx:#374151;--mu:#6b7280;--ln:#d4d4d8;--ls:#e4e4e7;--hd:#fafafa;--rd:#dc2626}
.lv{max-width:1150px;margin:0 auto;padding:30px 24px 50px;color:var(--tx);font-family:inherit}
.lv *{box-sizing:border-box}
.ic{width:17px;height:17px;fill:none;stroke:currentColor;stroke-width:2;stroke-linecap:round;stroke-linejoin:round;flex-shrink:0}

/* header */
.lv-top{display:flex;justify-content:space-between;align-items:flex-end;gap:18px;flex-wrap:wrap;margin-bottom:24px}
.lv-eye{margin:0 0 6px;font-size:15px;font-weight:700;color:var(--g)}
.lv h1{margin:0;font-size:40px;line-height:1.1;font-weight:800;color:var(--ink);letter-spacing:-.02em}
.lv-sub{margin:9px 0 0;font-size:16px;color:var(--mu)}

/* buttons */
.btn{display:inline-flex;align-items:center;justify-content:center;gap:9px;min-height:46px;padding:0 22px;border-radius:13px;font:700 14px inherit;
    font-family:inherit;cursor:pointer;border:1.5px solid var(--ln);background:#fff;color:var(--tx);transition:.15s}
.btn:hover{background:var(--hd);border-color:#a1a1aa}
.btn-g{background:var(--g);border-color:var(--gd);color:#fff;box-shadow:0 8px 20px rgba(21,128,61,.25);min-width:210px}
.btn-g:hover{background:var(--gd)}
.btn-g:disabled{opacity:.7;cursor:not-allowed}
.spin{width:15px;height:15px;border:2px solid rgba(255,255,255,.4);border-top-color:#fff;border-radius:50%;animation:sp .7s linear infinite}
@keyframes sp{to{transform:rotate(360deg)}}

/* layout + cards */
.grid{display:grid;grid-template-columns:minmax(0,1.6fr) minmax(0,1fr);gap:22px;align-items:start}
.card{background:#fff;border:1.5px solid var(--ln);border-radius:20px;overflow:hidden}
.card-h{display:flex;align-items:center;gap:13px;padding:20px 24px;border-bottom:1.5px solid var(--ln);background:var(--hd)}
.tile{width:42px;height:42px;border-radius:12px;background:var(--gs);border:1.5px solid var(--gl);color:var(--g);display:flex;align-items:center;justify-content:center}
.card-h h2{margin:0;font-size:19px;font-weight:800;color:var(--ink)}
.card-h p{margin:3px 0 0;font-size:13px;color:var(--mu)}

/* form */
.form{padding:24px}
.fg{display:grid;grid-template-columns:1fr 1fr;gap:20px}
.full{grid-column:1/-1}
.fld label{display:block;margin-bottom:7px;font-size:13px;font-weight:700;color:var(--ink)}
.fld label b{color:var(--rd)}
.inp{position:relative}
.inp .ic{position:absolute;left:14px;top:14px;color:var(--mu);pointer-events:none}
.fc{display:block;width:100%;height:47px;padding:0 14px 0 42px;border:1.5px solid var(--ln);border-radius:12px;background:#fff;color:var(--ink);font:500 14px inherit;font-family:inherit;outline:0;transition:.15s}
.fc:hover{border-color:#a1a1aa}
.fc:focus{border-color:var(--g);box-shadow:0 0 0 4px var(--gs)}
textarea.fc{height:140px;padding:13px 14px;resize:vertical;line-height:1.55}
.help{display:flex;justify-content:space-between;margin-top:7px;font-size:12px;color:var(--mu)}
.acts{display:flex;justify-content:flex-end;gap:12px;margin-top:24px;padding-top:20px;border-top:1.5px solid var(--ln)}

/* summary table */
.tb{width:100%;border-collapse:collapse}
.tb th,.tb td{padding:14px 18px;text-align:left;font-size:14px;border-bottom:1.5px solid var(--ls)}
.tb th{width:42%;background:var(--hd);color:var(--mu);font-weight:700;border-right:1.5px solid var(--ls)}
.tb td{color:var(--ink);font-weight:700}
.tb tr:last-child th,.tb tr:last-child td{border-bottom:0}
.days{display:inline-block;min-width:34px;padding:3px 12px;border-radius:99px;background:var(--g);color:#fff;text-align:center;font-size:13px}
.pend{display:inline-flex;align-items:center;gap:7px;padding:5px 12px;border-radius:99px;background:#fff7ed;border:1.5px solid #fed7aa;color:#c2410c;font-size:12px}
.pend i{width:7px;height:7px;border-radius:50%;background:#f97316}
.note{margin:0;padding:15px 18px;border-top:1.5px solid var(--ln);background:var(--gs);font-size:13px;line-height:1.55;color:var(--gd)}

/* alerts */
.sw{border-radius:22px!important;border:1.5px solid var(--ln)!important}
.sw-e{display:flex;gap:10px;align-items:flex-start;margin-bottom:8px;padding:10px 12px;border:1.5px solid #fecaca;border-radius:10px;background:#fef2f2;
    color:#7f1d1d;font-size:13px;text-align:left}

@media(max-width:860px){.grid{grid-template-columns:1fr}.fg{grid-template-columns:1fr}.lv h1{font-size:32px}.acts{flex-direction:column-reverse}.btn{width:100%}}
</style>

<svg width="0" height="0" style="position:absolute" aria-hidden="true"><defs>
    <symbol id="i-list" viewBox="0 0 24 24"><path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/></symbol>
    <symbol id="i-cal" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></symbol>
    <symbol id="i-tag" viewBox="0 0 24 24"><path d="M20.6 13.4 13.4 20.6a2 2 0 0 1-2.8 0L2 12V2h10l8.6 8.6a2 2 0 0 1 0 2.8z"/><path d="M7 7h.01"/></symbol>
    <symbol id="i-send" viewBox="0 0 24 24"><path d="m22 2-7 20-4-9-9-4 20-7zM22 2 11 13"/></symbol>
    <symbol id="i-edit" viewBox="0 0 24 24"><path d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/></symbol>
    <symbol id="i-sum" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M3 15h18M9 3v18"/></symbol>
    <symbol id="i-x" viewBox="0 0 24 24"><path d="M18 6 6 18M6 6l12 12"/></symbol>
</defs></svg>

<div class="lv">

    <div class="lv-top">
        <div>
            <p class="lv-eye">Employee · Leave management</p>
            <h1>Apply for leave</h1>
            <p class="lv-sub">Submit a leave application for approval by your reporting authority.</p>
        </div>
        <button type="button" class="btn" id="myLeavesButton"><svg class="ic"><use href="#i-list"/></svg> My leaves</button>
    </div>

    <div class="grid">

        {{-- FORM --}}
        <div class="card">
            <div class="card-h">
                <span class="tile"><svg class="ic"><use href="#i-edit"/></svg></span>
                <div>
                    <h2>Leave application</h2>
                    <p>Enter the leave details carefully before submitting.</p>
                </div>
            </div>

            <form id="leaveApplicationForm" class="form" method="POST" action="{{ route('portal.leaves.store') }}">
                @csrf
                <div class="fg">

                    <div class="fld full">
                        <label for="leave_type">Leave type <b>*</b></label>
                        <div class="inp">
                            <svg class="ic"><use href="#i-tag"/></svg>
                            <select name="leave_type" id="leave_type" class="fc" required>
                                <option value="">Select leave type</option>
                                <option value="Casual Leave">Casual Leave</option>
                                <option value="Sick Leave">Sick Leave</option>
                                <option value="Earned Leave">Earned Leave</option>
                                <option value="Unpaid Leave">Unpaid Leave</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>

                    <div class="fld">
                        <label for="from_date">From date <b>*</b></label>
                        <div class="inp">
                            <svg class="ic"><use href="#i-cal"/></svg>
                            <input type="date" name="from_date" id="from_date" class="fc" min="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>

                    <div class="fld">
                        <label for="to_date">To date <b>*</b></label>
                        <div class="inp">
                            <svg class="ic"><use href="#i-cal"/></svg>
                            <input type="date" name="to_date" id="to_date" class="fc" min="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>

                    <div class="fld full">
                        <label for="reason">Reason <b>*</b></label>
                        <textarea name="reason" id="reason" class="fc" maxlength="2000" placeholder="Enter the reason for your leave..." required></textarea>
                        <div class="help"><span>Maximum 2000 characters.</span><span id="count">0 / 2000</span></div>
                    </div>

                </div>

                <div class="acts">
                    <button type="reset" class="btn" id="resetLeaveButton"><svg class="ic"><use href="#i-x"/></svg> Clear</button>
                    <button type="submit" class="btn btn-g" id="submitLeaveButton"><svg class="ic"><use href="#i-send"/></svg> Submit application</button>
                </div>
            </form>
        </div>

        {{-- LIVE SUMMARY TABLE --}}
        <div class="card">
            <div class="card-h">
                <span class="tile"><svg class="ic"><use href="#i-sum"/></svg></span>
                <div>
                    <h2>Leave summary</h2>
                    <p>Updates as you fill the form.</p>
                </div>
            </div>

            <table class="tb">
                <tbody>
                    <tr><th>Leave type</th><td id="s-type">—</td></tr>
                    <tr><th>From</th><td id="s-from">—</td></tr>
                    <tr><th>To</th><td id="s-to">—</td></tr>
                    <tr><th>Total days</th><td><span class="days" id="s-days">0</span></td></tr>
                    <tr><th>Status</th><td><span class="pend"><i></i> Pending approval</span></td></tr>
                </tbody>
            </table>

            <p class="note">Your application goes to your reporting authority. You'll see its status under <b>My leaves</b>.</p>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    var form = document.getElementById('leaveApplicationForm');
    var type = document.getElementById('leave_type');
    var fromDate = document.getElementById('from_date');
    var toDate = document.getElementById('to_date');
    var reason = document.getElementById('reason');
    var submitButton = document.getElementById('submitLeaveButton');
    var csrfToken = document.querySelector('input[name="_token"]').value;
    var today = '{{ date("Y-m-d") }}';
    var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    var buttonHtml = submitButton.innerHTML;

    function $(id) { return document.getElementById(id); }

    function fmt(d) {
        if (!d) { return '—'; }
        var p = d.split('-');
        return p[2] + ' ' + months[parseInt(p[1], 10) - 1] + ' ' + p[0];
    }

    function escapeHtml(text) {
        var div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function updateSummary() {
        var days = 0;

        if (fromDate.value && toDate.value && toDate.value >= fromDate.value) {
            var a = new Date(fromDate.value + 'T00:00:00');
            var b = new Date(toDate.value + 'T00:00:00');
            days = Math.round((b - a) / 86400000) + 1;
        }

        $('s-type').innerText = type.value || '—';
        $('s-from').innerText = fmt(fromDate.value);
        $('s-to').innerText = fmt(toDate.value);
        $('s-days').innerText = days;
        $('count').innerText = reason.value.length + ' / 2000';
    }

    function alertBox(icon, title, html, btn, extra) {
        var opts = {
            icon: icon, title: title, html: html, confirmButtonText: btn,
            confirmButtonColor: '#15803d', customClass: { popup: 'sw' }
        };
        for (var k in extra) { if (extra.hasOwnProperty(k)) { opts[k] = extra[k]; } }
        Swal.fire(opts);
    }

    type.addEventListener('change', updateSummary);
    toDate.addEventListener('change', updateSummary);
    reason.addEventListener('input', updateSummary);

    fromDate.addEventListener('change', function () {
        if (fromDate.value) {
            toDate.min = fromDate.value;
            if (toDate.value && toDate.value < fromDate.value) { toDate.value = fromDate.value; }
        }
        updateSummary();
    });

    $('resetLeaveButton').addEventListener('click', function () {
        setTimeout(function () { toDate.min = today; updateSummary(); }, 50);
    });

    $('myLeavesButton').addEventListener('click', function () {
        window.location.href = '{{ route("portal.leaves.index") }}';
    });

    form.addEventListener('submit', function (event) {
        event.preventDefault();

        if (!form.checkValidity()) { form.reportValidity(); return; }

        if (toDate.value < fromDate.value) {
            alertBox('warning', 'Invalid date range', 'The To date cannot be earlier than the From date.', 'Review dates', {});
            return;
        }

        submitButton.disabled = true;
        submitButton.innerHTML = '<span class="spin"></span> Submitting...';

        fetch(form.action, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            body: new FormData(form)
        })
        .then(function (response) {
            return response.json().then(function (data) {
                return { ok: response.ok, status: response.status, data: data };
            });
        })
        .then(function (result) {
            submitButton.disabled = false;
            submitButton.innerHTML = buttonHtml;

            if (result.ok) {
                form.reset();
                toDate.min = today;
                updateSummary();
                alertBox('success', 'Leave submitted',
                    'Your leave application has been submitted successfully.<br><br>' +
                    '<span class="pend"><i></i> Pending approval</span>',
                    'Done', { timer: 4000, timerProgressBar: true });
                return;
            }

            if (result.status === 422 && result.data.errors) {
                var html = '';
                Object.keys(result.data.errors).forEach(function (field) {
                    result.data.errors[field].forEach(function (message) {
                        html += '<div class="sw-e"><b>!</b><span>' + escapeHtml(message) + '</span></div>';
                    });
                });
                alertBox('warning', 'Please check your details', html, 'Review form', {});
                return;
            }

            alertBox('error', 'Unable to submit',
                escapeHtml(result.data.message || 'Something went wrong while submitting your leave application.'),
                'Try again', {});
        })
        .catch(function (error) {
            submitButton.disabled = false;
            submitButton.innerHTML = buttonHtml;
            alertBox('error', 'Connection problem', 'Unable to connect to the server. Please try again.', 'Try again', {});
            console.error(error);
        });
    });

    updateSummary();
});
</script>

@endsection