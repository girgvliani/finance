<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'principal',
        'annual_rate',
        'months',
        'monthly_payment',
        'total_payable',
        'total_interest',
        'effective_rate',
    ];

    protected $casts = [
        'principal'       => 'decimal:2',
        'annual_rate'     => 'decimal:3',
        'monthly_payment' => 'decimal:2',
        'total_payable'   => 'decimal:2',
        'total_interest'  => 'decimal:2',
        'effective_rate'  => 'decimal:3',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
