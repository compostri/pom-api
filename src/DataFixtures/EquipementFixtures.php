<?php

namespace App\DataFixtures;

use App\Entity\Equipement;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class EquipementFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $equipements = [
            [
                'type' => 'Pavillon',
                'capacite' => '20m3',
            ],
            [
                'type' => 'Pavillon',
                'capacite' => '2.5m3',
            ],
            [
                'type' => 'Silos',
                'capacite' => '1.5m3',
            ],
            [
                'type' => 'Pavillon',
                'capacite' => '5m3',
            ],
            [
                'type' => 'Silos',
                'capacite' => '2x1m3 + BB',
            ],
            [
                'type' => 'Pavillon',
                'capacite' => '10m3',
            ],
            [
                'type' => 'Spécifique',
                'capacite' => '2.5m3',
            ],
            [
                'type' => 'Silos',
                'capacite' => '2x2,5m3 + BB',
            ],
            [
                'type' => 'Spécifique',
                'capacite' => '5m3',
            ],
            [
                'type' => 'Silos',
                'capacite' => '3x1m3 + BB',
            ],
            [
                'type' => 'Silos',
                'capacite' => '2x1m3 + BB + 1,5m3',
            ],
            [
                'type' => 'Place du Village',
                'capacite' => '5m3',
            ],
            [
                'type' => 'Silos',
                'capacite' => '3x2,5m3 + BB',
            ],
            [
                'type' => 'Silos',
                'capacite' => '4x1m3 + BB',
            ],
            [
                'type' => '2 Silos 7,5 m3 + BB 1300',
                'capacite' => '2 x 3 x 2,5 = 15m3',
            ]
        ];

        foreach ($equipements as $i => $equipement) {
            $entity = new Equipement();
            $entity->setType($equipement['type']);
            $entity->setCapacite($equipement['capacite']);
            $manager->persist($entity);

            $this->addReference('equipement_' . $i, $entity);
        }

        $manager->flush();
    }
}
