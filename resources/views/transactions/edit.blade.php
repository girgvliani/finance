@extends('layouts.finance')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="card p-4">
            <h4 class="fw-bold mb-4"><i class="bi bi-pencil-square"></i> Edit Transaction</h4>

            <form method="POST" action="{{ route('transactions.update', $transaction) }}" enctype="multipart/form-data">
                @csrf @method('PUT')

                <div class="mb-3">
                    <label class="form-label fw-semibold">Title</label>
                    <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                        value="{{ old('title', $transaction->title) }}">
                    @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Description <span class="text-muted fw-normal">(optional)</span></label>
                    <textarea name="description" class="form-control" rows="2">{{ old('description', $transaction->description) }}</textarea>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Type</label>
                        <select name="type" class="form-select @error('type') is-invalid @enderror">
                            @foreach(['income','expense','payment','investment','note'] as $t)
                                <option value="{{ $t }}" {{ old('type', $transaction->type) == $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
                            @endforeach
                        </select>
                        @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Amount ($) <span class="text-muted fw-normal">(not needed for notes)</span></label>
                        <input type="number" step="0.01" min="0.01" name="amount"
                            class="form-control @error('amount') is-invalid @enderror"
                            value="{{ old('amount', $transaction->amount) }}">
                        @error('amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Status</label>
                        <select name="status" class="form-select @error('status') is-invalid @enderror">
                            <option value="pending" {{ old('status', $transaction->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="cleared" {{ old('status', $transaction->status) == 'cleared' ? 'selected' : '' }}>Cleared</option>
                        </select>
                        @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Calendar Date</label>
                        <input type="date" name="event_date"
                            class="form-control @error('event_date') is-invalid @enderror"
                            value="{{ old('event_date', $transaction->event_date?->format('Y-m-d')) }}">
                        @error('event_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Deadline <span class="text-muted fw-normal">(optional)</span></label>
                        <input type="date" name="deadline"
                            class="form-control @error('deadline') is-invalid @enderror"
                            value="{{ old('deadline', $transaction->deadline?->format('Y-m-d')) }}">
                        @error('deadline') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Categories</label>
                    @if($categories->isEmpty())
                        <div class="text-muted small">No categories yet — <a href="{{ route('categories.create') }}">create one</a>.</div>
                    @else
                        @php $selected = old('categories', $transaction->categories->pluck('id')->all()); @endphp
                        <div class="d-flex flex-wrap gap-3">
                            @foreach($categories as $category)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="categories[]"
                                        value="{{ $category->id }}" id="cat{{ $category->id }}"
                                        {{ in_array($category->id, $selected) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="cat{{ $category->id }}">
                                        <span class="d-inline-block rounded-circle align-middle me-1" style="width:12px;height:12px;background:{{ $category->color }}"></span>
                                        {{ $category->name }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">Receipt Image <span class="text-muted fw-normal">(optional)</span></label>
                    @if($transaction->receipt_path)
                        <div class="mb-2">
                            <img src="{{ asset('storage/'.$transaction->receipt_path) }}" alt="Receipt"
                                 class="img-thumbnail" style="max-height:120px">
                        </div>
                    @endif
                    <input type="file" name="receipt" accept="image/*"
                        class="form-control @error('receipt') is-invalid @enderror">
                    <div class="form-text">Uploading a new image replaces the current one.</div>
                    @error('receipt') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary px-4">Update Transaction</button>
                    <a href="{{ route('transactions.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
