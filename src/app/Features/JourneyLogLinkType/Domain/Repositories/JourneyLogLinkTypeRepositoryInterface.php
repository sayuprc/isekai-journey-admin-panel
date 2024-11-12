<?php

declare(strict_types=1);

namespace App\Features\JourneyLogLinkType\Domain\Repositories;

use App\Features\JourneyLogLinkType\Domain\Entities\JourneyLogLinkType;

interface JourneyLogLinkTypeRepositoryInterface
{
    /**
     * @return JourneyLogLinkType[]
     */
    public function listJourneyLogLinkTypes(): array;

    public function createJourneyLogLinkType(JourneyLogLinkType $journeyLogLinkType): void;
}
