<?php

namespace Generator;

use Faker\Factory;

class ExchangeRateGenerator
{
    static public function generate(): array
    {
        $faker = Factory::create('pl_PL');
        return [
            'EUR' => $faker->randomFloat(2, 3,6),
            'GBP' => $faker->randomFloat(2, 4,5),
            'USD' => $faker->randomFloat(2, 3,7),
        ];
    }
}
