<?php
namespace Generator;

use Faker\Factory;

class ProvisionGenerator
{
    static public function generate(array $workers): array
    {
        $faker = Factory::create('pl_PL');
        $toReturn= [];
        foreach ($workers as $worker)
        {
            $toReturn[$worker['id']]=  $faker->numberBetween(100, 1000);
        }
        return $toReturn;
    }
}
