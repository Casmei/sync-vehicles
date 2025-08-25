<?php

namespace Database\Factories;

use App\Models\Vehicle;
use App\SourceType;
use Illuminate\Database\Eloquent\Factories\Factory;

class VehicleFactory extends Factory
{
    protected $model = Vehicle::class;

    public function definition(): array
    {
        return [
            'type' => 'carro',
            'brand' => $this->faker->company,
            'model' => $this->faker->word,
            'version' => $this->faker->word,
            'year' => [
                'model' => $this->faker->numberBetween(2015, 2025),
                'build' => $this->faker->numberBetween(2015, 2025),
            ],
            'color' => $this->faker->safeColorName,
            'fuel' => $this->faker->randomElement(['Gasolina', 'Flex', 'Diesel']),
            'doors' => $this->faker->numberBetween(2, 5),
            'km' => $this->faker->numberBetween(0, 100000),
            'price' => $this->faker->randomFloat(2, 50000, 300000),
            'description' => $this->faker->sentence,
            'category' => $this->faker->randomElement(['SUV', 'Sedan', 'Hatch']),
            'url_car' => $this->faker->slug,
            'optionals_json' => [],
            'fotos_json' => [],
            'source' => SourceType::LOCAL,
        ];
    }
}
