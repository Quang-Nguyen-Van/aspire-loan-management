<?php

use App\Enums\StatusEnum;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loan_amounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->double('amount')->nullable(false);
            $table->integer('loan_term')->nullable(false)->default(0);
            $table->string('status')->default('PENDING');
            $table->bigInteger('approver_id')->nullable(true);
            $table->dateTime('approved_at')->nullable(true);
            $table->double('total_repaid')->default(0);
            $table->softDeletes();
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
        Schema::dropIfExists('loan_amounts');
    }
};
