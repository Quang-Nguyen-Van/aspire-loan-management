<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;


class LoanAmount extends Model
{
    use HasFactory;

    protected $fillable = [
                            'user_id',
                            'amount',
                            'status',
                            'approver_id',
                        ];

    protected function loans(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    protected function repayments(): HasMany
    {
        return $this->hasMany(Repayment::class, 'loan_amount_id');
    }
}
