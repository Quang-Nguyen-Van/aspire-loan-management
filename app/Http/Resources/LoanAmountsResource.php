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
                                'id' => $repayment->id,
                                'status' => $repayment->status,
                                'planned_repayment_amount' => $repayment->planned_repayment_amount,
                                'planned_repayment_date' => $repayment->planned_repayment_date,
                                'approver_id' => $repayment->approver_id,
                                'approve_at' => $repayment->approve_at,
                                'paid_amount' => $repayment->paid_amount,
                                'paid_at' => $repayment->paid_at,
                                'created_at' => $repayment->created_at,
                                'updated_at' => $repayment->updated_at,
                            ];
            $i++;
        }

        return [
            'id' => (string)$this->id,
            'attributes' => [
                'amount' => (string)$this->amount,
                'status' => $this->status,
                'approver_id' => $this->approver_id,
                'approved_at' => $this->approved_at,
                'total_paid' => $this->total_paid,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
            ],
            'relationships' => [
                'user' => [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                ],
                'repayments' => $repayments,
            ]
        ];
    }
}
