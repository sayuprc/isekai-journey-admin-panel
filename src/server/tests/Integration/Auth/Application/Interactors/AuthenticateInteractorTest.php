<?php

declare(strict_types=1);

namespace Tests\Integration\Auth\Application\Interactors;

use Auth\Application\Interactors\AuthenticateInteractor;
use Auth\Application\UseCase\Authenticate\AuthenticateInputData;
use Auth\DebugInfrastructures\FileRefreshTokenRepository;
use Auth\Domain\Models\Credential\RefreshToken\ConsumptionStatus;
use Auth\Domain\Services\Credential\AccessToken\AccessTokenPayload;
use Auth\Infrastructures\Credential\AccessToken\JwtHandler;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\FileRepositoryTransaction;
use Tests\TestCase;
use User\DebugInfrastructures\FileUserRepository;

class AuthenticateInteractorTest extends TestCase
{
    use EntityFactory;
    use FileRepositoryTransaction;

    #[Test]
    public function canAuthenticate(): void
    {
        Carbon::setTestNow('2019-12-09 10:30:00');

        config()->set([
            'app.url' => 'issuer',
            'auth.jwt.alg' => 'HS256',
            'auth.jwt.key' => str_repeat('k', 256),
        ]);

        $user = $this->createUser($this->generateUuid(), 'example@example.com', 'password');
        $refreshToken = $this->createRefreshToken(
            $this->generateUuid(),
            $user->userId->value,
            'token',
            now()->addHour()->toDateTimeImmutable(),
            ConsumptionStatus::Unused,
        );
        $accessToken = $this->createAccessToken(
            $this->createJwt(
                new AccessTokenPayload(
                    'issuer',
                    now()->getTimestamp(),
                    now()->addMinutes(30)->getTimestamp(),
                    now()->getTimestamp(),
                    $refreshToken->refreshTokenId->value,
                ),
            ),
        );

        $this->factory(FileRefreshTokenRepository::class, $refreshToken->refreshTokenId->value, $refreshToken);
        $this->factory(FileUserRepository::class, $user->userId->value, $user);

        $result = $this->getInstance()->handle(new AuthenticateInputData($accessToken->jwt->value));

        $this->assertTrue($result->isOk());
    }

    #[Test]
    public function unauthenticatedWhenExpireToken(): void
    {
        config()->set([
            'auth.jwt.alg' => 'HS256',
            'auth.jwt.key' => str_repeat('k', 256),
        ]);

        $now = now()->toImmutable();
        CarbonImmutable::setTestNow($now->modify('-3 hours'));

        $user = $this->createUser($this->generateUuid(), 'example@example.com', 'password');
        $refreshToken = $this->createRefreshToken(
            $this->generateUuid(),
            $user->userId->value,
            'token',
            now()->subHour()->toDateTimeImmutable(),
            ConsumptionStatus::Unused,
        );
        $accessToken = $this->createAccessToken(
            $this->createJwt(
                new AccessTokenPayload(
                    'issuer',
                    now()->getTimestamp(),
                    now()->addMinutes(30)->getTimestamp(),
                    now()->getTimestamp(),
                    $refreshToken->refreshTokenId->value,
                ),
            ),
        );

        CarbonImmutable::setTestNow($now);

        $this->factory(FileRefreshTokenRepository::class, $refreshToken->refreshTokenId->value, $refreshToken);
        $this->factory(FileUserRepository::class, $user->userId->value, $user);

        $result = $this->getInstance()->handle(new AuthenticateInputData($accessToken->jwt->value));

        $this->assertTrue($result->isErr());
    }

    #[Test]
    public function unauthenticatedWhenCredentialNotFound(): void
    {
        config()->set([
            'auth.jwt.alg' => 'HS256',
            'auth.jwt.key' => str_repeat('k', 256),
        ]);

        $user = $this->createUser($this->generateUuid(), 'example@example.com', 'password');
        $refreshToken = $this->createRefreshToken(
            $this->generateUuid(),
            $user->userId->value,
            'token',
            now()->addHour()->toDateTimeImmutable(),
            ConsumptionStatus::Unused,
        );
        $accessToken = $this->createAccessToken(
            $this->createJwt(
                new AccessTokenPayload(
                    'issuer',
                    now()->getTimestamp(),
                    now()->addMinutes(30)->getTimestamp(),
                    now()->getTimestamp(),
                    $refreshToken->refreshTokenId->value,
                ),
            ),
        );

        $result = $this->getInstance()->handle(new AuthenticateInputData($accessToken->jwt->value));

        $this->assertTrue($result->isErr());
    }

    #[Test]
    public function unauthenticatedWhenUserNotFound(): void
    {
        config()->set([
            'auth.jwt.alg' => 'HS256',
            'auth.jwt.key' => str_repeat('k', 256),
        ]);

        $user = $this->createUser($this->generateUuid(), 'example@example.com', 'password');
        $refreshToken = $this->createRefreshToken(
            $this->generateUuid(),
            $user->userId->value,
            'token',
            now()->addHour()->toDateTimeImmutable(),
            ConsumptionStatus::Unused,
        );
        $accessToken = $this->createAccessToken(
            $this->createJwt(
                new AccessTokenPayload(
                    'issuer',
                    now()->getTimestamp(),
                    now()->addMinutes(30)->getTimestamp(),
                    now()->getTimestamp(),
                    $refreshToken->refreshTokenId->value,
                ),
            ),
        );

        $this->factory(FileRefreshTokenRepository::class, $refreshToken->refreshTokenId->value, $refreshToken);

        $result = $this->getInstance()->handle(new AuthenticateInputData($accessToken->jwt->value));

        $this->assertTrue($result->isErr());
    }

    private function createJwt(AccessTokenPayload $payload): string
    {
        return $this->app->make(JwtHandler::class)->generate($payload);
    }

    private function getInstance(): AuthenticateInteractor
    {
        return $this->app->make(AuthenticateInteractor::class);
    }
}
