<?php

namespace App\DataFixtures;

use App\Entity\Financeur;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;

class FinanceurFixtures extends Fixture
{

    /**
     * @var Generator
     */
    private Generator $faker;

    public function __construct()
    {
        $this->faker = Factory::create('fr_FR');

    }

    public function load(ObjectManager $manager)
    {
        for ($i = 0; $i < 10; $i++){
            // Create 10 maitre composter user
            $financeur = new Financeur();
            $financeur->setName($this->faker->company);
            $financeur->setInitials($this->faker->randomLetter);

            $manager->persist($financeur);

            $this->setReference('financeur_' . $i, $financeur);
        }

        $manager->flush();
    }
}
