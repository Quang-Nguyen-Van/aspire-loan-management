<?php

namespace App\Http\Controllers;

use Exception;
use App\Enums\StatusEnum;
use App\Models\Repayment;
use App\Models\LoanAmount;
use Illuminate\Http\Request;
use App\Traits\HttpResponses;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\RepaymentsResource;
use App\Http\Requests\StoreRepaymentRequest;
use App\Http\Requests\UpdateRepaymentRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class RepaymentController extends Controller
{
    use HttpResponses;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $repayments = $this->getRepayments();

        return $repayments !== False ? $this->responseResult(RepaymentsResource::collection($repayments)):
                $this->responseError('', 'Query Error', Response::HTTP_SERVICE_UNAVAILABLE);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRepaymentRequest $request)
    {
        $request->validated($request->all());

        try{
            $loanAmount = LoanAmount::where('id', $request->loan_amount_id)->firstOrFail();

            if($loanAmount->user_id === Auth::user()->id){
                $repayment = Repayment::create([
                    'user_id' => Auth::user()->id,
                    'loan_amount_id' => $request->loan_amount_id,
                    'status' => StatusEnum::PENDING,
                    'planned_repayment_amount' => $request->planned_repayment_amount,
                    'planned_repayment_date' => $request->planned_repayment_date,
                ]);
                return $this->responseResult(new RepaymentsResource($repayment));
            }
            else{
                return $this->responseError('', 'Record not found', Response::HTTP_NOT_FOUND);
            }
        }
        catch(ModelNotFoundException $e){
            return $this->responseError('', 'Record has not been created. Please check the loan_amount_id.', Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $repayment = $this->getRepayments($id);

        return $repayment !== False ? $this->responseResult(RepaymentsResource::collection($repayment)):
                $this->responseError('', 'Query Error', Response::HTTP_SERVICE_UNAVAILABLE);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRepaymentRequest $request, $id)
    {
        $request->validated($request->all());

        try{
            $repayment = Repayment::where(['id' => $id, 'status' => StatusEnum::PENDING])->firstOrFail();

            if($repayment->loanAmount->user_id === Auth::user()->id){

                $repayment->planned_repayment_amount = $request->planned_repayment_amount ?? $repayment->planned_repayment_amount;

                $repayment->planned_repayment_date = $request->planned_repayment_date ?? $repayment->planned_repayment_date;

                $repayment->update();

                return $this->responseResult(new RepaymentsResource($repayment));
            }
            else{
                return $this->responseError('', 'Record not found', Response::HTTP_NOT_FOUND);
            }
        }
        catch(ModelNotFoundException $e){
            return $this->responseError('', 'Record not found', Response::HTTP_NOT_FOUND);
        }
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function repay(Request $request, $id)
    {
        $validated = $request->validate([
            'paid_amount' => 'required|numeric',
        ]);

        $dataError = [];

        DB::beginTransaction();
        try{
            $repayment = Repayment::where(['id' => $id, 'status' => StatusEnum::APPROVED, 'user_id' => Auth::user()->id])->firstOrFail();

            if($validated['paid_amount'] >= $repayment->planned_repayment_amount){
                $totalpaid_remainamount_loanamountstatus = $this->calculateParameters($repayment, $validated['paid_amount']);

                if($totalpaid_remainamount_loanamountstatus['remain_amount'] >= $validated['paid_amount']){
                    // Repayment
                    $validated['status'] = StatusEnum::PAID;
                    $validated['paid_at'] = Carbon::now();
                    $repayment->update($validated);

                    // LoanAmount
                    $total_paid = $repayment->loanAmount->total_paid + $validated['paid_amount'];
                    $loanAmountStatus = ($total_paid >= $repayment->loanAmount->amount) ? StatusEnum::PAID : $repayment->loanAmount->status;

                    $repayment->loanAmount->update([
                        'total_paid' => $totalpaid_remainamount_loanamountstatus['total_paid'],
                        'status' => $totalpaid_remainamount_loanamountstatus['loan_amount_status'],
                    ]);

                    DB::commit();

                    return $this->responseResult(new RepaymentsResource($repayment));
                }
                else{
                    $dataError = [
                        'remain amount' => $totalpaid_remainamount_loanamountstatus['remain_amount'],
                        'paid amount' => $validated['paid_amount'],
                    ];
                }
            }
            else{
                $dataError = [
                    'planned repayment amount' => $repayment->planned_repayment_amount,
                    'paid amount' => $validated['paid_amount'],
                ];
            }

            return $this->responseError($dataError, 'Data mismatch', Response::HTTP_NOT_ACCEPTABLE);
        }
        catch(ModelNotFoundException $e){
            DB::rollBack();
            return $this->responseError('', 'Record not found', Response::HTTP_NOT_FOUND);
        }
    }



    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Models\Repayment
     * @param  float  $paid_amount
     * @return array[total_paid, loan_amount_status, remain_amount]
     */

    private function calculateParameters($repayment, $paid_amount)
    {
        $total_paid = $repayment->loanAmount->total_paid + $paid_amount;

        $result = [
            'total_paid' => $total_paid,
            'loan_amount_status' => ($total_paid >= $repayment->loanAmount->amount) ? StatusEnum::PAID : $repayment->loanAmount->status,
            'remain_amount' => round($repayment->loanAmount->amount - $repayment->loanAmount->total_paid, 2),
        ];

        return $result;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }


    /**
     * Query all repayments if Auth user is admin.
     * Query only repayments belong to Auth user if the Auth user is not admin.
     *
     * @param  int  $id
     * @return array result or false
     */
    private function getRepayments($id = 0)
    {
        $sqlStatement = '';

        if(Auth::user()->is_admin){
            if($id){
                $sqlStatement = Repayment::where(['id' => $id]);
            }
            else{
                $sqlStatement = Repayment::query();
            }
        }
        else{
            if($id){
                $sqlStatement = Repayment::where(['id' => $id, 'user_id' => Auth::user()->id]);
            }
            else{
                $sqlStatement = Repayment::where(['user_id' => Auth::user()->id]);
            }
        }

        try
        {
            return $sqlStatement->get();
        }
        catch(Exception $e)
        {
            return false;
        }
    }
}
