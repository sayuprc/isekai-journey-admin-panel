<?php

declare(strict_types=1);

namespace Tests\Unit\Auth\Application\Interactors;

use Auth\Application\Interactors\AuthenticateInteractor;
use Auth\Application\UseCase\Authenticate\AuthenticateInputData;
use Auth\Application\UseCase\Authenticate\AuthenticateUseCaseInterface;
use Auth\Domain\Models\Credential\RefreshToken\ConsumptionStatus;
use Auth\Domain\Models\Credential\RefreshToken\ExpiredAt;
use Auth\Domain\Models\Credential\RefreshToken\RefreshToken;
use Auth\Domain\Models\Credential\RefreshToken\RefreshTokenId;
use Auth\Domain\Models\Credential\RefreshToken\RefreshTokenRepositoryInterface;
use Auth\Domain\Models\Credential\RefreshToken\TokenValue;
use Auth\Domain\Services\Credential\AccessToken\AccessTokenPayload;
use Auth\Domain\Services\Credential\AccessToken\Exceptions\ExpiredException;
use Auth\Domain\Services\Credential\AccessToken\JwtHandlerInterface;
use DateTimeImmutable;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use User\Domain\Models\Email;
use User\Domain\Models\HashedPassword;
use User\Domain\Models\User;
use User\Domain\Models\UserId;
use User\Domain\Models\UserRepositoryInterface;

class AuthenticateInteractorTest extends TestCase
{
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
    public function isImplementsSpecificInterface(): void
    {
        $this->assertInstanceOf(AuthenticateUseCaseInterface::class, $this->getInstance());
    }

    #[Test]
    public function canAuthenticate(): void
    {
        $refreshTokenId = $this->generateUuid();

        $this->jwtHandler->shouldReceive('verify')
            ->with('access_token')
            ->andReturn(new AccessTokenPayload('', 0, 0, 0, $refreshTokenId))
            ->once();

        $userId = $this->generateUuid();

        $this->refreshTokenRepository->shouldReceive('findActive')
            ->with(Mockery::on(fn (RefreshTokenId $arg) => $arg->value === $refreshTokenId))
            ->andReturn(
                new RefreshToken(
                    new RefreshTokenId($refreshTokenId),
                    new UserId($userId),
                    new TokenValue('token'),
                    new ExpiredAt(new DateTimeImmutable()),
                    ConsumptionStatus::Unused,
                ),
            )
            ->once();

        $this->userRepository->shouldReceive('find')
            ->with(Mockery::on(fn (UserId $arg) => $arg->value === $userId))
            ->andReturn(new User(new UserId($userId), new Email('example@example.com'), new HashedPassword('')))
            ->once();

        $result = $this->getInstance()->handle(new AuthenticateInputData('access_token'));

        $this->assertTrue($result->isOk());
    }

    #[Test]
    public function unauthenticatedWhenExpireToken(): void
    {
        $this->jwtHandler->shouldReceive('verify')
            ->with('access_token')
            ->andThrow(new ExpiredException())
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
            ->andReturn(new AccessTokenPayload('', 0, 0, 0, $refreshTokenId))
            ->once();

        $this->refreshTokenRepository->shouldReceive('findActive')
            ->with(Mockery::on(fn (RefreshTokenId $arg) => $arg->value === $refreshTokenId))
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
            ->andReturn(new AccessTokenPayload('', 0, 0, 0, $refreshTokenId))
            ->once();

        $userId = $this->generateUuid();

        $this->refreshTokenRepository->shouldReceive('findActive')
            ->with(Mockery::on(fn (RefreshTokenId $arg) => $arg->value === $refreshTokenId))
            ->andReturn(
                new RefreshToken(
                    new RefreshTokenId($refreshTokenId),
                    new UserId($userId),
                    new TokenValue('token'),
                    new ExpiredAt(new DateTimeImmutable()),
                    ConsumptionStatus::Unused,
                ),
            )
            ->once();

        $this->userRepository->shouldReceive('find')
            ->with(Mockery::on(fn (UserId $arg) => $arg->value === $userId))
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
