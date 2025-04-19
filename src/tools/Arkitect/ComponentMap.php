<?php

declare(strict_types=1);

namespace Tools\Arkitect;

enum ComponentMap: string
{
    // JourneyLog ComponentMap
    case journeyLogDomain = 'JourneyLog\Domain\*';
    case journeyLogUseCase = 'JourneyLog\UseCases\*';

    // JourneyLogLinkType ComponentMap
    case journeyLogLinkTypeDomain = 'JourneyLogLinkType\Domain\*';
    case journeyLogLinkTypeUseCase = 'JourneyLogLinkType\UseCases\*';

    // Song ComponentMap
    case songDomain = 'Song\Domain\*';
    case songUseCase = 'Song\UseCases\*';

    // SongType ComponentMap
    case songTypeDomain = 'SongType\Domain\*';

    // Creator ComponentMap
    case creatorDomain = 'Creator\Domain\*';
    case creatorUseCase = 'Creator\UseCases\*';

    // Support ComponentMap
    case supportDomain = 'Support\Domain\*';
    case supportResultType = 'Support\ResultType\*';
}
