@extends('layouts.finance')

@section('content')

{{-- Summary Cards --}}
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card p-4 balance-card">
            <div class="small text-white-50">Current Balance</div>
            <div class="fs-2 fw-bold">${{ number_format($balance, 2) }}</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card p-4">
            <div class="small text-muted">Total Income</div>
            <div class="fs-3 fw-bold text-success">${{ number_format($totalIncome, 2) }}</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card p-4">
            <div class="small text-muted">Total Expenses</div>
            <div class="fs-3 fw-bold text-danger">${{ number_format($totalExpenses, 2) }}</div>
        </div>
    </div>
</div>

{{-- Filters + Add Button --}}
<div class="card p-4 mb-4">
    <form method="GET" action="{{ route('transactions.index') }}" class="row g-3 align-items-end">
        <div class="col-md-4">
            <label class="form-label fw-semibold">Filter by Type</label>
            <select name="type" class="form-select">
                <option value="">All Types</option>
                @foreach(['income','expense','payment','investment','note'] as $t)
                    <option value="{{ $t }}" {{ request('type') == $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold">Filter by Status</label>
            <select name="status" class="form-select">
                <option value="">All Statuses</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="cleared" {{ request('status') == 'cleared' ? 'selected' : '' }}>Cleared</option>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-dark w-100">Filter</button>
        </div>
        <div class="col-md-2">
            <a href="{{ route('transactions.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
        </div>
    </form>
</div>

{{-- Transactions Table --}}
<div class="card p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0 fw-bold">Transactions</h5>
        <a href="{{ route('transactions.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Add Transaction
        </a>
    </div>

    @if($transactions->isEmpty())
        <p class="text-muted text-center py-4">No transactions found. Add your first one!</p>
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Title</th>
                        <th>Type</th>
                        <th>Categories</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Deadline</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transactions as $transaction)
                    <tr>
                        <td>
                            <div class="fw-semibold">
                                {{ $transaction->title }}
                                @if($transaction->receipt_path)
                                    <a href="{{ asset('storage/'.$transaction->receipt_path) }}" target="_blank" title="View receipt">
                                        <i class="bi bi-paperclip"></i>
                                    </a>
                                @endif
                            </div>
                            @if($transaction->description)
                                <small class="text-muted">{{ $transaction->description }}</small>
                            @endif
                        </td>
                        <td>
                            @php
                                $typeClass = match($transaction->type) {
                                    'income'     => 'badge-income',
                                    'expense', 'payment' => 'badge-expense',
                                    default      => 'bg-light text-dark border',
                                };
                            @endphp
                            <span class="badge rounded-pill px-3 py-2 {{ $typeClass }}">
                                {{ ucfirst($transaction->type) }}
                            </span>
                        </td>
                        <td>
                            @forelse($transaction->categories as $category)
                                <span class="badge rounded-pill mb-1" style="background:{{ $category->color }}">{{ $category->name }}</span>
                            @empty
                                <span class="text-muted">—</span>
                            @endforelse
                        </td>
                        @php
                            $isNegative = in_array($transaction->type, ['expense', 'payment']);
                            $amountClass = $transaction->type === 'income' ? 'text-success' : ($isNegative ? 'text-danger' : 'text-muted');
                        @endphp
                        <td class="{{ $amountClass }} fw-bold">
                            @if($transaction->amount === null)
                                —
                            @else
                                {{ $transaction->type === 'income' ? '+' : ($isNegative ? '-' : '') }}${{ number_format($transaction->amount, 2) }}
                            @endif
                        </td>
                        <td>
                            <form method="POST" action="{{ route('transactions.toggle', $transaction) }}">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn btn-sm
                                    {{ $transaction->status === 'cleared' ? 'btn-success' : 'btn-outline-warning' }}">
                                    {{ ucfirst($transaction->status) }}
                                </button>
                            </form>
                        </td>
                        <td>{{ $transaction->deadline ? $transaction->deadline->format('M d, Y') : '—' }}</td>
                        <td class="text-end">
                            <a href="{{ route('transactions.edit', $transaction) }}" class="btn btn-sm btn-outline-primary me-1">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form method="POST" action="{{ route('transactions.destroy', $transaction) }}" class="d-inline"
                                onsubmit="return confirm('Delete this transaction?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

@endsection
