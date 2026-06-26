@extends('layouts.finance')

@section('content')
<div class="row g-4">
    {{-- Calculator form --}}
    <div class="col-md-5">
        <div class="card p-4">
            <h5 class="fw-bold mb-3"><i class="bi bi-calculator"></i> Debt / Loan Calculator</h5>
            <p class="text-muted small">Work out the monthly payment and total cost of a loan from its rate and term.</p>

            <form method="POST" action="{{ route('loans.store') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label fw-semibold">Title</label>
                    <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                        value="{{ old('title') }}" placeholder="e.g. Car Loan">
                    @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Principal ($)</label>
                    <input type="number" step="0.01" min="0.01" name="principal"
                        class="form-control @error('principal') is-invalid @enderror"
                        value="{{ old('principal') }}" placeholder="10000">
                    @error('principal') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Annual Interest Rate (%)</label>
                    <input type="number" step="0.001" min="0" max="100" name="annual_rate"
                        class="form-control @error('annual_rate') is-invalid @enderror"
                        value="{{ old('annual_rate') }}" placeholder="12.5">
                    @error('annual_rate') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-4">
                    <label class="form-label fw-semibold">Term (months)</label>
                    <input type="number" min="1" max="600" name="months"
                        class="form-control @error('months') is-invalid @enderror"
                        value="{{ old('months') }}" placeholder="36">
                    @error('months') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-lightning-charge"></i> Calculate &amp; Save
                </button>
            </form>
        </div>
    </div>

    {{-- Saved calculations --}}
    <div class="col-md-7">
        <div class="card p-4">
            <h5 class="fw-bold mb-3"><i class="bi bi-clock-history"></i> Saved Calculations</h5>

            @if($loans->isEmpty())
                <p class="text-muted text-center py-4">No calculations yet. Fill the form to create one.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Loan</th>
                                <th>Monthly</th>
                                <th>Total Cost</th>
                                <th>Interest</th>
                                <th>Eff. Rate</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($loans as $loan)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $loan->title }}</div>
                                    <small class="text-muted">${{ number_format($loan->principal, 2) }} · {{ $loan->annual_rate }}% · {{ $loan->months }} mo</small>
                                </td>
                                <td class="fw-bold">${{ number_format($loan->monthly_payment, 2) }}</td>
                                <td>${{ number_format($loan->total_payable, 2) }}</td>
                                <td class="text-danger">${{ number_format($loan->total_interest, 2) }}</td>
                                <td>{{ number_format($loan->effective_rate, 2) }}%</td>
                                <td class="text-end">
                                    <form method="POST" action="{{ route('loans.destroy', $loan) }}"
                                        onsubmit="return confirm('Delete this calculation?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
