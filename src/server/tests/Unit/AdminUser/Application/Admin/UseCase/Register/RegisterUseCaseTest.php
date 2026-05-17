<?php

declare(strict_types=1);

namespace Tests\Unit\AdminUser\Application\Admin\UseCase\Register;

use AdminUser\Application\Admin\UseCase\Register\RegisterInputData;
use AdminUser\Application\Admin\UseCase\Register\RegisterUseCase;
use AdminUser\Domain\Models\AdminUser;
use AdminUser\Domain\Models\AdminUserRepositoryInterface;
use AdminUser\Domain\Models\Email;
use AdminUser\Domain\Models\HashedPassword;
use AdminUser\Domain\Models\Permissions;
use AdminUser\Domain\Models\RegistrationToken\ConsumptionStatus as RegistrationConsumptionStatus;
use AdminUser\Domain\Models\RegistrationToken\ExpiredAt as RegistrationExpiredAt;
use AdminUser\Domain\Models\RegistrationToken\HashedTokenValue as RegistrationHashedTokenValue;
use AdminUser\Domain\Models\RegistrationToken\RegistrationToken;
use AdminUser\Domain\Models\RegistrationToken\RegistrationTokenId;
use AdminUser\Domain\Models\RegistrationToken\RegistrationTokenRepositoryInterface;
use AdminUser\Domain\Models\Role;
use AdminUser\Domain\Services\AdminUserIntegrityService;
use AdminUser\Domain\Services\HasherInterface;
use AdminUser\Domain\Services\RegistrationToken\RegistrationTokenConsumeService;
use Auth\Domain\Models\Token\RefreshToken\ConsumptionStatus as RefreshConsumptionStatus;
use Auth\Domain\Models\Token\RefreshToken\RefreshToken;
use Auth\Domain\Models\Token\RefreshToken\RefreshTokenRepositoryInterface;
use Auth\Domain\Services\Token\AccessToken\AccessTokenIssueService;
use Auth\Domain\Services\Token\RefreshToken\RefreshTokenIssueService;
use Closure;
use DateTimeImmutable;
use Mockery;
use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\Test;
use ResultType\Err;
use ResultType\Ok;
use Support\Contracts\TransactionInterface;
use Support\Domain\Error\BusinessRuleViolationError;
use Support\Domain\Error\DomainValidationError;
use Support\UseCase\Error\BusinessLogicError;
use Support\UseCase\Error\InvalidInputError;
use Tests\Support\Domain\EntityFactory;
use Tests\TestCase;

class RegisterUseCaseTest extends TestCase
{
    use EntityFactory;

    private MockInterface&TransactionInterface $transaction;

    private HasherInterface&MockInterface $hasher;

    private AdminUserRepositoryInterface&MockInterface $adminUserRepository;

    private AdminUserIntegrityService&MockInterface $integrityService;

    private MockInterface&RegistrationTokenConsumeService $consumeService;

    private MockInterface&RegistrationTokenRepositoryInterface $registrationTokenRepository;

    private MockInterface&RefreshTokenIssueService $refreshTokenIssueService;

    private AccessTokenIssueService&MockInterface $accessTokenIssueService;

    private MockInterface&RefreshTokenRepositoryInterface $refreshTokenRepository;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->transaction = Mockery::mock(TransactionInterface::class);
        $this->hasher = Mockery::mock(HasherInterface::class);
        $this->adminUserRepository = Mockery::mock(AdminUserRepositoryInterface::class);
        $this->integrityService = Mockery::mock(AdminUserIntegrityService::class);
        $this->consumeService = Mockery::mock(RegistrationTokenConsumeService::class);
        $this->registrationTokenRepository = Mockery::mock(RegistrationTokenRepositoryInterface::class);
        $this->refreshTokenIssueService = Mockery::mock(RefreshTokenIssueService::class);
        $this->accessTokenIssueService = Mockery::mock(AccessTokenIssueService::class);
        $this->refreshTokenRepository = Mockery::mock(RefreshTokenRepositoryInterface::class);
    }

    #[Test]
    public function canRegister(): void
    {
        $adminUserId = 'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB';
        $refreshTokenId = 'CCCCCCCC-CCCC-CCCC-CCCC-CCCCCCCCCCCC';

        $token = $this->buildToken('invitee@example.com', RegistrationConsumptionStatus::Unused);
        $adminUser = $this->createAdminUser($adminUserId, 'invitee@example.com');

        $refreshToken = $this->createRefreshToken(
            $refreshTokenId,
            $adminUserId,
            'hashed-refresh',
            new DateTimeImmutable('+7 days'),
            RefreshConsumptionStatus::Unused,
        );
        $accessToken = $this->createAccessToken('jwt-value');

        $this->expectTransactionScope();

        $this->consumeService->shouldReceive('verify')
            ->withArgs(fn (string $plainToken, Email $email): bool => $plainToken === 'plain-token'
                && $email->value === 'invitee@example.com')
            ->andReturn(new Ok($token))
            ->once();

        $this->integrityService->shouldReceive('prepareForCreate')
            ->withArgs(fn (string $name, string $email, int $role, array $permissions): bool => $name === '名前'
                && $email === 'invitee@example.com'
                && $role === Role::General->value
                && $permissions === [])
            ->andReturn(new Ok($adminUser))
            ->once();

        $this->hasher->shouldReceive('hash')
            ->with('password')
            ->andReturn('hashed-password')
            ->once();

        $this->adminUserRepository->shouldReceive('register')
            ->withArgs(fn (AdminUser $user, HashedPassword $password): bool => $user->equals($adminUser)
                && $password->value === 'hashed-password')
            ->andReturn($adminUser)
            ->once();

        $this->registrationTokenRepository->shouldReceive('save')
            ->withArgs(fn (RegistrationToken $arg): bool => $arg->status === RegistrationConsumptionStatus::Consumed
                && $arg->equals($token))
            ->andReturn($token->consume())
            ->once();

        $this->refreshTokenIssueService->shouldReceive('issue')
            ->with($adminUserId)
            ->andReturn(new Ok([
                'token' => $refreshToken,
                'plainToken' => 'plain-refresh',
            ]))
            ->once();

        $this->accessTokenIssueService->shouldReceive('issue')
            ->with($refreshTokenId)
            ->andReturn($accessToken)
            ->once();

        $this->refreshTokenRepository->shouldReceive('save')
            ->withArgs(fn (RefreshToken $arg): bool => $arg->refreshTokenId->value === $refreshTokenId)
            ->andReturn($refreshToken)
            ->once();

        $result = $this->getInstance()->handle(new RegisterInputData('plain-token', 'invitee@example.com', '名前', 'password'));

        $this->assertTrue($result->isOk());
        $output = $result->unwrap();
        $this->assertSame('jwt-value', $output->accessToken->jwt->value);
        $this->assertSame($refreshTokenId, $output->refreshTokenId);
        $this->assertSame('plain-refresh', $output->plainRefreshToken);
    }

    #[Test]
    public function registerFailsWhenTokenInvalid(): void
    {
        $this->expectTransactionScope();

        $this->consumeService->shouldReceive('verify')
            ->andReturn(new Err(new BusinessRuleViolationError('token_not_found')))
            ->once();

        $result = $this->getInstance()->handle(new RegisterInputData('plain-token', 'invitee@example.com', '名前', 'password'));

        $this->assertTrue($result->isErr());
        $this->assertInstanceOf(BusinessLogicError::class, $result->unwrapErr());
    }

    #[Test]
    public function registerFailsWhenEmailAlreadyExists(): void
    {
        $token = $this->buildToken('exists@example.com', RegistrationConsumptionStatus::Unused);

        $this->expectTransactionScope();

        $this->consumeService->shouldReceive('verify')
            ->andReturn(new Ok($token))
            ->once();

        $this->integrityService->shouldReceive('prepareForCreate')
            ->andReturn(new Err(new BusinessRuleViolationError('既に存在')))
            ->once();

        $result = $this->getInstance()->handle(new RegisterInputData('plain-token', 'exists@example.com', '名前', 'password'));

        $this->assertTrue($result->isErr());
        $this->assertInstanceOf(BusinessLogicError::class, $result->unwrapErr());
    }

    #[Test]
    public function registerFailsWhenIntegrityValidationErr(): void
    {
        $token = $this->buildToken('invitee@example.com', RegistrationConsumptionStatus::Unused);

        $this->expectTransactionScope();

        $this->consumeService->shouldReceive('verify')
            ->andReturn(new Ok($token))
            ->once();

        $this->integrityService->shouldReceive('prepareForCreate')
            ->andReturn(new Err(new DomainValidationError(['name' => ['必須']])))
            ->once();

        $result = $this->getInstance()->handle(new RegisterInputData('plain-token', 'invitee@example.com', '', 'password'));

        $this->assertTrue($result->isErr());
        $this->assertInstanceOf(InvalidInputError::class, $result->unwrapErr());
    }

    private function buildToken(string $email, RegistrationConsumptionStatus $status): RegistrationToken
    {
        return new RegistrationToken(
            RegistrationTokenId::reconstruct('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'),
            RegistrationHashedTokenValue::reconstruct('hashed'),
            Email::reconstruct($email),
            Role::General,
            Permissions::reconstruct([]),
            RegistrationExpiredAt::reconstruct(new DateTimeImmutable('+7 days')),
            $status,
        );
    }

    private function expectTransactionScope(): void
    {
        $this->transaction->shouldReceive('scope')
            ->withArgs(fn (Closure $_) => true)
            ->andReturnUsing(fn (Closure $arg) => $arg())
            ->once();
    }

    private function getInstance(): RegisterUseCase
    {
        return new RegisterUseCase(
            $this->transaction,
            $this->hasher,
            $this->adminUserRepository,
            $this->integrityService,
            $this->consumeService,
            $this->registrationTokenRepository,
            $this->refreshTokenIssueService,
            $this->accessTokenIssueService,
            $this->refreshTokenRepository,
        );
    }
}
