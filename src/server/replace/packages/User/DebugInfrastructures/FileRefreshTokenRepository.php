<?php

declare(strict_types=1);

namespace User\DebugInfrastructures;

use Support\Contracts\ClockInterface;
use Support\DebugInfrastructures\Repository\FileRepositoryConfig;
use Support\DebugInfrastructures\Repository\FileStore;
use User\Domain\Models\Credential\RefreshToken\RefreshToken;
use User\Domain\Models\Credential\RefreshToken\RefreshTokenId;
use User\Domain\Models\Credential\RefreshToken\RefreshTokenRepositoryInterface;

readonly class FileRefreshTokenRepository implements RefreshTokenRepositoryInterface
{
    private const string FILE_NAME = 'refresh-tokens.dat';

    private string $filePath;

    /**
     * @param FileStore<RefreshToken> $store
     */
    public function __construct(
        private FileStore $store,
        FileRepositoryConfig $config,
        private ClockInterface $clock,
    ) {
        $this->filePath = $config->filePath . '/' . self::FILE_NAME;
    }

    public function findActive(RefreshTokenId $refreshTokenId): ?RefreshToken
    {
        $found = $this->store->get($this->filePath, $refreshTokenId->value);

        return is_null($found) || ! $found->isEnabled($this->clock->now())
            ? null
            : $found;
    }

    public function insert(RefreshToken $refreshToken): void
    {
        $this->store->put($this->filePath, $refreshToken->refreshTokenId->value, $refreshToken);
    }
}
