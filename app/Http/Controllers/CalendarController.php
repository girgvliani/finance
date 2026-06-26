<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CalendarController extends Controller
{
    public function index(Request $request)
    {
        // Which month are we showing? Default to the current month.
        $month = Carbon::createFromFormat('Y-m', $request->input('month', now()->format('Y-m')))
            ->startOfMonth();

        // The visible grid starts on the Sunday on/before the 1st
        // and ends on the Saturday on/after the last day.
        $gridStart = $month->copy()->startOfMonth()->startOfWeek(Carbon::SUNDAY);
        $gridEnd   = $month->copy()->endOfMonth()->endOfWeek(Carbon::SATURDAY);

        // Pull this user's entries for the visible range, grouped by day.
        $entries = Auth::user()->transactions()
            ->with('categories')
            ->whereBetween('event_date', [$gridStart->toDateString(), $gridEnd->toDateString()])
            ->orderBy('event_date')
            ->get()
            ->groupBy(fn ($t) => $t->event_date->toDateString());

        // Build a flat list of day objects for the Blade grid.
        $days = [];
        for ($day = $gridStart->copy(); $day->lte($gridEnd); $day->addDay()) {
            $key = $day->toDateString();
            $days[] = [
                'date'           => $day->copy(),
                'inMonth'        => $day->month === $month->month,
                'isToday'        => $day->isToday(),
                'entries'        => $entries->get($key, collect()),
            ];
        }

        return view('calendar.index', [
            'month'     => $month,
            'days'      => $days,
            'prevMonth' => $month->copy()->subMonth()->format('Y-m'),
            'nextMonth' => $month->copy()->addMonth()->format('Y-m'),
        ]);
    }
}
