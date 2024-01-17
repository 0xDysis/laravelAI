<?php
namespace Database\Factories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        // Generate a unique order ID with a combination of letters and numbers
        $uniqueOrderId = 'ORD-' . $this->faker->unique()->bothify('??#####');

        return [
            'order_id' => $uniqueOrderId,
            'customer_name' => $this->faker->name,
            'order_total' => $this->faker->randomFloat(2, 10, 500),
            // ... add other fields as needed
        ];
    }
}

