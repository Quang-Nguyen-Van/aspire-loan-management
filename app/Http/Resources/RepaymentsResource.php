<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RepaymentsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => (string)$this->id,
            'attributes' => [
                'status' => $this->status,
                'planned_repayment_amount' => $this->planned_repayment_amount,
                'planned_repayment_date' => $this->planned_repayment_date,
                'paid_amount' => $this->paid_amount,
                'paid_at' => $this->paid_at,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
            ],
            'relationships' => [
                'user' => [
                    'id' => $this->loanAmount->user->id,
                    'name' => $this->loanAmount->user->name,
                    'email' => $this->loanAmount->user->email,
                ],
                'loanAmount' => [
                    'id' => $this->loanAmount->id,
                    'amount' => $this->loanAmount->amount,
                    'status' => $this->loanAmount->status,
                    'approver_id' => $this->loanAmount->approver_id,
                    'approved_at' => $this->loanAmount->approved_at,
                    'total_paid' => $this->loanAmount->total_paid,
                    'created_at' => $this->loanAmount->created_at,
                    'updated_at' => $this->loanAmount->updated_at,
                ]
            ]
        ];
    }
}
