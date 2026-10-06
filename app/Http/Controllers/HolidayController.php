<?php

namespace App\Http\Controllers;

use App\Models\Holiday;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class HolidayController extends Controller
{
    public function index(Request $request)
    {
        $attributes = $request->validate([
            'month' => 'nullable|date_format:Y-m',
        ]);
        $month = isset($attributes['month'])
            ? Carbon::createFromFormat('!Y-m', $attributes['month'])
            : Carbon::today()->startOfMonth();
        $monthStart = $month->copy()->startOfMonth();
        $monthEnd = $month->copy()->endOfMonth();
        $holidays = Holiday::whereBetween('holiday_date', [
            $monthStart->toDateString(),
            $monthEnd->toDateString(),
        ])->orderBy('holiday_date')->orderBy('name')->get();
        $holidaysByDate = $holidays->groupBy(function ($holiday) {
            return $holiday->holiday_date->toDateString();
        });
        $calendarStart = $monthStart->copy()->startOfWeek(Carbon::MONDAY);
        $calendarEnd = $monthEnd->copy()->endOfWeek(Carbon::SUNDAY);
        $weeks = [];

        for ($weekStart = $calendarStart->copy(); $weekStart->lte($calendarEnd); $weekStart->addWeek()) {
            $week = [];
            for ($day = $weekStart->copy(); $day->lt($weekStart->copy()->addWeek()); $day->addDay()) {
                $date = $day->toDateString();
                $week[] = [
                    'date' => $day->copy(),
                    'holidays' => $holidaysByDate->get($date, collect()),
                    'inMonth' => $day->month === $month->month,
                    'isToday' => $day->isToday(),
                ];
            }
            $weeks[] = $week;
        }

        return view('portal.holidays.index', [
            'month' => $month,
            'weeks' => $weeks,
            'holidays' => $holidays,
            'holidayTypes' => $this->holidayTypes(),
            'canCreateHolidays' => $request->user()->hasPermission('holidays', 'create'),
            'canEditHolidays' => $request->user()->hasPermission('holidays', 'edit'),
            'canDeleteHolidays' => $request->user()->hasPermission('holidays', 'delete'),
            'pageTitle' => 'Holiday calendar',
        ]);
    }

    public function store(Request $request)
    {
        $attributes = $request->validate($this->rules());
        Holiday::create($attributes);

        return redirect()->route('portal.holidays.index', ['month' => substr($attributes['holiday_date'], 0, 7)])
            ->with('status', 'Holiday added to the calendar.');
    }

    public function update(Request $request, Holiday $holiday)
    {
        $attributes = $request->validate($this->rules());
        $holiday->update($attributes);

        return redirect()->route('portal.holidays.index', ['month' => substr($attributes['holiday_date'], 0, 7)])
            ->with('status', 'Holiday updated.');
    }

    public function destroy(Holiday $holiday)
    {
        $month = $holiday->holiday_date->format('Y-m');
        $holiday->delete();

        return redirect()->route('portal.holidays.index', ['month' => $month])
            ->with('status', 'Holiday removed from the calendar.');
    }

    private function rules()
    {
        return [
            'name' => 'required|string|max:120',
            'holiday_date' => 'required|date_format:Y-m-d',
            'holiday_type' => ['required', Rule::in(array_keys($this->holidayTypes()))],
            'description' => 'nullable|string|max:1000',
        ];
    }

    private function holidayTypes()
    {
        return [
            'public' => 'Public holiday',
            'company' => 'Company holiday',
            'optional' => 'Optional holiday',
        ];
    }
}
