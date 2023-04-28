<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\LoanAmount;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('repayments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_amount_id')->references('id')->on('loan_amounts')->onDelete('cascade');
            $table->enum('status', ['PENDING', 'APPROVED', 'PAID'])->default('PENDING');
            $table->double('planned_repayment_amount');
            $table->date('planned_repayment_date');
            $table->bigInteger('approver_id')->nullable(true);
            $table->dateTime('approve_at')->nullable(true);
            $table->double('paid_amount')->nullable(true);
            $table->dateTime('paid_at')->nullable(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('repayments');
    }
};
