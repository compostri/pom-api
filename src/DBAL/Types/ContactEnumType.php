<?php

namespace App\DBAL\Types;

use Fresh\DoctrineEnumBundle\DBAL\Types\AbstractEnumType;

class ContactEnumType extends AbstractEnumType
{
    public const SYNDIC       = 'Syndic';
    public const INSTITUTION  = 'Institution';
    public const SCOLAIRE     = 'Établissement scolaire';

    protected static $choices = [
        self::SYNDIC       => 'Syndic',
        self::INSTITUTION  => 'Institution',
        self::SCOLAIRE     => 'Établissement scolaire',
    ];
}
