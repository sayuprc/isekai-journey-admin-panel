<?php

declare(strict_types=1);

namespace JourneyLogLinkType\DebugInfrastructures;

use JourneyLogLinkType\Domain\Models\JourneyLogLinkType;
use JourneyLogLinkType\Domain\Models\JourneyLogLinkTypeId;
use JourneyLogLinkType\Domain\Models\JourneyLogLinkTypeRepositoryInterface;
use Support\Contracts\ConfigInterface;
use Support\Repository\FileStore;

class FileJourneyLogLinkTypeRepository implements JourneyLogLinkTypeRepositoryInterface
{
    private const string FILE_NAME = 'journey-log-link-types.dat';

    private readonly string $filePath;

    /**
     * @param FileStore<JourneyLogLinkType> $store
     */
    public function __construct(
        private readonly FileStore $store,
        private readonly ConfigInterface $config,
    ) {
        $this->filePath = $this->config->getString('debug.file.path') . '/' . self::FILE_NAME;
    }

    /**
     * @return array<JourneyLogLinkType>
     */
    public function all(): array
    {
        return array_values($this->store->getAll($this->filePath));
    }

    public function find(JourneyLogLinkTypeId $journeyLogLinkTypeId): ?JourneyLogLinkType
    {
        return $this->store->get($this->filePath, $journeyLogLinkTypeId->value);
    }

    public function insert(JourneyLogLinkType $journeyLogLinkType): void
    {
        $this->store->put($this->filePath, $journeyLogLinkType->journeyLogLinkTypeId->value, $journeyLogLinkType);
    }

    public function update(JourneyLogLinkType $journeyLogLinkType): void
    {
        $this->store->put($this->filePath, $journeyLogLinkType->journeyLogLinkTypeId->value, $journeyLogLinkType);
    }

    public function delete(JourneyLogLinkTypeId $journeyLogLinkTypeId): void
    {
        $this->store->unset($this->filePath, $journeyLogLinkTypeId->value);
    }
}
