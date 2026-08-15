<?php

declare(strict_types=1);

namespace Tools\Arkitect\ComponentMaps;

enum PlaceComponent: string implements ComponentMap
{
    use Accessor;

    case Domain = 'Place\Domain\*';

    case UseCase = 'Place\Application\*\UseCase\*';
}
