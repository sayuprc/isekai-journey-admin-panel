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

    public function insert(JourneyLogLinkType $journeyLogLinkType): void;

    public function update(JourneyLogLinkType $journeyLogLinkType): void;

    public function delete(JourneyLogLinkTypeId $journeyLogLinkTypeId): void;
}
