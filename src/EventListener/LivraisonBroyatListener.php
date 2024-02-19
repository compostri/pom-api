<?php

namespace App\EventListener;

use App\DBAL\Types\BroyatEnumType;
use App\Entity\LivraisonBroyat;
use Doctrine\ORM\EntityManagerInterface;

class LivraisonBroyatListener
{
    protected $em;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    public function postPersist(LivraisonBroyat $livraison): void
    {
        $composter = $livraison->getComposter();

        // Si on créer un livraison de broyat lié a un composteur on repasse le niveau de broyat du composteur a plein
        if ($composter) {
            $composter->setBroyatLevel(BroyatEnumType::FULL);
            $this->em->persist($composter);
            $this->em->flush();
        }
    }
}
