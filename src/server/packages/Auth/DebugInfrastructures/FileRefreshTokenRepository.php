<?php

declare(strict_types=1);

namespace Auth\DebugInfrastructures;

use Auth\Domain\Models\Token\RefreshToken\RefreshToken;
use Auth\Domain\Models\Token\RefreshToken\RefreshTokenId;
use Auth\Domain\Models\Token\RefreshToken\RefreshTokenRepositoryInterface;
use Override;
use Support\Contracts\ClockInterface;
use Support\Contracts\MapperInterface;
use Support\DebugInfrastructures\Repository\DebugConfig;
use Support\DebugInfrastructures\Repository\JsonFileStore;

readonly class FileRefreshTokenRepository implements RefreshTokenRepositoryInterface
{
    private const string FILE_NAME = 'refresh-tokens';

    private string $filePath;

    public function __construct(
        private MapperInterface $mapper,
        private JsonFileStore $store,
        private ClockInterface $clock,
        DebugConfig $config,
    ) {
        $this->filePath = $config->path . '/' . self::FILE_NAME;
    }

    #[Override]
    public function findActive(RefreshTokenId $refreshTokenId): ?RefreshToken
    {
        $found = array_first(
            array_filter(
                $this->loadAll(),
                fn (RefreshToken $token): bool => $token->refreshTokenId->equals($refreshTokenId),
            ),
        );

        return is_null($found) || ! $found->isAvailable($this->clock->now())
            ? null
            : $found;
    }

    #[Override]
    public function save(RefreshToken $refreshToken): RefreshToken
    {
        $data = $refreshToken->toArray();

        $this->store->save(
            $this->filePath,
            $data,
            array_keys(
                array_filter(
                    $this->loadAll(),
                    fn (RefreshToken $item): bool => $item->equals($refreshToken),
                ),
            )[0] ?? null,
        );

        return $refreshToken;
    }

    /**
     * @return array<RefreshToken>
     */
    private function loadAll(): array
    {
        $class = RefreshToken::class;

        /** @var array<RefreshToken> */
        return $this->mapper->map("array<{$class}>", $this->store->load($this->filePath));
    }
}
