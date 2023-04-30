<?php

namespace App\Models;

use App\Enums\StatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LoanAmount extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'amount',
        'status',
        'approver_id',
        'total_paid',
    ];

    protected $casts = [
        'status' => StatusEnum::class
    ];

    protected $dates = ['deleted_at'];


    protected function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    protected function repayments(): HasMany
    {
        return $this->hasMany(Repayment::class, 'loan_amount_id');
    }

}
