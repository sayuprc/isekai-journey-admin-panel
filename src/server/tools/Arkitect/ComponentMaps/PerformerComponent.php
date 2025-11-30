<?php

declare(strict_types=1);

namespace Tools\Arkitect\ComponentMaps;

enum PerformerComponent: string implements ComponentMap
{
    use Accessor;

    case Domain = 'Performer\Domain\*';
    case UseCase = 'Performer\Application\UseCase\*';
}
