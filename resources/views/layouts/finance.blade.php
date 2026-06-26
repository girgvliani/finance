<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finance Made Easy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .navbar-brand { font-weight: 700; font-size: 1.4rem; }
        .card { border: none; box-shadow: 0 2px 8px rgba(0,0,0,0.08); border-radius: 12px; }
        .badge-income { background-color: #d1fae5; color: #065f46; }
        .badge-expense { background-color: #fee2e2; color: #991b1b; }
        .balance-card { background: linear-gradient(135deg, #4f46e5, #7c3aed); color: white; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="{{ route('transactions.index') }}">
                <i class="bi bi-wallet2"></i> Finance Made Easy
            </a>
            <ul class="navbar-nav me-auto ms-4 flex-row gap-3">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('transactions.*') ? 'active fw-semibold' : '' }}" href="{{ route('transactions.index') }}">
                        <i class="bi bi-list-ul"></i> Transactions
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('categories.*') ? 'active fw-semibold' : '' }}" href="{{ route('categories.index') }}">
                        <i class="bi bi-tags"></i> Categories
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('calendar.*') ? 'active fw-semibold' : '' }}" href="{{ route('calendar.index') }}">
                        <i class="bi bi-calendar3"></i> Calendar
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('loans.*') ? 'active fw-semibold' : '' }}" href="{{ route('loans.index') }}">
                        <i class="bi bi-calculator"></i> Loan Calc
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('csv.*') ? 'active fw-semibold' : '' }}" href="{{ route('csv.index') }}">
                        <i class="bi bi-file-earmark-spreadsheet"></i> Import/Export
                    </a>
                </li>
            </ul>
            <div class="ms-auto d-flex align-items-center gap-3">
                <span class="text-light">{{ Auth::user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="btn btn-outline-light btn-sm">Logout</button>
                </form>
            </div>
        </div>
    </nav>

    <div class="container">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
