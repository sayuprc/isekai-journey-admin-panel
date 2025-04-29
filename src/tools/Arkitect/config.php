<?php

declare(strict_types=1);

use Tools\Arkitect\ComponentMap;
use Tools\Arkitect\Define;

return [
    new Define(
        ComponentMap::journeyLogDomain,
        [
            ComponentMap::journeyLogDomain,
            ComponentMap::journeyLogLinkTypeDomain,
            ComponentMap::supportDomain,
        ]
    ),
    new Define(
        ComponentMap::journeyLogUseCase,
        [
            ComponentMap::journeyLogDomain,
            ComponentMap::supportResultType,
        ]
    ),

    new Define(
        ComponentMap::journeyLogLinkTypeDomain,
        [
            ComponentMap::journeyLogLinkTypeDomain,
            ComponentMap::supportDomain,
        ]
    ),
    new Define(
        ComponentMap::journeyLogLinkTypeUseCase,
        [
            ComponentMap::journeyLogLinkTypeDomain,
            ComponentMap::supportResultType,
        ]
    ),

    new Define(
        ComponentMap::songDomain,
        [
            ComponentMap::songDomain,
            ComponentMap::songTypeDomain,
            ComponentMap::creatorDomain,
            ComponentMap::supportDomain,
        ]
    ),
    new Define(
        ComponentMap::songUseCase,
        [
            ComponentMap::songDomain,
        ]
    ),

    new Define(
        ComponentMap::songTypeDomain,
        [
            ComponentMap::songTypeDomain,
            ComponentMap::supportDomain,
        ]
    ),

    new Define(
        ComponentMap::songTypeUseCase,
        [
            ComponentMap::songTypeDomain,
            ComponentMap::supportResultType,
        ]
    ),

    new Define(
        ComponentMap::creatorDomain,
        [
            ComponentMap::creatorDomain,
            ComponentMap::supportDomain,
        ]
    ),
    new Define(
        ComponentMap::creatorUseCase,
        [
            ComponentMap::creatorDomain,
            ComponentMap::supportResultType,
        ]
    ),

    new Define(ComponentMap::supportDomain),
    new Define(ComponentMap::supportResultType),
];
