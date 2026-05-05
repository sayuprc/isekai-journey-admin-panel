<?php

declare(strict_types=1);

namespace Tests\Unit\Auth\Application\UseCase;

use Auth\Application\UseCase\Login\LoginInputData;
use Auth\Application\UseCase\Login\LoginUseCase;
use Auth\Domain\Models\Token\RefreshToken\ConsumptionStatus;
use Auth\Domain\Models\Token\RefreshToken\RefreshToken;
use Auth\Domain\Models\Token\RefreshToken\RefreshTokenRepositoryInterface;
use Auth\Domain\Services\Token\AccessToken\AccessTokenIssueService;
use Auth\Domain\Services\Token\RefreshToken\RefreshTokenIssueService;
use Carbon\Carbon;
use Closure;
use Mockery;
use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\Test;
use ResultType\Ok;
use Support\UseCase\AuditLog\AuditLogRecorderInterface;
use Support\Contracts\TransactionInterface;
use Tests\Support\Domain\EntityFactory;
use Tests\TestCase;

class LoginUseCaseTest extends TestCase
{
    use EntityFactory;

    private MockInterface&TransactionInterface $transaction;

    private MockInterface&RefreshTokenRepositoryInterface $refreshTokenRepository;

    private MockInterface&RefreshTokenIssueService $refreshTokenIssueService;

    private AccessTokenIssueService&MockInterface $accessTokenIssueService;

    private AuditLogRecorderInterface&MockInterface $recorder;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->transaction = Mockery::mock(TransactionInterface::class);
        $this->refreshTokenRepository = Mockery::mock(RefreshTokenRepositoryInterface::class);
        $this->refreshTokenIssueService = Mockery::mock(RefreshTokenIssueService::class);
        $this->accessTokenIssueService = Mockery::mock(AccessTokenIssueService::class);
        $this->recorder = Mockery::mock(AuditLogRecorderInterface::class);
        $this->recorder->shouldReceive('record')->byDefault();
    }

    #[Test]
    public function canLogin(): void
    {
        Carbon::setTestNow('2019-12-09 10:30:00');

        $tokenId = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';
        $adminUserId = 'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB';
        $hashedToken = 'hashed-token';
        $plainToken = 'plain-token';

        $this->transaction->shouldReceive('scope')
            ->withArgs(fn (Closure $_) => true)
            ->andReturnUsing(fn (Closure $arg) => $arg())
            ->once();

        $now = now()->toDateTimeImmutable();

        $this->refreshTokenIssueService->shouldReceive('issue')
            ->with($adminUserId)
            ->andReturn(new Ok([
                'token' => $this->createRefreshToken($tokenId, $adminUserId, $hashedToken, $now, ConsumptionStatus::Unused),
                'plainToken' => $plainToken,
            ]))
            ->once();

        $this->accessTokenIssueService->shouldReceive('issue')
            ->with($tokenId)
            ->andReturn($this->createAccessToken(''))
            ->once();

        $this->refreshTokenRepository->shouldReceive('save')
            ->withArgs(
                fn (RefreshToken $arg) => $arg->refreshTokenId->value === $tokenId
                    && $arg->adminUserId->value === $adminUserId
                    && $arg->token->value === $hashedToken,
            )
            ->andReturnArg(0)
            ->once();

        $this->getInstance()->handle(new LoginInputData($adminUserId));
    }

    private function getInstance(): LoginUseCase
    {
        return new LoginUseCase(
            $this->transaction,
            $this->refreshTokenRepository,
            $this->refreshTokenIssueService,
            $this->accessTokenIssueService,
            $this->recorder,
        );
    }
}
