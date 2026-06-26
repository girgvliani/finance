<?php

use App\Http\Controllers\Api\TransactionApiController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CsvController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('transactions.index');
});

Route::get('/dashboard', function () {
    return redirect()->route('transactions.index');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('transactions', TransactionController::class)->middleware('owns');
    Route::patch('transactions/{transaction}/toggle', [TransactionController::class, 'toggle'])->name('transactions.toggle')->middleware('owns');

    Route::resource('categories', CategoryController::class)->except('show')->middleware('owns');

    Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');

    // Debt / loan calculator.
    Route::get('/loans', [LoanController::class, 'index'])->name('loans.index');
    Route::post('/loans', [LoanController::class, 'store'])->name('loans.store');
    Route::delete('/loans/{loan}', [LoanController::class, 'destroy'])->name('loans.destroy')->middleware('owns');

    // CSV import / export.
    Route::get('/csv', [CsvController::class, 'index'])->name('csv.index');
    Route::post('/csv/import', [CsvController::class, 'import'])->name('csv.import');
    Route::get('/csv/export', [CsvController::class, 'export'])->name('csv.export');

    // JSON API endpoints (session-authenticated).
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('transactions', [TransactionApiController::class, 'index'])->name('transactions.index');
        Route::get('transactions/{transaction}', [TransactionApiController::class, 'show'])->name('transactions.show')->middleware('owns');
        Route::get('events', [TransactionApiController::class, 'events'])->name('events');
    });
});

require __DIR__.'/auth.php';
