<?php

namespace App\Http\Controllers;

use Exception;
use App\Enums\StatusEnum;
use App\Models\LoanAmount;
use Illuminate\Http\Request;
use App\Traits\HttpResponses;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\LoanAmountsResource;
use App\Http\Requests\StoreLoanAmountRequest;
use Carbon\Carbon;
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
        try{
            $request->validated($request->all());

            $loanAmount = LoanAmount::create([
                'user_id' => Auth::user()->id,
                'amount' => $request->amount,
                'status' => StatusEnum::PENDING,
                'approver_id' => NULL,
                'approved_at' => NULL,
            ]);

            return $this->responseResult(new LoanAmountsResource($loanAmount));
        }
        catch(Exception $e){
            return $this->responseError('', 'Record has not been created', Response::HTTP_NOT_MODIFIED);
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
    public function update(StoreLoanAmountRequest $request, $id)
    {
        try{
            $loanAmount = LoanAmount::where(['id' => $id, 'user_id' => Auth::user()->id])->firstOrFail();

            $request->validated($request->all());

            $loanAmount->update([
                'amount' => $request->amount,
            ]);

            return $this->responseResult(new LoanAmountsResource($loanAmount));
        }
        catch(ModelNotFoundException $e){
            return $this->responseError('', 'Record has not been found', Response::HTTP_NOT_FOUND);
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

            $loanAmount = LoanAmount::where(['id' => $id, 'status' => StatusEnum::PENDING])->first();

        if($loanAmount->count()){
            DB::beginTransaction();

            try
            {
                $loanAmount = $loanAmount[0];
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
            catch(ModelNotFoundException $e){
                return $this->responseError('', 'Record has not been found', Response::HTTP_NOT_FOUND);
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
}
