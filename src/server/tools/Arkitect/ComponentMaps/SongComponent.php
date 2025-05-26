<?php

declare(strict_types=1);

namespace Tools\Arkitect\ComponentMaps;

enum SongComponent: string implements ComponentMap
{
    use Accessor;

    case Domain = 'Song\Domain\*';
    case UseCase = 'Song\UseCases\*';
}
