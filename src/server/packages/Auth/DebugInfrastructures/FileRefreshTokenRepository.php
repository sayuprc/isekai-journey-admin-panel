<?php

declare(strict_types=1);

namespace Auth\DebugInfrastructures;

use Auth\Domain\Models\Credential\RefreshToken\RefreshToken;
use Auth\Domain\Models\Credential\RefreshToken\RefreshTokenId;
use Auth\Domain\Models\Credential\RefreshToken\RefreshTokenRepositoryInterface;
use Support\Contracts\ClockInterface;
use Support\Contracts\ConfigInterface;
use Support\DebugInfrastructures\Repository\FileStore;

readonly class FileRefreshTokenRepository implements RefreshTokenRepositoryInterface
{
    private const string FILE_NAME = 'refresh-tokens.dat';

    private string $filePath;

    /**
     * @param FileStore<RefreshToken> $store
     */
    public function __construct(
        private FileStore $store,
        private ConfigInterface $config,
        private ClockInterface $clock,
    ) {
        $this->filePath = $this->config->getString('debug.file.path') . '/' . self::FILE_NAME;
    }

    public function findActive(RefreshTokenId $refreshTokenId): ?RefreshToken
    {
        $found = $this->store->get($this->filePath, $refreshTokenId->value);

        return is_null($found) || ! $found->isAvailable($this->clock->now())
            ? null
            : $found;
    }

    public function save(RefreshToken $refreshToken): RefreshToken
    {
        $this->store->put($this->filePath, $refreshToken->refreshTokenId->value, $refreshToken);

        return $refreshToken;
    }
}
