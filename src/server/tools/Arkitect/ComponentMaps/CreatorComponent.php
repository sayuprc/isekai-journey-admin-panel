<?php

declare(strict_types=1);

namespace Tools\Arkitect\ComponentMaps;

enum CreatorComponent: string implements ComponentMap
{
    use Accessor;

    case Domain = 'Creator\Domain\*';

    case UseCase = 'Creator\Application\UseCase\*';
}
