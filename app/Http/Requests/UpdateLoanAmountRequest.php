<?php

namespace App\Http\Requests;

use App\Enums\StatusEnum;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateLoanAmountRequest extends FormRequest
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
            'amount' => ['numeric'],
            'loan_term' => ['numeric'],
            'status'=> [Rule::in(StatusEnum::class)],
            'approver_id' => ['numeric'],
        ];
    }
}
