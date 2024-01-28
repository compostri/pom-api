<?php

namespace App\DataFixtures;

use App\Entity\Quartier;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class QuartierFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $quartiers = [
            'Nantes Île-de-Nantes',
            'Nantes Dervallières Zola',
            'Nantes Hauts-Pavés Saint-Félix',
            'Nantes Malakoff - Saint-Donatien',
            'Nantes Doulon-Bottière',
            'Nantes Breil-Barberie',
            'Nantes Erdre',
            'Nantes Sud',
            'Nantes Bellevue Chantenay',
            'Nantes Nord',
            'Nantes centre ville',
            'Rezé Pont-Rousseau',
            'Rezé La Houssais',
            'Rezé Trentemoult-les-Isles',
            'Rezé Château',
            'Rezé La Blordière',
            'Rezé Ragon',
            'Rezé Hôtel de Ville',
        ];

        foreach ($quartiers as $i => $quartier) {
            $entity = new Quartier();
            $entity->setName($quartier);
            $manager->persist($entity);

            $this->addReference('quartier_' . $i, $entity);
        }

        $manager->flush();
    }
}
