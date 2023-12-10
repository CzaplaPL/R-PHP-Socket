<?php

namespace Generator;

use Faker\Factory;

class BonusesGenerator
{
    static public function generate(array $workers): array
    {
        $workersCount = count($workers);
        $faker = Factory::create('pl_PL');
        $toReturn= [];
        for ($i = 0; $i < $faker->numberBetween(0, $workersCount/3); $i++) {
            $worker = $faker->randomElements($workers);
            $toReturn[$worker[0]['id']]=  $faker->numberBetween(0, 100);

        }
        return $toReturn;
    }
}
