<?php

namespace App\DataFixtures;

use App\Entity\Pole;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class PoleFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $poles = [
            'Loire, Sèvre et Vignoble',
            'Loire Chézine',
            'Pôle centralité ex Nantes-Loire',
            'Pôle Centralité ex Nantes-Ouest',
            'Erdre et Cens',
            'Sud Ouest',
            'Erdre et Cens',
        ];

        foreach ($poles as $i => $pole) {
            $entity = new Pole();
            $entity->setName($pole);
            $manager->persist($entity);

            $this->addReference('pole_'.$i, $entity);
        }

        $manager->flush();
    }
}
