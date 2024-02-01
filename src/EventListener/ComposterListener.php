<?php

namespace App\EventListener;

use App\Entity\Composter;
use App\Service\Mailjet;

class ComposterListener
{
    /**
     * @var Mailjet
     */
    private Mailjet $mj;


    /**
     * ComposterListener constructor.
     * @param Mailjet $mj
     */
    public function __construct(Mailjet $mj)
    {
        $this->mj = $mj;
    }


    /**
     * @param Composter $composter
     */
    public function prePersist(Composter $composter): void
    {
        $this->mj->createComposterContactList($composter);
    }


    /**
     * @param Composter $composter
     */
    public function preUpdate(Composter $composter): void
    {
        $this->mj->createComposterContactList($composter);
    }
}
