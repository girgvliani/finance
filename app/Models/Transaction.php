<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'type',
        'amount',
        'status',
        'deadline',
        'event_date',
        'receipt_path',
    ];

    protected $casts = [
        'deadline'   => 'date',
        'event_date' => 'date',
        'amount'     => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Many-to-many: a transaction can belong to many categories.
    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }
}
