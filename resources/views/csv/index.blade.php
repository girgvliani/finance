@extends('layouts.finance')

@section('content')
@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="row g-4 justify-content-center">
    {{-- Import --}}
    <div class="col-md-6">
        <div class="card p-4 h-100">
            <h5 class="fw-bold mb-3"><i class="bi bi-upload"></i> Import from CSV</h5>
            <p class="text-muted small">Upload a CSV file to bulk-add transactions to your account.</p>

            <form method="POST" action="{{ route('csv.import') }}" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <input type="file" name="file" accept=".csv,.txt"
                        class="form-control @error('file') is-invalid @enderror">
                    @error('file') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-upload"></i> Import File
                </button>
            </form>

            <hr>
            <div class="small text-muted">
                <strong>Expected columns (header row required):</strong>
                <code>title, description, type, amount, status, event_date, deadline</code>
                <ul class="mt-2 mb-0">
                    <li><strong>type</strong>: income, expense, payment, investment, note</li>
                    <li><strong>status</strong>: pending or cleared</li>
                    <li>dates as <code>YYYY-MM-DD</code> · amount optional for notes</li>
                </ul>
            </div>
        </div>
    </div>

    {{-- Export --}}
    <div class="col-md-6">
        <div class="card p-4 h-100">
            <h5 class="fw-bold mb-3"><i class="bi bi-download"></i> Export to CSV</h5>
            <p class="text-muted small">Download all of your transactions as a CSV file (opens in Excel / Google Sheets).</p>

            <a href="{{ route('csv.export') }}" class="btn btn-success w-100">
                <i class="bi bi-download"></i> Download My Transactions
            </a>

            <hr>
            <p class="small text-muted mb-0">
                The exported file uses the same column layout as the importer, so you can export,
                edit in a spreadsheet, and re-import.
            </p>
        </div>
    </div>
</div>
@endsection
