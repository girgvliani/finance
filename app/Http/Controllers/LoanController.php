<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoanController extends Controller
{
    public function index()
    {
        $loans = Auth::user()->loans()->latest()->get();

        return view('loans.index', compact('loans'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'principal'   => 'required|numeric|min:0.01',
            'annual_rate' => 'required|numeric|min:0|max:100',
            'months'      => 'required|integer|min:1|max:600',
        ]);

        $result = $this->amortize(
            (float) $data['principal'],
            (float) $data['annual_rate'],
            (int) $data['months']
        );

        Auth::user()->loans()->create(array_merge($data, $result));

        return redirect()->route('loans.index')->with('success', 'Loan calculated and saved!');
    }

    public function destroy(Loan $loan)
    {
        $loan->delete();

        return redirect()->route('loans.index')->with('success', 'Loan deleted.');
    }

    /**
     * Standard amortizing-loan math.
     *
     * monthly payment  = P * r / (1 - (1 + r)^-n)      (r = monthly rate)
     * effective rate   = (1 + r)^12 - 1                (annual, compounded monthly)
     *
     * @return array<string, float>
     */
    private function amortize(float $principal, float $annualRate, int $months): array
    {
        $monthlyRate = $annualRate / 100 / 12;

        if ($monthlyRate > 0) {
            $monthlyPayment = $principal * $monthlyRate / (1 - pow(1 + $monthlyRate, -$months));
            $effectiveRate  = (pow(1 + $monthlyRate, 12) - 1) * 100;
        } else {
            // Interest-free loan.
            $monthlyPayment = $principal / $months;
            $effectiveRate  = 0.0;
        }

        $totalPayable  = $monthlyPayment * $months;
        $totalInterest = $totalPayable - $principal;

        return [
            'monthly_payment' => round($monthlyPayment, 2),
            'total_payable'   => round($totalPayable, 2),
            'total_interest'  => round($totalInterest, 2),
            'effective_rate'  => round($effectiveRate, 3),
        ];
    }
}
