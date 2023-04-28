<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Repayment extends Model
{
    use HasFactory;

    protected $fillable = [
                            'loan_ammount_id',
                            'status',
                            'planned_repayment_amount',
                            'planned_repayment_date',
                            'approver_id',
                            'approve_at',
                            'paid_amount',
                            'paid_at',
                        ];

    protected function loanAmount(): BelongsTo
    {
        return $this->belongsTo(LoanAmount::class, 'loan_amount_id');
    }
}
