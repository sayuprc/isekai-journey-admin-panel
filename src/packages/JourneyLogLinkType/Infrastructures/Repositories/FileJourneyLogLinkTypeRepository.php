<?php

declare(strict_types=1);

namespace JourneyLogLinkType\Infrastructures\Repositories;

use JourneyLogLinkType\Domain\Entities\JourneyLogLinkType;
use JourneyLogLinkType\Domain\Entities\JourneyLogLinkTypeId;
use JourneyLogLinkType\Domain\Repositories\JourneyLogLinkTypeRepositoryInterface;
use Support\Repository\FileStore;

class FileJourneyLogLinkTypeRepository implements JourneyLogLinkTypeRepositoryInterface
{
    private const string PATH = 'journey-log-link-types.dat';

    /**
     * @param FileStore<JourneyLogLinkType> $store
     */
    public function __construct(private readonly FileStore $store)
    {
    }

    /**
     * @return array<JourneyLogLinkType>
     */
    public function listJourneyLogLinkTypes(): array
    {
        return array_values($this->store->getAll(self::PATH));
    }

    public function createJourneyLogLinkType(JourneyLogLinkType $journeyLogLinkType): void
    {
        $this->store->put(self::PATH, $journeyLogLinkType->journeyLogLinkTypeId->value, $journeyLogLinkType);
    }

    public function getJourneyLogLinkType(JourneyLogLinkTypeId $journeyLogLinkTypeId): JourneyLogLinkType
    {
        $found = $this->store->get(self::PATH, $journeyLogLinkTypeId->value);
        assert($found instanceof JourneyLogLinkType);

        return $found;
    }

    public function editJourneyLogLinkType(JourneyLogLinkType $journeyLogLinkType): void
    {
        $this->store->put(self::PATH, $journeyLogLinkType->journeyLogLinkTypeId->value, $journeyLogLinkType);
    }

    public function deleteJourneyLogLinkType(JourneyLogLinkTypeId $journeyLogLinkTypeId): void
    {
        $this->store->unset(self::PATH, $journeyLogLinkTypeId->value);
    }
}
