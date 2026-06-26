<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionApiController extends Controller
{
    /**
     * GET /api/transactions
     * Returns the authenticated user's transactions as JSON.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Auth::user()->transactions()->with('categories')->latest();

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        return TransactionResource::collection($query->get())->response();
    }

    /**
     * GET /api/transactions/{transaction}
     * Returns a single transaction as JSON (ownership enforced by 'owns' middleware).
     */
    public function show(Transaction $transaction): JsonResponse
    {
        $transaction->load('categories');

        return (new TransactionResource($transaction))->response();
    }

    /**
     * GET /api/events?month=YYYY-MM
     * Calendar-friendly feed: lightweight events for a given month.
     */
    public function events(Request $request): JsonResponse
    {
        $month = Carbon::createFromFormat('Y-m', $request->input('month', now()->format('Y-m')))
            ->startOfMonth();

        $colors = [
            'income' => '#16a34a', 'expense' => '#dc2626', 'payment' => '#ea580c',
            'investment' => '#2563eb', 'note' => '#6b7280',
        ];

        $events = Auth::user()->transactions()
            ->whereBetween('event_date', [$month->copy()->startOfMonth()->toDateString(), $month->copy()->endOfMonth()->toDateString()])
            ->orderBy('event_date')
            ->get()
            ->map(fn ($t) => [
                'id'    => $t->id,
                'title' => $t->title,
                'type'  => $t->type,
                'amount' => $t->amount,
                'start' => $t->event_date?->toDateString(),
                'color' => $colors[$t->type] ?? '#6b7280',
                'url'   => route('transactions.edit', $t),
            ]);

        return response()->json([
            'month'  => $month->format('Y-m'),
            'count'  => $events->count(),
            'events' => $events,
        ]);
    }
}
