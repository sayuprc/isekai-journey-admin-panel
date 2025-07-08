<?php

declare(strict_types=1);

namespace User\DebugInfrastructures;

use Support\Contracts\ConfigInterface;
use Support\Repository\FileStore;
use User\Domain\Models\Credential\Credential;
use User\Domain\Models\Credential\CredentialRepositoryInterface;

class FileCredentialRepository implements CredentialRepositoryInterface
{
    private const string FILE_NAME = 'credentials.dat';

    private readonly string $filePath;

    /**
     * @param FileStore<Credential> $store
     */
    public function __construct(
        private readonly FileStore $store,
        private readonly ConfigInterface $config,
    ) {
        $this->filePath = $this->config->getString('debug.file.path') . '/' . self::FILE_NAME;
    }

    public function insert(Credential $credential): void
    {
        $this->store->put($this->filePath, $credential->userId->value, $credential);
    }
}
