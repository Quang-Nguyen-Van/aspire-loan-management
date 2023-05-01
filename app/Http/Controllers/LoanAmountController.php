<?php

namespace App\Http\Controllers;

use Exception;
use Carbon\Carbon;
use App\Enums\StatusEnum;
use App\Models\Repayment;
use App\Models\LoanAmount;
use Illuminate\Http\Request;
use App\Traits\HttpResponses;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\LoanAmountsResource;
use App\Http\Requests\StoreLoanAmountRequest;
use App\Http\Requests\UpdateLoanAmountRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class LoanAmountController extends Controller
{
    use HttpResponses;

    public function index()
    {
        $loanAmounts = $this->getLoanAmounts();

        return $loanAmounts->count() ? $this->responseResult(LoanAmountsResource::collection($loanAmounts)) :
            $this->responseError('', 'Record not found', Response::HTTP_NOT_FOUND);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  App\Http\Requests\StoreLoanAmountRequest  $request
     * @return App\Http\Resources\LoanAmountsResource
     */
    public function store(StoreLoanAmountRequest $request)
    {
        $request->validated($request->all());
        DB::beginTransaction();
        try{


            $loanAmount = LoanAmount::create([
                'user_id' => Auth::user()->id,
                'amount' => $request->amount,
                'loan_term' => $request->loan_term,
                'status' => StatusEnum::PENDING,
                'approver_id' => NULL,
                'approved_at' => NULL,
            ]);
            $loanAmount->refresh();

            $planned_repayment_amount = round($request->amount / $request->loan_term, 2);
            for($i = 1; $i <= $request->loan_term; $i++){
                $planned_repayment_amount = ($request->loan_term == $i) ?
                    $this->calculateRepaymentAmountForLastTerm($request->amount, $request->loan_term): $planned_repayment_amount;

                $repayment = Repayment::create([
                    'user_id' => Auth::user()->id,
                    'loan_amount_id' => $loanAmount->id,
                    'status' => StatusEnum::PENDING,
                    'planned_repayment_amount' => $planned_repayment_amount,
                    'planned_repayment_date' => Carbon::now()->addDays($i * 7)->toDateString(),
                ]);
            }

            DB::commit();
            return $this->responseResult(new LoanAmountsResource($loanAmount));
        }
        catch(Exception $e){
            DB::rollBack();
            return $this->responseError('', 'Record has not been created', Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return App\Http\Resources\LoanAmountsResource
     * Or
     * @return App\Traits\HttpResponses->responseError
     */
    public function show($id)
    {
        try{
            // $loanAmount = LoanAmount::where(['id' => $id, 'user_id' => Auth::user()->id])->firstOrFail();
            $loanAmount = $this->getLoanAmounts($id);

            return $this->responseResult(LoanAmountsResource::collection($loanAmount));
        }
        catch(ModelNotFoundException $e){
            $message = 'Record not found';
            return $this->responseError('', $message, Response::HTTP_NOT_FOUND);
        }
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  App\Http\Requests\StoreLoanAmountRequest  $request
     * @param  int  $id
     * @return App\Http\Resources\LoanAmountsResource
     */
    public function update(UpdateLoanAmountRequest $request, $id)
    {
        $request->validated($request->all());

        $loanAmount = $this->getLoanAmount($id, ['user_id' => Auth::user()->id, 'status' => StatusEnum::PENDING]);

        if($loanAmount !== false){
            DB::beginTransaction();
            try{
                $old_loan_term = $loanAmount->loan_term;

                $loanAmount->amount = $request->amount ?? $loanAmount->amount;
                $loanAmount->loan_term = $request->loan_term ?? $loanAmount->loan_term;
                $loanAmount->save();

                $loanAmount->refresh();

                $planned_repayment_amount = round($loanAmount->amount / $loanAmount->loan_term, 2);


                $diff_term = $loanAmount->loan_term - $old_loan_term;
                if($diff_term > 0){
                    for($i = $old_loan_term + 1; $i <= $loanAmount->loan_term; $i++){
                        $loanAmount_created_at = new Carbon($loanAmount->created_at);
                        $repayment = Repayment::create([
                            'user_id' => Auth::user()->id,
                            'loan_amount_id' => $loanAmount->id,
                            'status' => StatusEnum::PENDING,
                            'planned_repayment_amount' => 0,
                            'planned_repayment_date' => $loanAmount_created_at->addDays($i * 7)->toDateString(),
                        ]);
                    }
                }
                elseif($diff_term < 0){
                    $i = 1;
                    foreach($loanAmount->repayments as $repayment){
                        $loanAmount_created_at = new Carbon($loanAmount->created_at);
                        if($i <= $loanAmount->loan_term){
                            $repayment->planned_repayment_date = $loanAmount_created_at->addDays($i * 7)->toDateString();
                            $repayment->save();
                        }
                        else{
                            $repayment->delete();
                        }
                        $i++;
                    }
                }

                $loanAmount->refresh();

                $planned_repayment_amount = round($loanAmount->amount / $loanAmount->loan_term, 2);

                $j = 1;
                foreach($loanAmount->repayments as $repayment){
                    $planned_repayment_amount = ($loanAmount->loan_term == $j) ?
                        $this->calculateRepaymentAmountForLastTerm($loanAmount->amount, $loanAmount->loan_term): $planned_repayment_amount;

                    $repayment->planned_repayment_amount = $planned_repayment_amount;
                    $repayment->save();
                    $j++;
                }

                DB::commit();
                return $this->responseResult(new LoanAmountsResource($loanAmount));
            }
            catch(Exception $e){
                DB::rollBack();
                return $this->responseError('', $e->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        }
        else{
            return $this->responseError('', 'Record has not been found', Response::HTTP_NOT_FOUND);
        }
    }


    private function getLoanAmount($id, $conds = [])
    {
        $conds['id'] = $id;
        try{
            $loanAmount = LoanAmount::where($conds)->firstOrFail();

            return $loanAmount;
        }
        catch(ModelNotFoundException $e){
            return false;
        }
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  App\Http\Requests\StoreLoanAmountRequest  $request
     * @param  int  $id
     * @return App\Http\Resources\LoanAmountsResource
     */
    public function approve(Request $request, $id)
    {
        $loanAmount = $this->getLoanAmount($id, ['status' => StatusEnum::PENDING]);

        if($loanAmount->count()){
            DB::beginTransaction();

            try
            {
                $loanAmount->status = StatusEnum::APPROVED;
                $loanAmount->approver_id = Auth::user()->id;
                $loanAmount->approved_at = Carbon::now();

                $loanAmount->save();

                foreach($loanAmount->repayments as $repayment){
                    $repayment->status = StatusEnum::APPROVED;
                    $repayment->save();
                }

                DB::commit();

                return $this->responseResult(new LoanAmountsResource($loanAmount));
            }
            catch(Exception $e){
                DB::rollBack();
                return $this->responseError('', 'This Loan Amount has not been approved', Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        DB::beginTransaction();

        try{
            $loanAmount = LoanAmount::where(['id' => $id, 'user_id' => Auth::user()->id])->firstOrFail();
            $loanAmount->delete();

            foreach($loanAmount->repayments as $repayment){
                $repayment->delete();
            }

            DB::commit();

            return $this->responseSuccess('', 'Record was deleted successful');
        }
        catch(ModelNotFoundException $e){
            DB::rollBack();
            return $this->responseError('', 'Record not found', Response::HTTP_NOT_FOUND);
        }
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    private function getLoanAmounts($id = 0)
    {
        $sqlStatement = '';

        if(Auth::user()->is_admin){
            if($id){
                $sqlStatement = LoanAmount::where(['id' => $id]);
            }
            else{
                $sqlStatement = LoanAmount::query();
            }
        }
        else{
            if($id){
                $sqlStatement = LoanAmount::where(['id' => $id, 'user_id' => Auth::user()->id]);
            }
            else{
                $sqlStatement = LoanAmount::where(['user_id' => Auth::user()->id]);
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

    private function calculateRepaymentAmountForLastTerm($amount_required, $loan_term){
        $term_repayment_amount = round($amount_required / $loan_term, 2);

        $diff_amount_required_and_total_repayment_amount = $amount_required - $term_repayment_amount * $loan_term;
        $last_term_repayment_amount = $term_repayment_amount + $diff_amount_required_and_total_repayment_amount;

        return $last_term_repayment_amount;
    }
}
