<?php

declare(strict_types=1);

namespace Tests\Integration\Auth\DebugInfrastructures;

use Auth\DebugInfrastructures\FileRefreshTokenRepository;
use Auth\Domain\Models\Credential\RefreshToken\ConsumptionStatus;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\FileRepositoryTransaction;
use Tests\TestCase;

class FileRefreshTokenRepositoryTest extends TestCase
{
    use EntityFactory;
    use FileRepositoryTransaction;

    #[Test]
    public function findActive(): void
    {
        Carbon::setTestNow('2019-12-09 12:00:00');

        $refreshToken = $this->createRefreshToken(
            $this->generateUuid(),
            $this->generateUuid(),
            'token',
            now()->addMinutes(30)->toDateTimeImmutable(),
            ConsumptionStatus::Unused,
        );

        $this->storeRefreshTokens($refreshToken);

        $found = $this->getInstance()->findActive($refreshToken->refreshTokenId);

        $this->assertNotNull($found);
        $this->assertEquals($refreshToken, $found);
    }

    #[Test]
    public function save(): void
    {
        Carbon::setTestNow('2019-12-09 12:00:00');

        $refreshToken = $this->createRefreshToken(
            $this->generateUuid(),
            $this->generateUuid(),
            'token',
            now()->addMinutes(30)->toDateTimeImmutable(),
            ConsumptionStatus::Unused,
        );

        $this->getInstance()->save($refreshToken);

        $found = $this->getInstance()->findActive($refreshToken->refreshTokenId);

        $this->assertNotNull($found);
        $this->assertEquals($refreshToken, $found);
    }

    private function getInstance(): FileRefreshTokenRepository
    {
        return $this->app->make(FileRefreshTokenRepository::class);
    }
}
