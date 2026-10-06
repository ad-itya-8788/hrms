@extends('layouts.portal')

@section('title', 'Apply for Exit Pass')

@section('content')
<style @if (isset($nonce) && $nonce) nonce="{{ $nonce }}" @endif>
.ep{--green:#16834a;--green-dark:#12683a;--green-soft:#effaf3;--ink:#17211b;--text:#46534b;--muted:#748078;--line:#e1e9e3;--red:#b42318;max-width:1120px;margin:0 auto;padding:32px 24px 56px;color:var(--text)}
.ep *{box-sizing:border-box}
.ep-head{display:flex;justify-content:space-between;align-items:flex-end;gap:20px;margin-bottom:26px}
.ep-eyebrow{margin:0 0 7px;color:var(--green);font-size:12px;font-weight:800;letter-spacing:.12em;text-transform:uppercase}
.ep h1{margin:0;color:var(--ink);font-size:clamp(30px,4vw,42px);line-height:1.1;font-weight:800;letter-spacing:-.035em}
.ep-sub{margin:10px 0 0;color:var(--muted);font-size:15px}
.ep-link{display:inline-flex;align-items:center;justify-content:center;gap:8px;min-height:44px;padding:0 16px;border:1px solid var(--line);border-radius:12px;background:#fff;color:var(--text);font-size:14px;font-weight:700;text-decoration:none;transition:.18s}
.ep-link:hover{border-color:#b7c8bc;background:#f8fbf9;color:var(--ink);text-decoration:none}
.ep-link svg,.ep-icon{width:18px;height:18px;fill:none;stroke:currentColor;stroke-width:1.8;stroke-linecap:round;stroke-linejoin:round}
.ep-layout{display:grid;grid-template-columns:minmax(0,1.55fr) minmax(270px,.8fr);gap:20px;align-items:start}
.ep-card{overflow:hidden;border:1px solid var(--line);border-radius:20px;background:#fff;box-shadow:0 12px 35px rgba(23,50,32,.06)}
.ep-card-head{display:flex;align-items:center;gap:14px;padding:22px 24px;border-bottom:1px solid var(--line);background:linear-gradient(120deg,#fbfefc,#f4faf6)}
.ep-badge{display:grid;width:46px;height:46px;place-items:center;border:1px solid #d2ebda;border-radius:15px;background:#eaf7ee;color:var(--green)}
.ep-card-head h2{margin:0;color:var(--ink);font-size:18px;font-weight:800}
.ep-card-head p{margin:4px 0 0;color:var(--muted);font-size:13px}
.ep-form{padding:24px}
.ep-fields{display:grid;grid-template-columns:1fr 1fr;gap:19px}
.ep-field{min-width:0}
.ep-field.is-wide{grid-column:1/-1}
.ep-field label{display:block;margin:0 0 7px;color:var(--ink);font-size:13px;font-weight:750}
.ep-required{color:var(--red)}
.ep-control{display:block;width:100%;min-height:48px;padding:11px 13px;border:1px solid #d6e0d9;border-radius:11px;outline:0;background:#fff;color:var(--ink);font-family:inherit;font-size:14px;font-weight:500;transition:border-color .15s,box-shadow .15s}
.ep-control:hover{border-color:#aebdb2}
.ep-control:focus{border-color:var(--green);box-shadow:0 0 0 4px rgba(22,131,74,.11)}
textarea.ep-control{min-height:112px;resize:vertical;line-height:1.55}
.ep-control[aria-invalid="true"]{border-color:#dc6b63}
.ep-help{display:flex;justify-content:space-between;gap:12px;margin-top:6px;color:var(--muted);font-size:12px}
.ep-error{display:block;min-height:0;margin-top:5px;color:var(--red);font-size:12px}
.ep-error:empty{display:none}
.ep-actions{display:flex;justify-content:flex-end;gap:10px;margin-top:23px;padding-top:20px;border-top:1px solid var(--line)}
.ep-btn{display:inline-flex;align-items:center;justify-content:center;gap:9px;min-height:46px;padding:0 20px;border:1px solid transparent;border-radius:12px;font-family:inherit;font-size:14px;font-weight:700;text-decoration:none;cursor:pointer;transition:.18s}
.ep-btn-light{border-color:var(--line);background:#fff;color:var(--text)}
.ep-btn-light:hover{background:#f7faf8;color:var(--ink)}
.ep-btn-primary{background:var(--green);color:#fff;box-shadow:0 8px 18px rgba(22,131,74,.18)}
.ep-btn-primary:hover{background:var(--green-dark)}
.ep-btn-primary:disabled{opacity:.72;cursor:wait}
.ep-spin{width:16px;height:16px;border:2px solid rgba(255,255,255,.4);border-top-color:#fff;border-radius:50%;animation:ep-rotate .7s linear infinite}
@keyframes ep-rotate{to{transform:rotate(360deg)}}
.ep-notice{display:flex;align-items:flex-start;gap:11px;margin:0 0 18px;padding:14px 16px;border:1px solid;border-radius:13px;font-size:14px;line-height:1.5}
.ep-notice[hidden]{display:none}
.ep-notice.is-success{border-color:#b9e5c7;background:#f0fbf3;color:#17653a}
.ep-notice.is-error{border-color:#f2c6c2;background:#fff5f4;color:#8e2119}
.ep-notice-icon{flex:0 0 20px;width:20px;height:20px}
.ep-aside{padding:23px}
.ep-aside h2{margin:0;color:var(--ink);font-size:17px;font-weight:800}
.ep-aside-intro{margin:7px 0 20px;color:var(--muted);font-size:13px;line-height:1.55}
.ep-summary{display:grid;gap:0;margin:0;padding:0;list-style:none}
.ep-summary li{display:flex;align-items:center;justify-content:space-between;gap:14px;padding:13px 0;border-top:1px solid #edf1ee;font-size:13px}
.ep-summary span:first-child{color:var(--muted)}
.ep-summary strong{color:var(--ink);font-weight:750;text-align:right;overflow-wrap:anywhere}
.ep-status{display:inline-flex;align-items:center;gap:7px;padding:6px 10px;border:1px solid #fed7aa;border-radius:99px;background:#fff7ed;color:#b54708;font-size:12px;font-weight:750}
.ep-status i{width:7px;height:7px;border-radius:50%;background:#f79009}
.ep-tip{margin:19px 0 0;padding:14px;border:1px solid #d8ecde;border-radius:12px;background:var(--green-soft);color:#356247;font-size:12px;line-height:1.55}
.ep-tip strong{display:block;margin-bottom:3px;color:#205536}
@media(max-width:800px){.ep-layout{grid-template-columns:1fr}.ep-aside{order:-1}}
@media(max-width:580px){.ep{padding:24px 15px 38px}.ep-head{align-items:flex-start;flex-direction:column}.ep-fields{grid-template-columns:1fr}.ep-field.is-wide{grid-column:auto}.ep-form{padding:19px}.ep-card-head{padding:18px 19px}.ep-actions{flex-direction:column-reverse}.ep-btn{width:100%}}
</style>

<div class="ep">
    <header class="ep-head">
        <div>
            <p class="ep-eyebrow">Employee services</p>
            <h1>Apply for an exit pass</h1>
            <p class="ep-sub">Let your manager know when and why you need to leave during working hours.</p>
        </div>
        <a href="{{ route('portal.exit-pass.index') }}" class="ep-link">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m15 18-6-6 6-6M9 12h12"/><path d="M3 5v14"/></svg>
            My exit passes
        </a>
    </header>

    <div class="ep-notice" id="exit-pass-notice" role="status" aria-live="polite" hidden>
        <svg class="ep-notice-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"></svg>
        <div></div>
    </div>

    <div class="ep-layout">
        <section class="ep-card" aria-labelledby="exit-pass-form-title">
            <div class="ep-card-head">
                <span class="ep-badge"><svg class="ep-icon" viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="3" width="16" height="18" rx="2"/><path d="M8 7h8M8 11h5M8 16h.01M12 16h.01M16 16h.01"/></svg></span>
                <div>
                    <h2 id="exit-pass-form-title">Exit pass details</h2>
                    <p>Fields marked <span class="ep-required">*</span> are required.</p>
                </div>
            </div>

            <form class="ep-form" id="exit-pass-form" method="POST" action="{{ route('portal.exit-pass.store') }}" novalidate>
                @csrf
                <div class="ep-fields">
                    <div class="ep-field">
                        <label for="exit_date">Exit date <span class="ep-required">*</span></label>
                        <input class="ep-control" type="date" id="exit_date" name="exit_date" value="{{ old('exit_date', date('Y-m-d')) }}" min="{{ date('Y-m-d') }}" required aria-describedby="error-exit_date">
                        <small class="ep-error" id="error-exit_date" data-error-for="exit_date"></small>
                    </div>
                    <div class="ep-field">
                        <label for="exit_time">Leaving time <span class="ep-required">*</span></label>
                        <input class="ep-control" type="time" id="exit_time" name="exit_time" value="{{ old('exit_time') }}" required aria-describedby="error-exit_time">
                        <small class="ep-error" id="error-exit_time" data-error-for="exit_time"></small>
                    </div>
                    <div class="ep-field">
                        <label for="expected_return_time">Expected return time <span style="color:var(--muted);font-weight:500">(optional)</span></label>
                        <input class="ep-control" type="time" id="expected_return_time" name="expected_return_time" value="{{ old('expected_return_time') }}" aria-describedby="error-expected_return_time">
                        <small class="ep-error" id="error-expected_return_time" data-error-for="expected_return_time"></small>
                    </div>
                    <div class="ep-field">
                        <label for="destination">Destination <span style="color:var(--muted);font-weight:500">(optional)</span></label>
                        <input class="ep-control" type="text" id="destination" name="destination" value="{{ old('destination') }}" maxlength="255" placeholder="Where are you going?" aria-describedby="error-destination">
                        <small class="ep-error" id="error-destination" data-error-for="destination"></small>
                    </div>
                    <div class="ep-field is-wide">
                        <label for="reason">Reason for leaving <span class="ep-required">*</span></label>
                        <textarea class="ep-control" id="reason" name="reason" rows="4" maxlength="500" placeholder="Briefly explain why you need to leave..." required aria-describedby="reason-help error-reason">{{ old('reason') }}</textarea>
                        <div class="ep-help"><span id="reason-help">Please provide a short explanation (up to 500 characters).</span><span id="reason-count">0 / 500</span></div>
                        <small class="ep-error" id="error-reason" data-error-for="reason"></small>
                    </div>
                    <div class="ep-field is-wide">
                        <label for="remarks">Additional remarks <span style="color:var(--muted);font-weight:500">(optional)</span></label>
                        <textarea class="ep-control" id="remarks" name="remarks" rows="3" maxlength="5000" placeholder="Anything else your manager should know?" aria-describedby="error-remarks">{{ old('remarks') }}</textarea>
                        <small class="ep-error" id="error-remarks" data-error-for="remarks"></small>
                    </div>
                </div>

                <div class="ep-actions">
                    <a href="{{ route('portal.exit-pass.index') }}" class="ep-btn ep-btn-light">Cancel</a>
                    <button type="submit" class="ep-btn ep-btn-primary" id="exit-pass-submit">
                        <svg class="ep-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="m22 2-7 20-4-9-9-4 20-7zM22 2 11 13"/></svg>
                        Submit request
                    </button>
                </div>
            </form>
        </section>

        <aside class="ep-card ep-aside" aria-labelledby="exit-pass-summary-title">
            <h2 id="exit-pass-summary-title">Request summary</h2>
            <p class="ep-aside-intro">Check your planned time before sending the request.</p>
            <ul class="ep-summary">
                <li><span>Date</span><strong id="summary-date">Not selected</strong></li>
                <li><span>Leaving</span><strong id="summary-exit-time">Not selected</strong></li>
                <li><span>Return</span><strong id="summary-return-time">Not specified</strong></li>
                <li><span>Destination</span><strong id="summary-destination">Not specified</strong></li>
                <li><span>Approval</span><span class="ep-status"><i></i>Pending review</span></li>
            </ul>
            <p class="ep-tip"><strong>What happens next?</strong>Your request will be sent for approval. You can check its status from “My exit passes”.</p>
        </aside>
    </div>
</div>

<script @if (isset($nonce) && $nonce) nonce="{{ $nonce }}" @endif>
(function () {
    'use strict';

    var form = document.getElementById('exit-pass-form');
    var notice = document.getElementById('exit-pass-notice');
    var submit = document.getElementById('exit-pass-submit');
    var submitLabel = submit.innerHTML;
    var reason = document.getElementById('reason');
    var exitDate = document.getElementById('exit_date');
    var exitTime = document.getElementById('exit_time');
    var returnTime = document.getElementById('expected_return_time');
    var destination = document.getElementById('destination');
    var today = '{{ date("Y-m-d") }}';

    function field(name) {
        return document.getElementById(name);
    }

    function setNotice(kind, message) {
        notice.className = 'ep-notice is-' + kind;
        notice.hidden = false;
        notice.querySelector('div').textContent = message;
        notice.querySelector('svg').innerHTML = kind === 'success'
            ? '<path d="m5 12 4 4L19 6"/>'
            : '<circle cx="12" cy="12" r="9"/><path d="M12 8v4m0 4h.01"/>';
        notice.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    function clearErrors() {
        var errors = form.querySelectorAll('[data-error-for]');
        Array.prototype.forEach.call(errors, function (error) {
            error.textContent = '';
            var input = field(error.getAttribute('data-error-for'));
            if (input) {
                input.removeAttribute('aria-invalid');
            }
        });
    }

    function showErrors(errors) {
        clearErrors();
        Object.keys(errors || {}).forEach(function (name) {
            var target = form.querySelector('[data-error-for="' + name + '"]');
            var input = field(name);
            if (target) {
                target.textContent = errors[name].join(' ');
            }
            if (input) {
                input.setAttribute('aria-invalid', 'true');
            }
        });
    }

    function formatDate(value) {
        if (!value) { return 'Not selected'; }
        var parts = value.split('-');
        var date = new Date(Number(parts[0]), Number(parts[1]) - 1, Number(parts[2]));
        return date.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
    }

    function formatTime(value) {
        if (!value) { return 'Not specified'; }
        var parts = value.split(':');
        var date = new Date();
        date.setHours(Number(parts[0]), Number(parts[1]), 0, 0);
        return date.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' });
    }

    function updateSummary() {
        document.getElementById('summary-date').textContent = formatDate(exitDate.value);
        document.getElementById('summary-exit-time').textContent = formatTime(exitTime.value);
        document.getElementById('summary-return-time').textContent = formatTime(returnTime.value);
        document.getElementById('summary-destination').textContent = destination.value.trim() || 'Not specified';
        document.getElementById('reason-count').textContent = reason.value.length + ' / 500';
    }

    [exitDate, exitTime, returnTime, destination, reason].forEach(function (input) {
        input.addEventListener('input', updateSummary);
        input.addEventListener('change', updateSummary);
        input.addEventListener('input', function () {
            input.removeAttribute('aria-invalid');
            var error = form.querySelector('[data-error-for="' + input.name + '"]');
            if (error) { error.textContent = ''; }
        });
    });

    form.addEventListener('submit', function (event) {
        event.preventDefault();
        clearErrors();
        notice.hidden = true;

        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        if (returnTime.value && exitTime.value && returnTime.value <= exitTime.value) {
            showErrors({ expected_return_time: ['Return time must be later than the leaving time.'] });
            returnTime.focus();
            return;
        }

        submit.disabled = true;
        submit.innerHTML = '<span class="ep-spin" aria-hidden="true"></span> Submitting...';

        fetch(form.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: new FormData(form)
        })
        .then(function (response) {
            var contentType = response.headers.get('content-type') || '';
            if (contentType.indexOf('application/json') === -1) {
                throw new Error('The server returned an unexpected response. Please reload and try again.');
            }
            return response.json().then(function (data) {
                return { ok: response.ok, status: response.status, data: data };
            });
        })
        .then(function (result) {
            if (result.ok) {
                form.reset();
                exitDate.min = today;
                exitDate.value = today;
                updateSummary();
                setNotice('success', result.data.message || 'Your exit pass request has been submitted.');
                return;
            }

            if (result.status === 422 && result.data.errors) {
                showErrors(result.data.errors);
                setNotice('error', 'Please review the highlighted fields and try again.');
                var firstInvalid = form.querySelector('[aria-invalid="true"]');
                if (firstInvalid) { firstInvalid.focus(); }
                return;
            }

            throw new Error(result.data.message || 'Unable to submit your exit pass request.');
        })
        .catch(function (error) {
            setNotice('error', error.message || 'Unable to connect to the server. Please try again.');
            console.error('Exit pass submission failed:', error);
        })
        .then(function () {
            submit.disabled = false;
            submit.innerHTML = submitLabel;
        });
    });

    updateSummary();
})();
</script>
@endsection
