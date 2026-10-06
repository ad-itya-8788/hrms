@extends('layouts.portal')

@section('content')
@php
    $previousMonth = $month->copy()->subMonth()->format('Y-m');
    $nextMonth = $month->copy()->addMonth()->format('Y-m');
    $firstWeekday = $month->copy()->startOfMonth()->dayOfWeekIso;
    $monthName = $month->format('F Y');
@endphp

<main class="holiday-page">
    <header class="holiday-header">
        <div>
            <p class="holiday-eyebrow">PEOPLE &amp; CULTURE</p>
            <h1>Holiday calendar</h1>
            <p class="holiday-subtitle">Company, public and optional holidays for your team.</p>
        </div>
        @if ($canCreateHolidays)
            <a class="holiday-primary" href="#holiday-create">Add holiday</a>
        @endif
    </header>

    @if (session('status'))
        <div class="holiday-notice" role="status">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="holiday-error" role="alert">{{ $errors->first() }}</div>
    @endif

    <section class="holiday-panel">
        <div class="holiday-toolbar">
            <div class="holiday-month-control">
                <a class="holiday-arrow" href="{{ route('portal.holidays.index', ['month' => $previousMonth]) }}" aria-label="Previous month">&larr;</a>
                <h2>{{ $monthName }}</h2>
                <a class="holiday-arrow" href="{{ route('portal.holidays.index', ['month' => $nextMonth]) }}" aria-label="Next month">&rarr;</a>
            </div>
            <a class="holiday-today" href="{{ route('portal.holidays.index', ['month' => now()->format('Y-m')]) }}">Today</a>
        </div>

        <div class="holiday-calendar" role="grid" aria-label="Holiday calendar for {{ $monthName }}">
            @foreach (['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $weekday)
                <div class="holiday-weekday" role="columnheader">{{ $weekday }}</div>
            @endforeach

            @foreach ($weeks as $week)
                @foreach ($week as $day)
                    <div class="holiday-day {{ $day['inMonth'] ? '' : 'is-outside' }} {{ $day['isToday'] ? 'is-today' : '' }}" role="gridcell" aria-label="{{ $day['date']->format('l, F j, Y') }}">
                        <span class="holiday-day-number">{{ $day['date']->day }}</span>
                        @foreach ($day['holidays'] as $holiday)
                            <span class="holiday-chip holiday-chip-{{ $holiday->holiday_type }}" title="{{ $holiday->name }}{{ $holiday->description ? ': ' . $holiday->description : '' }}">
                                {{ $holiday->name }}
                            </span>
                        @endforeach
                    </div>
                @endforeach
            @endforeach
        </div>

        <div class="holiday-legend" aria-label="Holiday types">
            <span><i class="holiday-chip-public"></i>Public holiday</span>
            <span><i class="holiday-chip-company"></i>Company holiday</span>
            <span><i class="holiday-chip-optional"></i>Optional holiday</span>
        </div>
    </section>

    @if ($canCreateHolidays)
        <details class="holiday-form-panel" id="holiday-create" {{ $errors->any() ? 'open' : '' }}>
            <summary>Add a holiday</summary>
            <form method="POST" action="{{ route('portal.holidays.store') }}" class="holiday-form">
                @csrf
                <label>Holiday name
                    <input type="text" name="name" maxlength="120" value="{{ old('name') }}" required>
                </label>
                <label>Date
                    <input type="date" name="holiday_date" value="{{ old('holiday_date', now()->format('Y-m-d')) }}" required>
                </label>
                <label>Type
                    <select name="holiday_type" required>
                        @foreach ($holidayTypes as $key => $label)
                            <option value="{{ $key }}" {{ old('holiday_type', 'company') === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="holiday-form-wide">Description
                    <textarea name="description" maxlength="1000" rows="3">{{ old('description') }}</textarea>
                </label>
                <div class="holiday-form-wide holiday-form-actions">
                    <button type="submit" class="holiday-primary">Save holiday</button>
                </div>
            </form>
        </details>
    @endif

    <section class="holiday-list-panel">
        <div class="holiday-list-heading">
            <div>
                <h2>Holidays this month</h2>
                <p>{{ $holidays->count() }} {{ \Illuminate\Support\Str::plural('holiday', $holidays->count()) }} in {{ $monthName }}</p>
            </div>
        </div>
        @forelse ($holidays as $holiday)
            <article class="holiday-list-item">
                <div class="holiday-date-badge">
                    <strong>{{ $holiday->holiday_date->format('d') }}</strong>
                    <span>{{ $holiday->holiday_date->format('M') }}</span>
                </div>
                <div class="holiday-list-copy">
                    <h3>{{ $holiday->name }}</h3>
                    <p>{{ $holiday->holiday_date->format('l') }} · {{ $holidayTypes[$holiday->holiday_type] }}</p>
                    @if ($holiday->description)
                        <p>{{ $holiday->description }}</p>
                    @endif
                </div>
                @if ($canEditHolidays || $canDeleteHolidays)
                    <div class="holiday-item-actions">
                        @if ($canEditHolidays)
                            <details class="holiday-edit">
                                <summary>Edit</summary>
                                <form method="POST" action="{{ route('portal.holidays.update', $holiday) }}" class="holiday-edit-form">
                                    @csrf
                                    @method('PUT')
                                    <label>Holiday name<input type="text" name="name" maxlength="120" value="{{ $holiday->name }}" required></label>
                                    <label>Date<input type="date" name="holiday_date" value="{{ $holiday->holiday_date->format('Y-m-d') }}" required></label>
                                    <label>Type
                                        <select name="holiday_type" required>
                                            @foreach ($holidayTypes as $key => $label)
                                                <option value="{{ $key }}" {{ $holiday->holiday_type === $key ? 'selected' : '' }}>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </label>
                                    <label class="holiday-form-wide">Description<textarea name="description" maxlength="1000" rows="2">{{ $holiday->description }}</textarea></label>
                                    <button type="submit" class="holiday-primary">Update holiday</button>
                                </form>
                            </details>
                        @endif
                        @if ($canDeleteHolidays)
                            <form method="POST" action="{{ route('portal.holidays.destroy', $holiday) }}" onsubmit="return confirm('Remove this holiday from the calendar?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="holiday-delete">Delete</button>
                            </form>
                        @endif
                    </div>
                @endif
            </article>
        @empty
            <p class="holiday-empty">No holidays are scheduled for {{ $monthName }}.</p>
        @endforelse
    </section>
</main>

<style>
.holiday-page { max-width: 1240px; margin: 0 auto; color: #17211b; }
.holiday-header, .holiday-toolbar, .holiday-month-control, .holiday-list-heading, .holiday-list-item, .holiday-item-actions { display: flex; align-items: center; }
.holiday-header, .holiday-toolbar, .holiday-list-heading { justify-content: space-between; gap: 16px; }
.holiday-header { margin-bottom: 22px; }
.holiday-header h1 { margin: 0; font-size: clamp(28px, 4vw, 38px); letter-spacing: -.03em; }
.holiday-eyebrow { margin: 0 0 6px; color: #15803d; font-size: 12px; font-weight: 800; letter-spacing: .09em; }
.holiday-subtitle, .holiday-list-heading p { margin: 6px 0 0; color: #737d76; }
.holiday-primary, .holiday-today, .holiday-arrow, .holiday-edit summary, .holiday-delete { display: inline-flex; align-items: center; justify-content: center; min-height: 40px; padding: 0 15px; border: 1px solid #dce5df; border-radius: 10px; background: #fff; color: #26352b; font: inherit; font-weight: 700; text-decoration: none; cursor: pointer; }
.holiday-primary { border-color: #15803d; background: #15803d; color: #fff; }
.holiday-primary:hover { background: #166534; }
.holiday-panel, .holiday-form-panel, .holiday-list-panel { margin-bottom: 18px; border: 1px solid #e2e8e4; border-radius: 16px; background: #fff; box-shadow: 0 5px 24px rgba(22, 45, 31, .045); }
.holiday-panel { overflow: hidden; }
.holiday-toolbar { min-height: 72px; padding: 14px 18px; border-bottom: 1px solid #edf0ee; }
.holiday-month-control { gap: 12px; }
.holiday-month-control h2, .holiday-list-heading h2 { margin: 0; font-size: 20px; }
.holiday-arrow { min-width: 40px; padding: 0; font-size: 21px; }
.holiday-calendar { display: grid; grid-template-columns: repeat(7, minmax(0, 1fr)); }
.holiday-weekday { padding: 12px 8px; border-bottom: 1px solid #edf0ee; color: #78837b; font-size: 12px; font-weight: 700; text-align: center; }
.holiday-day { min-height: 112px; padding: 9px; border-right: 1px solid #edf0ee; border-bottom: 1px solid #edf0ee; overflow: hidden; }
.holiday-day:nth-child(7n) { border-right: 0; }
.holiday-day.is-outside { background: #fafbfa; color: #a8b0aa; }
.holiday-day.is-today .holiday-day-number { display: inline-grid; min-width: 27px; height: 27px; place-items: center; border-radius: 50%; background: #15803d; color: #fff; }
.holiday-day-number { display: inline-block; margin-bottom: 6px; font-size: 13px; font-weight: 700; }
.holiday-chip { display: block; margin-top: 4px; padding: 4px 6px; border-radius: 5px; overflow: hidden; font-size: 11px; font-weight: 700; text-overflow: ellipsis; white-space: nowrap; }
.holiday-chip-public { background: #fff0e8; color: #a14616; }
.holiday-chip-company { background: #eaf7ee; color: #166534; }
.holiday-chip-optional { background: #eff2ff; color: #4546a5; }
.holiday-legend { display: flex; flex-wrap: wrap; gap: 16px; padding: 14px 18px; color: #626d65; font-size: 12px; }
.holiday-legend span { display: inline-flex; align-items: center; gap: 7px; }
.holiday-legend i { width: 10px; height: 10px; border-radius: 3px; }
.holiday-legend .holiday-chip-public { background: #f2a46c; }
.holiday-legend .holiday-chip-company { background: #56a96d; }
.holiday-legend .holiday-chip-optional { background: #8283d6; }
.holiday-form-panel { padding: 16px 18px; }
.holiday-form-panel summary, .holiday-edit summary { cursor: pointer; font-weight: 700; }
.holiday-form, .holiday-edit-form { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 14px; margin-top: 16px; }
.holiday-form label, .holiday-edit-form label { display: grid; gap: 6px; color: #445148; font-size: 13px; font-weight: 700; }
.holiday-form input, .holiday-form select, .holiday-form textarea, .holiday-edit-form input, .holiday-edit-form select, .holiday-edit-form textarea { width: 100%; min-height: 42px; padding: 9px 11px; border: 1px solid #dce4de; border-radius: 8px; background: #fff; color: #202a23; font: inherit; }
.holiday-form textarea, .holiday-edit-form textarea { resize: vertical; }
.holiday-form-wide { grid-column: 1 / -1; }
.holiday-form-actions { display: flex; justify-content: flex-end; }
.holiday-notice, .holiday-error { margin-bottom: 16px; padding: 12px 15px; border-radius: 10px; background: #eaf7ee; color: #166534; }
.holiday-error { background: #fff0f0; color: #a12626; }
.holiday-list-panel { padding: 18px; }
.holiday-list-heading { margin-bottom: 12px; }
.holiday-list-item { gap: 14px; padding: 14px 0; border-top: 1px solid #edf0ee; }
.holiday-date-badge { display: grid; width: 52px; min-width: 52px; height: 58px; align-content: center; justify-items: center; border-radius: 11px; background: #f1f8f3; color: #166534; }
.holiday-date-badge strong { font-size: 19px; line-height: 1.1; }
.holiday-date-badge span { margin-top: 3px; font-size: 11px; font-weight: 800; text-transform: uppercase; }
.holiday-list-copy { flex: 1; min-width: 0; }
.holiday-list-copy h3 { margin: 0; font-size: 15px; }
.holiday-list-copy p { margin: 4px 0 0; color: #717b74; font-size: 13px; }
.holiday-item-actions { align-self: flex-start; gap: 8px; }
.holiday-edit summary { min-height: 36px; padding: 0 11px; }
.holiday-edit-form { min-width: min(560px, 80vw); padding: 12px; border: 1px solid #e2e8e4; border-radius: 10px; background: #fff; }
.holiday-delete { min-height: 36px; padding: 0 11px; color: #a12626; }
.holiday-empty { padding: 20px 0; color: #737d76; text-align: center; }
@media (max-width: 760px) {
    .holiday-day { min-height: 82px; padding: 6px 4px; }
    .holiday-weekday { font-size: 10px; }
    .holiday-chip { padding: 3px 4px; font-size: 9px; }
    .holiday-form, .holiday-edit-form { grid-template-columns: 1fr; }
    .holiday-form-wide { grid-column: auto; }
}
@media (max-width: 520px) {
    .holiday-header { align-items: flex-start; flex-direction: column; }
    .holiday-header .holiday-primary { width: 100%; }
    .holiday-day { min-height: 64px; }
    .holiday-month-control h2 { font-size: 17px; }
    .holiday-list-item { align-items: flex-start; flex-wrap: wrap; }
    .holiday-item-actions { width: 100%; margin-left: 66px; }
}
@media print {
    .holiday-header > .holiday-primary, .holiday-form-panel, .holiday-item-actions, .holiday-today, .holiday-arrow { display: none !important; }
    .holiday-panel, .holiday-list-panel { box-shadow: none; break-inside: avoid; }
}
</style>
@endsection
