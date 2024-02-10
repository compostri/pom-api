<?php

namespace App\DBAL\Types;

use Fresh\DoctrineEnumBundle\DBAL\Types\AbstractEnumType;

class CapabilityEnumType extends AbstractEnumType
{
    public const REFERENT = 'Referent';
    public const OPENER = 'Opener';
    public const USER = 'User';

    protected static $choices = [
        self::REFERENT => 'Référent',
        self::OPENER => 'Ouvreur',
        self::USER => 'User',
    ];
}
