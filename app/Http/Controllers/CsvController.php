<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CsvController extends Controller
{
    private const COLUMNS = ['title', 'description', 'type', 'amount', 'status', 'event_date', 'deadline'];
    private const TYPES   = ['income', 'expense', 'payment', 'investment', 'note'];

    public function index()
    {
        return view('csv.index');
    }

    /**
     * Export the user's transactions to a downloadable CSV file.
     */
    public function export(): StreamedResponse
    {
        $filename = 'transactions_' . now()->format('Y-m-d') . '.csv';
        $transactions = Auth::user()->transactions()->latest()->get();

        return response()->streamDownload(function () use ($transactions) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, self::COLUMNS); // header row

            foreach ($transactions as $t) {
                fputcsv($handle, [
                    $t->title,
                    $t->description,
                    $t->type,
                    $t->amount,
                    $t->status,
                    $t->event_date?->toDateString(),
                    $t->deadline?->toDateString(),
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    /**
     * Import transactions from an uploaded CSV file.
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $handle = fopen($request->file('file')->getRealPath(), 'r');

        $header   = fgetcsv($handle);
        $imported = 0;
        $skipped  = 0;

        if (! $header) {
            fclose($handle);
            return back()->with('error', 'The CSV file appears to be empty.');
        }

        // Map header names to column positions so column order doesn't matter.
        $map = array_flip(array_map(fn ($h) => strtolower(trim($h)), $header));

        while (($row = fgetcsv($handle)) !== false) {
            if (count(array_filter($row, fn ($v) => trim((string) $v) !== '')) === 0) {
                continue; // blank line
            }

            $get  = fn ($key) => isset($map[$key]) && isset($row[$map[$key]]) ? trim($row[$map[$key]]) : null;
            $type = strtolower((string) $get('type'));

            // Basic per-row validation — skip rows that don't make sense.
            if (! $get('title') || ! in_array($type, self::TYPES, true)) {
                $skipped++;
                continue;
            }

            Auth::user()->transactions()->create([
                'title'       => $get('title'),
                'description' => $get('description'),
                'type'        => $type,
                'amount'      => is_numeric($get('amount')) ? $get('amount') : null,
                'status'      => in_array($get('status'), ['pending', 'cleared'], true) ? $get('status') : 'pending',
                'event_date'  => $this->parseDate($get('event_date')) ?? now()->toDateString(),
                'deadline'    => $this->parseDate($get('deadline')),
            ]);

            $imported++;
        }

        fclose($handle);

        return redirect()->route('transactions.index')
            ->with('success', "Import complete: {$imported} added, {$skipped} skipped.");
    }

    private function parseDate(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        try {
            return Carbon::parse($value)->toDateString();
        } catch (\Exception) {
            return null;
        }
    }
}
