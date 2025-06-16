<?php

declare(strict_types=1);

namespace Tools\Arkitect\ComponentMaps;

enum JourneyLogLinkTypeComponent: string implements ComponentMap
{
    use Accessor;

    case Domain = 'JourneyLogLinkType\Domain\*';
    case UseCase = 'JourneyLogLinkType\Application\UseCase\*';
}
