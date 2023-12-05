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
            'phoneNumber' => $faker->phoneNumber(),
        ];
    }
}
