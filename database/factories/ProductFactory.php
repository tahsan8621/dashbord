<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {

        return [
            'name' => $this->faker->name,
            'description' => $this->faker->paragraph,
            'total_products'=>$this->faker->numberBetween(0, 10000),
            'total_sales'=>$this->faker->numberBetween(0, 10000),
            'images' => $this->faker->imageUrl()
        ];
    }
}
