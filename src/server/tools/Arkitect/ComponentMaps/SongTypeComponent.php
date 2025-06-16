<?php

declare(strict_types=1);

namespace Tools\Arkitect\ComponentMaps;

enum SongTypeComponent: string implements ComponentMap
{
    use Accessor;

    case Domain = 'SongType\Domain\*';
    case UseCase = 'SongType\Application\UseCase\*';
}
