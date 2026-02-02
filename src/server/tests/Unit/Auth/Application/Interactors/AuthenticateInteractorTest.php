<?php

declare(strict_types=1);

namespace Tests\Unit\Auth\Application\Interactors;

use Auth\Application\Interactors\AuthenticateInteractor;
use Auth\Application\UseCase\Authenticate\AuthenticateInputData;
use Auth\Domain\Models\Credential\RefreshToken\ConsumptionStatus;
use Auth\Domain\Models\Credential\RefreshToken\RefreshTokenId;
use Auth\Domain\Models\Credential\RefreshToken\RefreshTokenRepositoryInterface;
use Auth\Domain\Services\Credential\AccessToken\AccessTokenPayload;
use Auth\Domain\Services\Credential\AccessToken\JwtHandlerInterface;
use DateTimeImmutable;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use ResultType\Err;
use ResultType\Ok;
use Tests\Support\Domain\EntityFactory;
use Tests\TestCase;
use User\Domain\Models\UserId;
use User\Domain\Models\UserRepositoryInterface;

class AuthenticateInteractorTest extends TestCase
{
    use EntityFactory;

    private JwtHandlerInterface&MockInterface $jwtHandler;

    private MockInterface&RefreshTokenRepositoryInterface $refreshTokenRepository;

    private MockInterface&UserRepositoryInterface $userRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->jwtHandler = Mockery::mock(JwtHandlerInterface::class);
        $this->refreshTokenRepository = Mockery::mock(RefreshTokenRepositoryInterface::class);
        $this->userRepository = Mockery::mock(UserRepositoryInterface::class);
    }

    #[Test]
    public function canAuthenticate(): void
    {
        $refreshTokenId = $this->generateUuid();

        $this->jwtHandler->shouldReceive('verify')
            ->with('access_token')
            ->andReturn(new Ok(new AccessTokenPayload('', 0, 0, 0, $refreshTokenId)))
            ->once();

        $userId = $this->generateUuid();

        $this->refreshTokenRepository->shouldReceive('findActive')
            ->withArgs(fn (RefreshTokenId $arg) => $arg->value === $refreshTokenId)
            ->andReturn(
                $this->createRefreshToken(
                    $refreshTokenId,
                    $userId,
                    'token',
                    new DateTimeImmutable(),
                    ConsumptionStatus::Unused,
                ),
            )
            ->once();

        $this->userRepository->shouldReceive('find')
            ->withArgs(fn (UserId $arg) => $arg->value === $userId)
            ->andReturn($this->createUser($userId, 'example@example.com', ''))
            ->once();

        $result = $this->getInstance()->handle(new AuthenticateInputData('access_token'));

        $this->assertTrue($result->isOk());
    }

    #[Test]
    public function unauthenticatedWhenExpireToken(): void
    {
        $this->jwtHandler->shouldReceive('verify')
            ->with('access_token')
            ->andReturn(new Err(''))
            ->once();

        $result = $this->getInstance()->handle(new AuthenticateInputData('access_token'));

        $this->assertTrue($result->isErr());
    }

    #[Test]
    public function unauthenticatedWhenCredentialNotFound(): void
    {
        $refreshTokenId = $this->generateUuid();

        $this->jwtHandler->shouldReceive('verify')
            ->with('access_token')
            ->andReturn(new Ok(new AccessTokenPayload('', 0, 0, 0, $refreshTokenId)))
            ->once();

        $this->refreshTokenRepository->shouldReceive('findActive')
            ->withArgs(fn (RefreshTokenId $arg) => $arg->value === $refreshTokenId)
            ->andReturnNull()
            ->once();

        $result = $this->getInstance()->handle(new AuthenticateInputData('access_token'));

        $this->assertTrue($result->isErr());
    }

    #[Test]
    public function unauthenticatedWhenUserNotFound(): void
    {
        $refreshTokenId = $this->generateUuid();

        $this->jwtHandler->shouldReceive('verify')
            ->with('access_token')
            ->andReturn(new Ok(new AccessTokenPayload('', 0, 0, 0, $refreshTokenId)))
            ->once();

        $userId = $this->generateUuid();

        $this->refreshTokenRepository->shouldReceive('findActive')
            ->withArgs(fn (RefreshTokenId $arg) => $arg->value === $refreshTokenId)
            ->andReturn(
                $this->createRefreshToken(
                    $refreshTokenId,
                    $userId,
                    'token',
                    new DateTimeImmutable(),
                    ConsumptionStatus::Unused,
                ),
            )
            ->once();

        $this->userRepository->shouldReceive('find')
            ->withArgs(fn (UserId $arg) => $arg->value === $userId)
            ->andReturnNull()
            ->once();

        $result = $this->getInstance()->handle(new AuthenticateInputData('access_token'));

        $this->assertTrue($result->isErr());
    }

    private function getInstance(): AuthenticateInteractor
    {
        return new AuthenticateInteractor($this->jwtHandler, $this->refreshTokenRepository, $this->userRepository);
    }
}
