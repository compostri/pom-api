<?php

namespace App\EventListener;

use App\Entity\Composter;
use App\Service\Mailjet;

class ComposterListener
{
    private Mailjet $mj;

    /**
     * ComposterListener constructor.
     */
    public function __construct(Mailjet $mj)
    {
        $this->mj = $mj;
    }

    public function prePersist(Composter $composter): void
    {
        $this->mj->createComposterContactList($composter);
    }

    public function preUpdate(Composter $composter): void
    {
        $this->mj->createComposterContactList($composter);
    }
}
