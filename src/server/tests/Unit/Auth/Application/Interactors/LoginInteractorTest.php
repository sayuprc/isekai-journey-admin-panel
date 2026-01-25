<?php

declare(strict_types=1);

namespace Tests\Unit\Auth\Application\Interactors;

use Auth\Application\Interactors\LoginInteractor;
use Auth\Application\UseCase\Login\LoginInputData;
use Auth\Domain\Models\Credential\AccessToken\AccessToken;
use Auth\Domain\Models\Credential\AccessToken\AccessTokenFactoryInterface;
use Auth\Domain\Models\Credential\AccessToken\Jwt;
use Auth\Domain\Models\Credential\RefreshToken\ConsumptionStatus;
use Auth\Domain\Models\Credential\RefreshToken\ExpiredAt;
use Auth\Domain\Models\Credential\RefreshToken\RefreshToken;
use Auth\Domain\Models\Credential\RefreshToken\RefreshTokenFactoryInterface;
use Auth\Domain\Models\Credential\RefreshToken\RefreshTokenId;
use Auth\Domain\Models\Credential\RefreshToken\RefreshTokenRepositoryInterface;
use Auth\Domain\Models\Credential\RefreshToken\TokenValue;
use Closure;
use DateTimeImmutable;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Support\Contracts\TransactionInterface;
use Tests\TestCase;
use User\Domain\Models\UserId;

class LoginInteractorTest extends TestCase
{
    private readonly MockInterface&TransactionInterface $transaction;

    private readonly MockInterface&RefreshTokenFactoryInterface $refreshTokenFactory;

    private readonly MockInterface&RefreshTokenRepositoryInterface $refreshTokenRepository;

    private readonly AccessTokenFactoryInterface&MockInterface $accessTokenFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->transaction = Mockery::mock(TransactionInterface::class);
        $this->refreshTokenFactory = Mockery::mock(RefreshTokenFactoryInterface::class);
        $this->refreshTokenRepository = Mockery::mock(RefreshTokenRepositoryInterface::class);
        $this->accessTokenFactory = Mockery::mock(AccessTokenFactoryInterface::class);
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

        $this->refreshTokenFactory->shouldReceive('create')
            ->with($userId)
            ->andReturn($refreshToken = new RefreshToken(
                new RefreshTokenId('BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB'),
                new UserId($userId),
                new TokenValue('token'),
                new ExpiredAt($now->modify('+ 7 days')),
                ConsumptionStatus::Unused,
            ))
            ->once();

        $this->accessTokenFactory->shouldReceive('create')
            ->with('BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB')
            ->andReturn(new AccessToken(new Jwt('jwt')))
            ->once();

        $this->refreshTokenRepository->shouldReceive('save')
            ->with(
                Mockery::on(
                    fn (RefreshToken $arg) => $arg->refreshTokenId->value === 'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB'
                        && $arg->userId->value === $userId
                        && $arg->token->value === 'token'
                        && $arg->isAvailable($now),
                ),
            )
            ->andReturn($refreshToken)
            ->once();

        $this->getInstance()->handle(new LoginInputData($userId));
    }

    private function getInstance(): LoginInteractor
    {
        return new LoginInteractor(
            $this->transaction,
            $this->refreshTokenFactory,
            $this->refreshTokenRepository,
            $this->accessTokenFactory,
        );
    }
}
