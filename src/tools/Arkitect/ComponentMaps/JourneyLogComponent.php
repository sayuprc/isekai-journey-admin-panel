<?php

declare(strict_types=1);

namespace Tools\Arkitect\ComponentMaps;

enum JourneyLogComponent: string implements ComponentMap
{
    use Accessor;

    case Domain = 'JourneyLog\Domain\*';
    case UseCase = 'JourneyLog\UseCases\*';
}
