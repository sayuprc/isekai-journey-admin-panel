<?php

declare(strict_types=1);

namespace JourneyLogLinkType\Domain\Repositories;

use JourneyLogLinkType\Domain\Entities\JourneyLogLinkType;
use JourneyLogLinkType\Domain\Entities\JourneyLogLinkTypeId;

interface JourneyLogLinkTypeRepositoryInterface
{
    /**
     * @return JourneyLogLinkType[]
     */
    public function listJourneyLogLinkTypes(): array;

    public function createJourneyLogLinkType(JourneyLogLinkType $journeyLogLinkType): void;

    public function getJourneyLogLinkType(JourneyLogLinkTypeId $journeyLogLinkTypeId): JourneyLogLinkType;

    public function editJourneyLogLinkType(JourneyLogLinkType $journeyLogLinkType): void;

    public function deleteJourneyLogLinkType(JourneyLogLinkTypeId $journeyLogLinkTypeId): void;
}
