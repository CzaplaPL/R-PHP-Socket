<?php

namespace Generator;

use Faker\Factory;

class WorkerGenerator
{
    static public function generate(): array
    {
        $faker = Factory::create('pl_PL');
        return [
            'id' => $faker->uuid(),
            'name' => $faker->name(),
            'email' => $faker->email(),
            'role' => $faker->randomElement(['HR','seller','workMan','accounting']),
            'phoneNumber' => $faker->phoneNumber(),
            'salary' => $faker->numberBetween(2000, 10000),
            'currency' => $faker->randomElement(['PLN','EUR','GBP','USD'])
        ];
    }
}
