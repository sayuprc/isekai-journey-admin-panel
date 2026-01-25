<?php

declare(strict_types=1);

namespace Tests\Integration\Auth\Application\Interactors;

use Auth\Application\Interactors\LoginInteractor;
use Auth\Application\UseCase\Login\LoginInputData;
use Auth\DebugInfrastructures\FileRefreshTokenRepository;
use Auth\Domain\Models\Credential\RefreshToken\RefreshToken;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\FileRepositoryTransaction;
use Tests\TestCase;

class LoginInteractorTest extends TestCase
{
    use FileRepositoryTransaction;

    #[Test]
    public function canLogin(): void
    {
        config()->set([
            'auth.jwt.alg' => 'HS256',
            'auth.jwt.key' => str_repeat('k', 256),
        ]);

        CarbonImmutable::setTestNow('2019-12-02 12:34:29');

        $now = new CarbonImmutable();

        $userId = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';

        $this->getInstance()->handle(new LoginInputData($userId));

        /** @var array<RefreshToken> */
        $refreshTokens = $this->getAll(FileRefreshTokenRepository::class);
        $this->assertCount(1, $refreshTokens);
        $this->assertSame($userId, $refreshTokens[array_key_first($refreshTokens)]->userId->value);
        $this->assertTrue($refreshTokens[array_key_first($refreshTokens)]->isAvailable($now));
    }

    private function getInstance(): LoginInteractor
    {
        return $this->app->make(LoginInteractor::class);
    }
}
