<?php

namespace App\DataFixtures;

use App\Entity\Categorie;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CategorieFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $cats = [
           "Quartier",
           "Privé",
           "École",
           "Jardin",
           "Place de village"
        ];

        foreach ($cats as $i => $cat) {
            $entity = new Categorie();
            $entity->setName($cat);
            $manager->persist($entity);

            $this->addReference('categorie_' . $i, $entity);
        }

        $manager->flush();
    }
}
