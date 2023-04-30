<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use Carbon\Carbon;
use App\Models\User;
use App\Enums\StatusEnum;
use App\Models\Repayment;
use App\Models\LoanAmount;
use Illuminate\Database\Seeder;
use Database\Seeders\UserSeeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'name' => 'User',
            'email' => 'user@example.com',
            'password' => Hash::make('T3stabcde#'),
        ]);

        User::create([
            'name' => 'User',
            'email' => 'test@example.com',
            'password' => Hash::make('T3stabcde#'),
        ]);

        LoanAmount::factory(5)->create();


        $loanAmounts = LoanAmount::query()->get();

        foreach($loanAmounts as $loanAmount){
            for($i = 1; $i <= 3; $i++){
                Repayment::factory()->create([
                    'user_id' => $loanAmount->user_id,
                    'loan_amount_id' => $loanAmount->id,
                    'status' => StatusEnum::PENDING,
                    'planned_repayment_amount' => round($loanAmount->amount / 3, 2),
                    'planned_repayment_date' => Carbon::now()->addDays($i*7)->toDateString(),
                ]);
            }
        }


        User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('T3stabcde#'),
            'is_admin' => true,
        ]);
    }
}
