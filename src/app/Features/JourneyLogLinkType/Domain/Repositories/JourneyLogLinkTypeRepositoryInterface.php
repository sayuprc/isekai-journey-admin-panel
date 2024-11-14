<?php

declare(strict_types=1);

namespace App\Features\JourneyLogLinkType\Domain\Repositories;

use App\Features\JourneyLogLinkType\Domain\Entities\JourneyLogLinkType;
use App\Features\JourneyLogLinkType\Domain\Entities\JourneyLogLinkTypeId;

interface JourneyLogLinkTypeRepositoryInterface
{
    /**
     * @return JourneyLogLinkType[]
     */
    public function listJourneyLogLinkTypes(): array;

    public function createJourneyLogLinkType(JourneyLogLinkType $journeyLogLinkType): void;

    public function getJourneyLogLinkType(JourneyLogLinkTypeId $journeyLogLinkTypeId): JourneyLogLinkType;
}
