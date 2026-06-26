@extends('layouts.finance')

@section('content')
<style>
    .calendar-grid { display: grid; grid-template-columns: repeat(7, 1fr); }
    .calendar-grid .head { background:#212529; color:#fff; text-align:center; padding:.5rem; font-weight:600; }
    .calendar-cell { min-height: 110px; vertical-align: top; }
    .calendar-entry { font-size:.72rem; padding:1px 4px; margin-bottom:2px; background:#f8f9fa; border-radius:3px; color:#212529; }
    .calendar-entry:hover { background:#e9ecef; }
</style>

<div class="card p-4">
    {{-- Month navigation --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="{{ route('calendar.index', ['month' => $prevMonth]) }}" class="btn btn-outline-secondary">
            <i class="bi bi-chevron-left"></i> Prev
        </a>
        <h4 class="fw-bold mb-0"><i class="bi bi-calendar3"></i> {{ $month->format('F Y') }}</h4>
        <a href="{{ route('calendar.index', ['month' => $nextMonth]) }}" class="btn btn-outline-secondary">
            Next <i class="bi bi-chevron-right"></i>
        </a>
    </div>

    {{-- Type legend --}}
    <div class="d-flex flex-wrap gap-3 mb-3 small">
        @foreach(['income'=>'#16a34a','expense'=>'#dc2626','payment'=>'#ea580c','investment'=>'#2563eb','note'=>'#6b7280'] as $type => $color)
            <span><span class="d-inline-block rounded-circle align-middle me-1" style="width:11px;height:11px;background:{{ $color }}"></span>{{ ucfirst($type) }}</span>
        @endforeach
    </div>

    {{-- Calendar grid --}}
    <div class="calendar-grid border-top border-start">
        @foreach(['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $weekday)
            <div class="head border-end border-bottom">{{ $weekday }}</div>
        @endforeach

        @foreach($days as $day)
            @include('calendar.partials.day', ['day' => $day])
        @endforeach
    </div>

    <div class="mt-3 text-muted small">
        <i class="bi bi-info-circle"></i> Click any entry to edit it. Add new entries from
        <a href="{{ route('transactions.create') }}">Add Transaction</a> (set the “Calendar Date”).
    </div>
</div>
@endsection
