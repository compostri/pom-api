<?php

namespace App\DBAL\Types;

use Fresh\DoctrineEnumBundle\DBAL\Types\AbstractEnumType;

class StatusEnumType extends AbstractEnumType
{
    public const ACTIVE = 'Active';
    public const DELETE = 'Delete';
    public const MOVED = 'Moved';
    public const TO_BE_MOVED = 'ToBeMoved';
    public const DORMANT = 'Dormant';
    public const IN_PROJECT = 'InProject';

    protected static $choices = [
        self::ACTIVE => 'En activité',
        self::DELETE => 'Supprimé',
        self::MOVED => 'déplacé',
        self::TO_BE_MOVED => 'À déplacer',
        self::DORMANT => 'En dormance',
        self::IN_PROJECT => 'En projet',
    ];
}
