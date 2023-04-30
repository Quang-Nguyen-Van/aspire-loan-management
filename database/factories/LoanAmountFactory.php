<?php

namespace Database\Factories;

use App\Enums\StatusEnum;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LoanAmount>
 */
class LoanAmountFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'user_id' => User::all()->random()->id,
            'amount' => $this->faker->randomNumber(5),
            'status' => StatusEnum::PENDING,
        ];
    }
}
