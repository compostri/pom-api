<?php

namespace App\DataFixtures;

use App\Entity\ApprovisionnementBroyat;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ApprovisionnementBroyatFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {

    $appros = [
        'Autonomie',
        'ALISE',
        'Compostri',
        'Ville',
        'Séquoïa',
        'Libre service Compostri',
    ];

        foreach ( $appros as $i => $appro){
            $entity = new ApprovisionnementBroyat();
            $entity->setName($appro);
            $manager->persist($entity);

            $this->addReference('approvisionnement_broyat_' . $i, $entity);
        }

        $manager->flush();
    }
}
