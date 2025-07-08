<?php

declare(strict_types=1);

namespace Tests\Unit\User\Application\Interactors;

use Closure;
use DateTimeImmutable;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Support\Contracts\TransactionInterface;
use Tests\TestCase;
use User\Application\Interactors\LoginInteractor;
use User\Application\UseCase\Login\LoginInputData;
use User\Domain\Models\Credential\AccessToken;
use User\Domain\Models\Credential\Credential;
use User\Domain\Models\Credential\CredentialFactoryInterface;
use User\Domain\Models\Credential\CredentialId;
use User\Domain\Models\Credential\CredentialRepositoryInterface;
use User\Domain\Models\Credential\ExpiredAt;
use User\Domain\Models\Credential\IsEnabled;
use User\Domain\Models\Credential\Jwt;
use User\Domain\Models\Credential\RefreshToken;
use User\Domain\Models\Credential\TokenValue;
use User\Domain\Models\UserId;

class LoginInteractorTest extends TestCase
{
    private readonly MockInterface&TransactionInterface $transaction;

    private readonly CredentialFactoryInterface&MockInterface $factory;

    private readonly CredentialRepositoryInterface&MockInterface $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->transaction = Mockery::mock(TransactionInterface::class);
        $this->factory = Mockery::mock(CredentialFactoryInterface::class);
        $this->repository = Mockery::mock(CredentialRepositoryInterface::class);
    }

    #[Test]
    public function canLogin(): void
    {
        $userId = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';

        $this->transaction->shouldReceive('scope')
            ->with(Mockery::on(fn (Closure $_) => true))
            ->andReturnUsing(fn (Closure $arg) => $arg())
            ->once();

        $now = new DateTimeImmutable();

        $this->factory->shouldReceive('create')
            ->with($userId)
            ->andReturnUsing(fn () => new Credential(
                new CredentialId('BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB'),
                new UserId($userId),
                new AccessToken(new Jwt('jwt')),
                new RefreshToken(new TokenValue('token'), new ExpiredAt($now), new IsEnabled(true))
            ))
            ->once();

        $this->repository->shouldReceive('insert')
            ->with(
                Mockery::on(
                    fn (Credential $arg) => $arg->credentialId->value === 'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB'
                        && $arg->userId->value === $userId
                        && $arg->accessToken->jwt->value === 'jwt'
                        && $arg->refreshToken->token->value === 'token'
                        && $arg->refreshToken->expiredAt->value->format('Y-m-d H:i:s') === $now->format('Y-m-d H:i:s')
                        && $arg->refreshToken->isEnabled->value === true
                )
            )
            ->once();

        $this->getInstance()->handle(new LoginInputData($userId));
    }

    private function getInstance(): LoginInteractor
    {
        return new LoginInteractor($this->transaction, $this->factory, $this->repository);
    }
}
