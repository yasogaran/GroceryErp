<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Customer>
 */
class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        static $customerNumber = 1;

        return [
            'customer_code' => 'CUST-' . str_pad($customerNumber++, 6, '0', STR_PAD_LEFT),
            'name' => fake()->name(),
            'phone' => fake()->numerify('07########'),
            'email' => fake()->optional(0.3)->safeEmail(),
            'address' => fake()->optional(0.5)->address(),
            'points_balance' => fake()->randomFloat(2, 0, 1000),
            'total_purchases' => fake()->randomFloat(2, 0, 50000),
            'is_active' => fake()->boolean(90), // 90% active
        ];
    }

    /**
     * Indicate that the customer is active
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the customer is inactive
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
