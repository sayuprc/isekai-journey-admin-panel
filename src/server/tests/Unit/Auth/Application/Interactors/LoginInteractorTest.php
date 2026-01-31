<?php

declare(strict_types=1);

namespace Tests\Unit\Auth\Application\Interactors;

use Auth\Application\Interactors\LoginInteractor;
use Auth\Application\UseCase\Login\LoginInputData;
use Auth\Domain\Models\Credential\AccessToken\AccessTokenFactoryInterface;
use Auth\Domain\Models\Credential\RefreshToken\ConsumptionStatus;
use Auth\Domain\Models\Credential\RefreshToken\ExpiredAt;
use Auth\Domain\Models\Credential\RefreshToken\RefreshToken;
use Auth\Domain\Models\Credential\RefreshToken\RefreshTokenFactoryInterface;
use Auth\Domain\Models\Credential\RefreshToken\RefreshTokenId;
use Auth\Domain\Models\Credential\RefreshToken\RefreshTokenRepositoryInterface;
use Auth\Domain\Models\Credential\RefreshToken\TokenValue;
use Auth\Domain\Services\Credential\AccessToken\AccessTokenPayload;
use Auth\Domain\Services\Credential\AccessToken\JwtConfigInterface;
use Auth\Domain\Services\Credential\RefreshToken\RandomTokenGeneratorInterface;
use Carbon\Carbon;
use Closure;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Support\Contracts\ClockInterface;
use Support\Contracts\TransactionInterface;
use Support\Contracts\UuidGeneratorInterface;
use Tests\Support\Domain\EntityFactory;
use Tests\TestCase;
use User\Domain\Models\UserId;

class LoginInteractorTest extends TestCase
{
    use EntityFactory;

    private readonly MockInterface&TransactionInterface $transaction;

    private readonly MockInterface&RefreshTokenFactoryInterface $refreshTokenFactory;

    private readonly MockInterface&RefreshTokenRepositoryInterface $refreshTokenRepository;

    private readonly AccessTokenFactoryInterface&MockInterface $accessTokenFactory;

    private readonly MockInterface&UuidGeneratorInterface $uuidGenerator;

    private readonly MockInterface&RandomTokenGeneratorInterface $randomTokenGenerator;

    private readonly ClockInterface&MockInterface $clock;

    private readonly JwtConfigInterface&MockInterface $jwtConfig;

    protected function setUp(): void
    {
        parent::setUp();

        $this->transaction = Mockery::mock(TransactionInterface::class);
        $this->refreshTokenFactory = Mockery::mock(RefreshTokenFactoryInterface::class);
        $this->refreshTokenRepository = Mockery::mock(RefreshTokenRepositoryInterface::class);
        $this->accessTokenFactory = Mockery::mock(AccessTokenFactoryInterface::class);
        $this->uuidGenerator = Mockery::mock(UuidGeneratorInterface::class);
        $this->randomTokenGenerator = Mockery::mock(RandomTokenGeneratorInterface::class);
        $this->clock = Mockery::mock(ClockInterface::class);
        $this->jwtConfig = Mockery::mock(JwtConfigInterface::class);
    }

    #[Test]
    public function canLogin(): void
    {
        Carbon::setTestNow('2019-12-09 10:30:00');

        $this->transaction->shouldReceive('scope')
            ->with(Mockery::on(fn (Closure $_) => true))
            ->andReturnUsing(fn (Closure $arg) => $arg())
            ->once();

        $this->uuidGenerator->shouldReceive('generate')
            ->with()
            ->andReturn('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA')
            ->once();

        $this->randomTokenGenerator->shouldReceive('generate')
            ->with()
            ->andReturn('token')
            ->once();

        $now = now()->toDateTimeImmutable();

        $this->clock->shouldReceive('now')
            ->with()
            ->andReturn($now)
            ->twice();

        $this->refreshTokenFactory->shouldReceive('create')
            ->with(
                Mockery::on(fn (RefreshTokenId $arg): bool => $arg->value === 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'),
                Mockery::on(fn (UserId $arg): bool => $arg->value === 'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB'),
                Mockery::on(fn (TokenValue $arg): bool => $arg->value === 'token'),
                Mockery::on(fn (ExpiredAt $arg): bool => $arg->value->format('Y-m-d H:i:s') === '2019-12-16 10:30:00'),
                Mockery::on(fn (ConsumptionStatus $arg): bool => $arg === ConsumptionStatus::Unused),
            )
            ->andReturn(
                $refreshToken = $this->createRefreshToken(
                    'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA',
                    'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB',
                    'token',
                    $now->modify('+7 days'),
                    ConsumptionStatus::Unused,
                ),
            )
            ->once();

        $this->jwtConfig->shouldReceive('issuer')
            ->with()
            ->andReturn('issuer')
            ->once();

        $this->accessTokenFactory->shouldReceive('create')
            ->withArgs(function (AccessTokenPayload $arg) use ($now): bool {
                $payload = $arg->toArray();

                return $payload['iss'] === 'issuer'
                    && $payload['iat'] === $now->getTimestamp()
                    && $payload['exp'] === $now->modify('+1 hours')->getTimestamp()
                    && $payload['nbf'] === $now->getTimestamp()
                    && $payload['jti'] === 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';
            })
            ->andReturn($this->createAccessToken('jwt'))
            ->once();

        $this->refreshTokenRepository->shouldReceive('save')
            ->with(
                Mockery::on(
                    fn (RefreshToken $arg) => $arg->refreshTokenId->value === 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'
                        && $arg->userId->value === 'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB'
                        && $arg->token->value === 'token'
                        && $arg->isAvailable($now),
                ),
            )
            ->andReturn($refreshToken)
            ->once();

        $this->getInstance()->handle(new LoginInputData('BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB'));
    }

    private function getInstance(): LoginInteractor
    {
        return new LoginInteractor(
            $this->transaction,
            $this->refreshTokenFactory,
            $this->refreshTokenRepository,
            $this->accessTokenFactory,
            $this->uuidGenerator,
            $this->randomTokenGenerator,
            $this->clock,
            $this->jwtConfig,
        );
    }
}
