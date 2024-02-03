<?php

namespace App\DataFixtures;

use App\Entity\Movie;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class MovieFixtures extends Fixture
{

    public function load(ObjectManager $manager): void
    {
        $factory = new Factory();

        $faker = $factory->create('it_IT');

        for ($i = 1; $i <= 100; ++$i) {
            $movie = new Movie();

            $movie->setTitle($faker->realText(20, 2));
            $movie->setDescription($faker->realText(200, 2));
            $movie->setReleaseYear($faker->date('Y'));
            $movie->setImagePath($faker->imageUrl());

            // Add data to pivot table ManyToMany relantionship
            // we call here the references created in ActorFixtures
            $movie->addActor($this->getReference('actor_'. random_int(1, 25)));
            $movie->addActor($this->getReference('actor_'. random_int(26, 50)));
            $movie->addActor($this->getReference('actor_'. random_int(51, 75)));
            $movie->addActor($this->getReference('actor_'. random_int(76, 100)));

            $manager->persist($movie);
        }

        $manager->flush();
    }
}
