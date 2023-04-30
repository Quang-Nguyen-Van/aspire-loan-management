<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RepaymentController;
use App\Http\Controllers\LoanAmountController;


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/login',[AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);


Route::group(['middleware' => ['auth:sanctum']], function(){
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::resource('/loans', LoanAmountController::class, ['only' => [
        'index', 'show', 'store', 'update', 'destroy'
    ]]);

    Route::resource('/repayments', RepaymentController::class, ['only' => [
        'index', 'show', 'store', 'update', 'destroy'
    ]]);

    Route::patch('/repayments/repay/{id}', [RepaymentController::class, 'repay']);

    Route::group(['middleware' => ['admin']], function(){
        Route::patch('/loans/approve/{id}', [LoanAmountController::class, 'approve']);
    });
});



Route::fallback(function(){
    return response()->json([
        'message' => 'Page Not Found. If error persists, contact info@website.com'], 404);
});
