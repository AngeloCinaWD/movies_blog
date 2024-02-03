<?php

namespace App\DataFixtures;

use App\Entity\Actor;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class ActorFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $factory = new Factory();

        $faker = $factory->create('it_IT');

        for ($i = 1; $i <= 100; ++$i) {
            $actor = new Actor();
            $actor->setName($faker->name);
            $manager->persist($actor);

            $this->addReference('actor_' . $i, $actor);
        }


        $manager->flush();
    }
}
