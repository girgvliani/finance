{{-- Renders a single calendar day cell. Expects $day (array from CalendarController). --}}
@php
    $typeColors = [
        'income'     => '#16a34a',
        'expense'    => '#dc2626',
        'payment'    => '#ea580c',
        'investment' => '#2563eb',
        'note'       => '#6b7280',
    ];
@endphp
<div class="calendar-cell border p-1 {{ $day['inMonth'] ? '' : 'bg-light text-muted' }} {{ $day['isToday'] ? 'border-primary border-2' : '' }}">
    <div class="d-flex justify-content-between align-items-center mb-1">
        <span class="small {{ $day['isToday'] ? 'fw-bold text-primary' : '' }}">{{ $day['date']->day }}</span>
        @if($day['entries']->isNotEmpty())
            <span class="badge bg-secondary rounded-pill" style="font-size:.65rem">{{ $day['entries']->count() }}</span>
        @endif
    </div>

    @foreach($day['entries'] as $entry)
        <a href="{{ route('transactions.edit', $entry) }}"
           class="d-block text-decoration-none text-truncate calendar-entry"
           style="border-left:3px solid {{ $typeColors[$entry->type] ?? '#6b7280' }}"
           title="{{ ucfirst($entry->type) }} — {{ $entry->title }}{{ $entry->amount !== null ? ' ($'.number_format($entry->amount, 2).')' : '' }}">
            {{ $entry->title }}
            @if($entry->amount !== null)
                <span class="text-muted">${{ number_format($entry->amount, 0) }}</span>
            @endif
        </a>
    @endforeach
</div>
