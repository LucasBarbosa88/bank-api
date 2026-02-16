<?php

namespace Database\Factories;

use App\Models\Account;
use Illuminate\Database\Eloquent\Factories\Factory;

class AccountFactory extends Factory
{
  protected $model = Account::class;

  public function definition(): array
  {
    return [
      'id' => $this->faker->unique()->randomNumber(5),
      'balance' => $this->faker->randomFloat(2, -100, 1000),
    ];
  }
}
