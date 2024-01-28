<?php

namespace App\DBAL\Types;

use Fresh\DoctrineEnumBundle\DBAL\Types\AbstractEnumType;

class BroyatEnumType extends AbstractEnumType
{
    public const EMPTY         = 'Empty';
    public const RESERVE       = 'Reserve';
    public const FULL          = 'Full';

    protected static $choices = [
        self::EMPTY       => 'Vide',
        self::RESERVE     => 'Sur la réserve',
        self::FULL        => 'Plein'
    ];
}
