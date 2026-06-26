@extends('layouts.finance')

@section('content')
<div class="card p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0 fw-bold"><i class="bi bi-tags"></i> Categories</h5>
        <a href="{{ route('categories.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Add Category
        </a>
    </div>

    @if($categories->isEmpty())
        <p class="text-muted text-center py-4">No categories yet. Create your first one!</p>
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Color</th>
                        <th>Name</th>
                        <th>Transactions</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($categories as $category)
                    <tr>
                        <td>
                            <span class="d-inline-block rounded-circle" style="width:22px;height:22px;background:{{ $category->color }}"></span>
                        </td>
                        <td class="fw-semibold">{{ $category->name }}</td>
                        <td><span class="badge bg-secondary">{{ $category->transactions_count }}</span></td>
                        <td class="text-end">
                            <a href="{{ route('categories.edit', $category) }}" class="btn btn-sm btn-outline-primary me-1">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form method="POST" action="{{ route('categories.destroy', $category) }}" class="d-inline"
                                onsubmit="return confirm('Delete this category?')">
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
@endsection
