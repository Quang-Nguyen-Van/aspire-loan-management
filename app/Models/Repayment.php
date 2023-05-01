<?php

namespace App\Models;

use App\Enums\StatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Repayment extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'loan_amount_id',
        'status',
        'planned_repayment_amount',
        'planned_repayment_date',
        'repaid_amount',
        'repaid_at',
    ];


    protected $casts = [
        'status' => StatusEnum::class
    ];

    protected $dates = ['deleted_at', 'repaid_at'];


    protected function loanAmount(): BelongsTo
    {
        return $this->belongsTo(LoanAmount::class, 'loan_amount_id');
    }

}
