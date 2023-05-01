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
                'planned_repayment_amount' => (string)$this->planned_repayment_amount,
                'planned_repayment_date' => $this->planned_repayment_date,
                'repaid_amount' => (string)$this->repaid_amount,
                'repaid_at' => $this->repaid_at,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
            ],
            'relationships' => [
                'user' => [
                    'id' => (string)$this->loanAmount->user->id,
                    'name' => $this->loanAmount->user->name,
                    'email' => $this->loanAmount->user->email,
                ],
                'loanAmount' => [
                    'id' => (string)$this->loanAmount->id,
                    'amount' => (string)$this->loanAmount->amount,
                    'status' => $this->loanAmount->status,
                    'approver_id' => (string)$this->loanAmount->approver_id,
                    'approved_at' => $this->loanAmount->approved_at,
                    'total_repaid' => (string)$this->loanAmount->total_repaid,
                    'created_at' => $this->loanAmount->created_at,
                    'updated_at' => $this->loanAmount->updated_at,
                ]
            ]
        ];
    }
}
