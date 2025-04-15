<?php

declare(strict_types=1);

namespace Creator\Infrastructures\Repositories;

use Creator\Domain\Models\Creator;
use Creator\Domain\Repositories\CreatorRepositoryInterface;
use Support\Config\ConfigInterface;
use Support\Repository\FileStore;

class FileCreatorRepository implements CreatorRepositoryInterface
{
    private const string FILE_NAME = 'creators.dat';

    private readonly string $filePath;

    /**
     * @param FileStore<Creator> $store
     */
    public function __construct(
        private readonly FileStore $store,
        private readonly ConfigInterface $config,
    ) {
        $this->filePath = $this->config->getString('debug.file.path') . '/' . self::FILE_NAME;
    }

    public function createCreator(Creator $creator): void
    {
        $this->store->put($this->filePath, $creator->creatorId->value, $creator);
    }
}
