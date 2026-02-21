<?php

declare(strict_types=1);

namespace Tests\Integration\Auth\Application\Interactors;

use AdminUser\DebugInfrastructures\FileAdminUserRepository;
use Auth\Application\Interactors\AuthenticateInteractor;
use Auth\Application\UseCase\Authenticate\AuthenticateInputData;
use Auth\DebugInfrastructures\FileRefreshTokenRepository;
use Auth\Domain\Models\Token\RefreshToken\ConsumptionStatus;
use Auth\Domain\Services\Token\AccessToken\AccessTokenPayload;
use Auth\Infrastructures\Token\AccessToken\JwtHandler;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\FileRepositoryTransaction;
use Tests\TestCase;

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

        $user = $this->createUser($this->generateUuid(), 'example@example.com');
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

        $this->factory(FileRefreshTokenRepository::class, $refreshToken->toArray());
        $this->factory(FileAdminUserRepository::class, $user->toArray());

        $result = $this->getInstance()->handle(new AuthenticateInputData($accessToken->jwt->value));

        $this->assertTrue($result->isOk());
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
