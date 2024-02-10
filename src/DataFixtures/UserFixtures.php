<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;

// Add your User entity path here

class UserFixtures extends Fixture
{
    private Generator $faker;

    public function __construct()
    {
        $this->faker = Factory::create('fr_FR');
    }

    public function load(ObjectManager $manager)
    {
        for ($i = 0; $i < 10; ++$i) {
            // Create 10 maitre composter user
            $user = new User();
            $user->setRoles(['ROLE_ADMIN']);
            $user->setFirstname($this->faker->firstName);
            $user->setLastname($this->faker->lastName);
            $user->setEmail($this->faker->email);
            $user->setUsername($this->faker->userName);
            $user->setPlainPassword('password');
            $user->setPhone($this->faker->phoneNumber);
            $user->setRole($this->faker->boolean ? $this->faker->jobTitle : null);
            $user->setEnabled(true);
            $user->setMailjetId($this->faker->randomNumber(5));

            $user->setHasFormationReferentSite(true);
            $user->setHasFormationGuideComposteur(true);
            $user->setIsSubscribeToCompostriNewsletter(false);

            $manager->persist($user);

            $this->setReference('mc_'.$i, $user);
        }

        for ($i = 0; $i < 100; ++$i) {
            // Create 100 utilisateurs
            $user = new User();
            $user->setRoles(['ROLE_User']);
            $user->setFirstname($this->faker->firstName);
            $user->setLastname($this->faker->lastName);
            $user->setEmail($this->faker->email);
            $user->setUsername($this->faker->userName);
            $user->setPlainPassword('password');
            $user->setPhone($this->faker->phoneNumber);
            $user->setRole($this->faker->boolean ? $this->faker->jobTitle : null);
            $user->setEnabled($this->faker->boolean);
            $user->setMailjetId($this->faker->randomNumber(5));

            $user->setHasFormationReferentSite($this->faker->boolean);
            $user->setHasFormationGuideComposteur($this->faker->boolean);

            // Pour ne pas déclencher la synchro avec le service d'emailing
            $user->setIsSubscribeToCompostriNewsletter(false);

            $manager->persist($user);

            //$this->setReference('user_' . $i, $user);
        }

        $manager->flush();
    }
}
