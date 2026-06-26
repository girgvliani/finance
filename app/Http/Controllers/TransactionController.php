<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = Auth::user()->transactions()->with('categories')->latest();

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $transactions  = $query->get();
        $totalIncome   = Auth::user()->transactions()->where('type', 'income')->sum('amount');
        $totalExpenses = Auth::user()->transactions()->whereIn('type', ['expense', 'payment'])->sum('amount');
        $balance       = $totalIncome - $totalExpenses;

        return view('transactions.index', compact('transactions', 'totalIncome', 'totalExpenses', 'balance'));
    }

    public function create()
    {
        $categories = Auth::user()->categories()->orderBy('name')->get();
        return view('transactions.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $this->validateTransaction($request);

        $data['receipt_path'] = $this->storeReceipt($request);
        $data['event_date'] ??= now()->toDateString();

        $transaction = Auth::user()->transactions()->create($data);
        $transaction->categories()->sync($this->ownedCategoryIds($request));

        return redirect()->route('transactions.index')->with('success', 'Transaction added successfully!');
    }

    public function edit(Transaction $transaction)
    {
        $categories = Auth::user()->categories()->orderBy('name')->get();
        $transaction->load('categories');
        return view('transactions.edit', compact('transaction', 'categories'));
    }

    public function update(Request $request, Transaction $transaction)
    {
        $data = $this->validateTransaction($request);

        if ($newReceipt = $this->storeReceipt($request)) {
            $this->deleteReceipt($transaction);
            $data['receipt_path'] = $newReceipt;
        }
        $data['event_date'] ??= $transaction->event_date?->toDateString() ?? now()->toDateString();

        $transaction->update($data);
        $transaction->categories()->sync($this->ownedCategoryIds($request));

        return redirect()->route('transactions.index')->with('success', 'Transaction updated successfully!');
    }

    public function destroy(Transaction $transaction)
    {
        $this->deleteReceipt($transaction);
        $transaction->delete();

        return redirect()->route('transactions.index')->with('success', 'Transaction deleted successfully!');
    }

    public function toggle(Transaction $transaction)
    {
        $transaction->update([
            'status' => $transaction->status === 'pending' ? 'cleared' : 'pending',
        ]);

        return redirect()->back()->with('success', 'Status updated!');
    }

    // ---- helpers -------------------------------------------------------

    private function validateTransaction(Request $request): array
    {
        $request->validate([
            'title'        => 'required|string|max:255',
            'description'  => 'nullable|string',
            'type'         => 'required|in:income,expense,payment,investment,note',
            'amount'       => 'nullable|required_unless:type,note|numeric|min:0.01',
            'status'       => 'required|in:pending,cleared',
            'deadline'     => 'nullable|date',
            'event_date'   => 'nullable|date',
            'categories'   => 'nullable|array',
            'categories.*' => 'integer',
            'receipt'      => 'nullable|image|max:2048',
        ]);

        // Only the actual table columns get mass-assigned.
        return $request->only([
            'title', 'description', 'type', 'amount', 'status', 'deadline', 'event_date',
        ]);
    }

    /** Only keep category ids that actually belong to the current user. */
    private function ownedCategoryIds(Request $request): array
    {
        $ids = (array) $request->input('categories', []);

        return Auth::user()->categories()->whereIn('id', $ids)->pluck('id')->all();
    }

    private function storeReceipt(Request $request): ?string
    {
        if ($request->hasFile('receipt')) {
            return $request->file('receipt')->store('receipts', 'public');
        }

        return null;
    }

    private function deleteReceipt(Transaction $transaction): void
    {
        if ($transaction->receipt_path) {
            Storage::disk('public')->delete($transaction->receipt_path);
        }
    }
}
