<?php

namespace App\Http\Requests;

use App\Enums\StatusEnum;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class StoreRepaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'loan_amount_id' => ['required', 'numeric'],
            'planned_repayment_amount' => ['required', 'numeric'],
            'planned_repayment_date' => ['required', 'date_format:Y-m-d'],
        ];
    }
}
