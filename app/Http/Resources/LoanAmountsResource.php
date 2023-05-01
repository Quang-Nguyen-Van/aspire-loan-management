<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class LoanAmountsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $repayments = [];
        $i = 0;
        foreach($this->repayments as $repayment){
            $repayments['repayment'. $i] = [
                                'id' => (string)$repayment->id,
                                'status' => $repayment->status,
                                'planned_repayment_amount' => (string)$repayment->planned_repayment_amount,
                                'planned_repayment_date' => $repayment->planned_repayment_date,
                                'approver_id' => (string)$repayment->approver_id,
                                'approve_at' => $repayment->approve_at,
                                'repaid_amount' => (string)$repayment->repaid_amount,
                                'repaid_at' => $repayment->repaid_at,
                                'created_at' => $repayment->created_at,
                                'updated_at' => $repayment->updated_at,
                            ];
            $i++;
        }

        return [
            'id' => (string)$this->id,
            'attributes' => [
                'amount' => (string)$this->amount,
                'loan_term' => (string)$this->loan_term,
                'status' => $this->status,
                'approver_id' => (string)$this->approver_id,
                'approved_at' => $this->approved_at,
                'total_repaid' => (string)$this->total_repaid,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
            ],
            'relationships' => [
                'user' => [
                    'id' => (string)$this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                ],
                'repayments' => $repayments,
            ]
        ];
    }
}
