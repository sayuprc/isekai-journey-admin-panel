<?php

declare(strict_types=1);

namespace Tests\Unit\User\Application\Interactors;

use DateTimeImmutable;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use User\Application\Interactors\AuthenticateInteractor;
use User\Application\UseCase\Authenticate\AuthenticateInputData;
use User\Application\UseCase\Authenticate\AuthenticateUseCaseInterface;
use User\Domain\Models\Credential\AccessToken;
use User\Domain\Models\Credential\Credential;
use User\Domain\Models\Credential\CredentialId;
use User\Domain\Models\Credential\CredentialRepositoryInterface;
use User\Domain\Models\Credential\ExpiredAt;
use User\Domain\Models\Credential\IsEnabled;
use User\Domain\Models\Credential\Jwt;
use User\Domain\Models\Credential\RefreshToken;
use User\Domain\Models\Credential\TokenValue;
use User\Domain\Models\Email;
use User\Domain\Models\HashedPassword;
use User\Domain\Models\User;
use User\Domain\Models\UserId;
use User\Domain\Models\UserRepositoryInterface;
use User\Domain\Services\Jwt\AccessTokenPayload;
use User\Domain\Services\Jwt\Exceptions\ExpiredException;
use User\Domain\Services\Jwt\JwtHandlerInterface;

class AuthenticateInteractorTest extends TestCase
{
    private JwtHandlerInterface&MockInterface $jwtHandler;

    private CredentialRepositoryInterface&MockInterface $credentialRepository;

    private MockInterface&UserRepositoryInterface $userRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->jwtHandler = Mockery::mock(JwtHandlerInterface::class);
        $this->credentialRepository = Mockery::mock(CredentialRepositoryInterface::class);
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
        $credentialId = $this->generateUuid();

        $this->jwtHandler->shouldReceive('decode')
            ->with('access_token')
            ->andReturn(new AccessTokenPayload('', 0, 0, 0, $credentialId))
            ->once();

        $userId = $this->generateUuid();

        $this->credentialRepository->shouldReceive('findActive')
            ->with(Mockery::on(fn (CredentialId $arg) => $arg->value === $credentialId))
            ->andReturn(
                new Credential(
                    new CredentialId($credentialId),
                    new UserId($userId),
                    new AccessToken(new Jwt('')),
                    new RefreshToken(new TokenValue(''), new ExpiredAt(new DateTimeImmutable())),
                    new IsEnabled(true)
                )
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
        $this->jwtHandler->shouldReceive('decode')
            ->with('access_token')
            ->andThrow(new ExpiredException())
            ->once();

        $result = $this->getInstance()->handle(new AuthenticateInputData('access_token'));

        $this->assertTrue($result->isErr());
    }

    #[Test]
    public function unauthenticatedWhenCredentialNotFound(): void
    {
        $credentialId = $this->generateUuid();

        $this->jwtHandler->shouldReceive('decode')
            ->with('access_token')
            ->andReturn(new AccessTokenPayload('', 0, 0, 0, $credentialId))
            ->once();

        $this->credentialRepository->shouldReceive('findActive')
            ->with(Mockery::on(fn (CredentialId $arg) => $arg->value === $credentialId))
            ->andReturnNull()
            ->once();

        $result = $this->getInstance()->handle(new AuthenticateInputData('access_token'));

        $this->assertTrue($result->isErr());
    }

    #[Test]
    public function unauthenticatedWhenUserNotFound(): void
    {
        $credentialId = $this->generateUuid();

        $this->jwtHandler->shouldReceive('decode')
            ->with('access_token')
            ->andReturn(new AccessTokenPayload('', 0, 0, 0, $credentialId))
            ->once();

        $userId = $this->generateUuid();

        $this->credentialRepository->shouldReceive('findActive')
            ->with(Mockery::on(fn (CredentialId $arg) => $arg->value === $credentialId))
            ->andReturn(
                new Credential(
                    new CredentialId($credentialId),
                    new UserId($userId),
                    new AccessToken(new Jwt('')),
                    new RefreshToken(new TokenValue(''), new ExpiredAt(new DateTimeImmutable())),
                    new IsEnabled(true)
                )
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
        return new AuthenticateInteractor($this->jwtHandler, $this->credentialRepository, $this->userRepository);
    }
}
