<?php

declare(strict_types=1);

namespace Tests\Unit\Auth\Application\Interactors;

use Auth\Application\Interactors\LoginInteractor;
use Auth\Application\UseCase\Login\LoginInputData;
use Auth\Domain\Models\Credential\RefreshToken\ConsumptionStatus;
use Auth\Domain\Models\Credential\RefreshToken\RefreshToken;
use Auth\Domain\Models\Credential\RefreshToken\RefreshTokenRepositoryInterface;
use Auth\Domain\Services\Credential\AccessToken\AccessTokenIssueService;
use Auth\Domain\Services\Credential\RefreshToken\RefreshTokenIssueService;
use Carbon\Carbon;
use Closure;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use ResultType\Ok;
use Support\Contracts\TransactionInterface;
use Tests\Support\Domain\EntityFactory;
use Tests\TestCase;

class LoginInteractorTest extends TestCase
{
    use EntityFactory;

    private MockInterface&TransactionInterface $transaction;

    private MockInterface&RefreshTokenRepositoryInterface $refreshTokenRepository;

    private MockInterface&RefreshTokenIssueService $refreshTokenIssueService;

    private AccessTokenIssueService&MockInterface $accessTokenIssueService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->transaction = Mockery::mock(TransactionInterface::class);
        $this->refreshTokenRepository = Mockery::mock(RefreshTokenRepositoryInterface::class);
        $this->refreshTokenIssueService = Mockery::mock(RefreshTokenIssueService::class);
        $this->accessTokenIssueService = Mockery::mock(AccessTokenIssueService::class);
    }

    #[Test]
    public function canLogin(): void
    {
        Carbon::setTestNow('2019-12-09 10:30:00');

        $tokenId = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';
        $userId = 'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB';
        $token = 'token';

        $this->transaction->shouldReceive('scope')
            ->withArgs(fn (Closure $_) => true)
            ->andReturnUsing(fn (Closure $arg) => $arg())
            ->once();

        $now = now()->toDateTimeImmutable();

        $this->refreshTokenIssueService->shouldReceive('issue')
            ->with($userId)
            ->andReturn(new Ok($this->createRefreshToken($tokenId, $userId, $token, $now, ConsumptionStatus::Unused)))
            ->once();

        $this->accessTokenIssueService->shouldReceive('issue')
            ->with($tokenId)
            ->andReturn($this->createAccessToken(''))
            ->once();

        $this->refreshTokenRepository->shouldReceive('save')
            ->withArgs(
                fn (RefreshToken $arg) => $arg->refreshTokenId->value === $tokenId
                    && $arg->userId->value === $userId
                    && $arg->token->value === $token,
            )
            ->andReturnArg(0)
            ->once();

        $this->getInstance()->handle(new LoginInputData($userId));
    }

    private function getInstance(): LoginInteractor
    {
        return new LoginInteractor(
            $this->transaction,
            $this->refreshTokenRepository,
            $this->refreshTokenIssueService,
            $this->accessTokenIssueService,
        );
    }
}
