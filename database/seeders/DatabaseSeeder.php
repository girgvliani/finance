<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Loan;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'demo@finance.com'],
            ['name' => 'Demo User', 'password' => Hash::make('password')]
        );

        // --- Categories (many-to-many target) -------------------------------
        $categoryNames = [
            'Salary'    => '#16a34a',
            'Housing'   => '#dc2626',
            'Food'      => '#ea580c',
            'Bills'     => '#2563eb',
            'Savings'   => '#7c3aed',
        ];

        $categories = collect($categoryNames)->map(fn ($color, $name) => Category::firstOrCreate(
            ['user_id' => $user->id, 'name' => $name],
            ['color' => $color]
        ));

        // Only seed transactions once.
        if ($user->transactions()->count() < 5) {
            $month = Carbon::now()->startOfMonth();

            $rows = [
                ['Monthly Salary',    'income',     3000.00, 'cleared', 1,  'Salary'],
                ['Freelance Project', 'income',     750.00,  'cleared', 6,  'Salary'],
                ['Apartment Rent',    'payment',    900.00,  'cleared', 1,  'Housing'],
                ['Grocery Shopping',  'expense',    120.00,  'cleared', 8,  'Food'],
                ['Netflix',           'expense',    15.99,   'pending', 5,  'Bills'],
                ['Electricity Bill',  'expense',    85.00,   'pending', 10, 'Bills'],
                ['Index Fund Buy',    'investment', 500.00,  'cleared', 12, 'Savings'],
                ['Call the bank',     'note',       null,    'pending', 15, null],
                ['Car Insurance',     'payment',    220.00,  'pending', 20, 'Bills'],
                ['Restaurant Dinner', 'expense',    60.00,   'cleared', 22, 'Food'],
            ];

            foreach ($rows as [$title, $type, $amount, $status, $day, $catName]) {
                $tx = $user->transactions()->create([
                    'title'      => $title,
                    'type'       => $type,
                    'amount'     => $amount,
                    'status'     => $status,
                    'event_date' => $month->copy()->addDays($day - 1)->toDateString(),
                ]);

                if ($catName) {
                    $tx->categories()->attach($categories->firstWhere('name', $catName));
                }
            }
        }

        // --- Sample loan calculation ---------------------------------------
        if ($user->loans()->count() === 0) {
            $p = 10000; $rate = 12; $n = 12;
            $r = $rate / 100 / 12;
            $monthly = $p * $r / (1 - pow(1 + $r, -$n));

            Loan::create([
                'user_id'         => $user->id,
                'title'           => 'Car Loan',
                'principal'       => $p,
                'annual_rate'     => $rate,
                'months'          => $n,
                'monthly_payment' => round($monthly, 2),
                'total_payable'   => round($monthly * $n, 2),
                'total_interest'  => round($monthly * $n - $p, 2),
                'effective_rate'  => round((pow(1 + $r, 12) - 1) * 100, 3),
            ]);
        }
    }
}
