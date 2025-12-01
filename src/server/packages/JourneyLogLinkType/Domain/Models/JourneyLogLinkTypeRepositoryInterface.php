<?php

declare(strict_types=1);

namespace JourneyLogLinkType\Domain\Models;

interface JourneyLogLinkTypeRepositoryInterface
{
    /**
     * @return array<JourneyLogLinkType>
     */
    public function all(): array;

    public function find(JourneyLogLinkTypeId $journeyLogLinkTypeId): ?JourneyLogLinkType;

    public function save(JourneyLogLinkType $journeyLogLinkType): JourneyLogLinkType;

    public function delete(JourneyLogLinkTypeId $journeyLogLinkTypeId): void;
}
