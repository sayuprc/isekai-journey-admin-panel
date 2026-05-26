<?php

declare(strict_types=1);

namespace Tests\Unit\AdminUser\Application\Admin\UseCase\RegisterFinish;

use AdminUser\Application\Admin\UseCase\RegisterFinish\RegisterFinishInputData;
use AdminUser\Application\Admin\UseCase\RegisterFinish\RegisterFinishUseCase;
use AdminUser\Domain\Models\AdminUser;
use AdminUser\Domain\Models\AdminUserRepositoryInterface;
use AdminUser\Domain\Models\Email;
use AdminUser\Domain\Models\Permissions;
use AdminUser\Domain\Models\RegistrationToken\ConsumptionStatus as RegistrationConsumptionStatus;
use AdminUser\Domain\Models\RegistrationToken\ExpiredAt as RegistrationExpiredAt;
use AdminUser\Domain\Models\RegistrationToken\HashedTokenValue as RegistrationHashedTokenValue;
use AdminUser\Domain\Models\RegistrationToken\RegistrationToken;
use AdminUser\Domain\Models\RegistrationToken\RegistrationTokenId;
use AdminUser\Domain\Models\RegistrationToken\RegistrationTokenRepositoryInterface;
use AdminUser\Domain\Models\Role;
use AdminUser\Domain\Services\AdminUserIntegrityService;
use AdminUser\Domain\Services\RegistrationToken\RegistrationTokenConsumeService;
use Auth\Domain\Models\AdminUserPasskey;
use Auth\Domain\Models\AdminUserPasskeyRepositoryInterface;
use Auth\Domain\Models\PasskeyCeremonyState;
use Auth\Domain\Models\PasskeyCeremonyStoreInterface;
use Auth\Domain\Models\PasskeyCeremonyType;
use Auth\Domain\Models\Token\RefreshToken\ConsumptionStatus as RefreshConsumptionStatus;
use Auth\Domain\Models\Token\RefreshToken\RefreshTokenRepositoryInterface;
use Auth\Domain\Services\PasskeyAuthenticatorInterface;
use Auth\Domain\Services\PasskeyRegistrationResult;
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
use RuntimeException;
use Support\Contracts\ClockInterface;
use Support\Contracts\TransactionInterface;
use Support\Contracts\Uuid\UuidGeneratorInterface;
use Support\Domain\Error\EntityRuleViolationError;
use Support\UseCase\Error\BusinessLogicError;
use Tests\Support\Domain\EntityFactory;
use Tests\TestCase;

class RegisterFinishUseCaseTest extends TestCase
{
    use EntityFactory;

    private MockInterface&TransactionInterface $transaction;

    private AdminUserRepositoryInterface&MockInterface $adminUserRepository;

    private AdminUserIntegrityService&MockInterface $integrityService;

    private MockInterface&RegistrationTokenConsumeService $consumeService;

    private MockInterface&RegistrationTokenRepositoryInterface $registrationTokenRepository;

    private MockInterface&PasskeyAuthenticatorInterface $passkeyAuthenticator;

    private MockInterface&PasskeyCeremonyStoreInterface $ceremonyStore;

    private AdminUserPasskeyRepositoryInterface&MockInterface $passkeyRepository;

    private MockInterface&RefreshTokenIssueService $refreshTokenIssueService;

    private AccessTokenIssueService&MockInterface $accessTokenIssueService;

    private MockInterface&RefreshTokenRepositoryInterface $refreshTokenRepository;

    private MockInterface&UuidGeneratorInterface $uuidGenerator;

    private ClockInterface&MockInterface $clock;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->transaction = Mockery::mock(TransactionInterface::class);
        $this->adminUserRepository = Mockery::mock(AdminUserRepositoryInterface::class);
        $this->integrityService = Mockery::mock(AdminUserIntegrityService::class);
        $this->consumeService = Mockery::mock(RegistrationTokenConsumeService::class);
        $this->registrationTokenRepository = Mockery::mock(RegistrationTokenRepositoryInterface::class);
        $this->passkeyAuthenticator = Mockery::mock(PasskeyAuthenticatorInterface::class);
        $this->ceremonyStore = Mockery::mock(PasskeyCeremonyStoreInterface::class);
        $this->passkeyRepository = Mockery::mock(AdminUserPasskeyRepositoryInterface::class);
        $this->refreshTokenIssueService = Mockery::mock(RefreshTokenIssueService::class);
        $this->accessTokenIssueService = Mockery::mock(AccessTokenIssueService::class);
        $this->refreshTokenRepository = Mockery::mock(RefreshTokenRepositoryInterface::class);
        $this->uuidGenerator = Mockery::mock(UuidGeneratorInterface::class);
        $this->clock = Mockery::mock(ClockInterface::class);
    }

    #[Test]
    public function canFinishRegistration(): void
    {
        $adminUserId = 'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB';
        $passkeyId = 'CCCCCCCC-CCCC-CCCC-CCCC-CCCCCCCCCCCC';
        $refreshTokenId = 'DDDDDDDD-DDDD-DDDD-DDDD-DDDDDDDDDDDD';
        $now = new DateTimeImmutable('2026-01-01 00:00:00');
        $state = new PasskeyCeremonyState('EEEEEEEE-EEEE-EEEE-EEEE-EEEEEEEEEEEE', PasskeyCeremonyType::Register, 'invitee@example.com', '名前', $adminUserId, '{"challenge":"challenge"}');
        $verification = new PasskeyRegistrationResult('credential-id', 'public-key', 'user-handle', '00000000-0000-0000-0000-000000000000', ['internal'], true, false, 123);
        $token = $this->buildToken('invitee@example.com');
        $adminUser = $this->createAdminUser($adminUserId, 'invitee@example.com', name: '名前');
        $refreshToken = $this->createRefreshToken($refreshTokenId, $adminUserId, 'hashed-refresh', new DateTimeImmutable('+7 days'), RefreshConsumptionStatus::Unused);
        $accessToken = $this->createAccessToken('jwt-value');

        $this->ceremonyStore->shouldReceive('pull')->with('EEEEEEEE-EEEE-EEEE-EEEE-EEEEEEEEEEEE')->andReturn($state)->once();
        $this->passkeyAuthenticator->shouldReceive('finishRegistration')
            ->with(['id' => 'credential-id'], '{"challenge":"challenge"}')
            ->andReturn($verification)
            ->once();
        $this->transaction->shouldReceive('scope')
            ->withArgs(fn (Closure $_): bool => true)
            ->andReturnUsing(fn (Closure $arg) => $arg())
            ->once();
        $this->consumeService->shouldReceive('verify')
            ->withArgs(fn (string $plainToken, Email $email): bool => $plainToken === 'plain-token'
                && $email->value === 'invitee@example.com')
            ->andReturn(new Ok($token))
            ->once();
        $this->integrityService->shouldReceive('prepareForCreateWithId')
            ->with($adminUserId, '名前', 'invitee@example.com', Role::General->value, [])
            ->andReturn(new Ok($adminUser))
            ->once();
        $this->adminUserRepository->shouldReceive('register')
            ->withArgs(fn (AdminUser $user): bool => $user->equals($adminUser))
            ->andReturn($adminUser)
            ->once();
        $this->uuidGenerator->shouldReceive('generate')->andReturn($passkeyId)->once();
        $this->clock->shouldReceive('now')->andReturn($now)->once();
        $this->passkeyRepository->shouldReceive('save')
            ->withArgs(fn (AdminUserPasskey $passkey): bool => $passkey->adminUserPasskeyId === $passkeyId
                && $passkey->adminUserId === $adminUserId
                && $passkey->userHandle === 'user-handle'
                && $passkey->credentialId === 'credential-id'
                && $passkey->aaguid === '00000000-0000-0000-0000-000000000000'
                && $passkey->transports === ['internal']
                && $passkey->backupEligible === true
                && $passkey->backupState === false)
            ->andReturnUsing(fn (AdminUserPasskey $passkey): AdminUserPasskey => $passkey)
            ->once();
        $this->registrationTokenRepository->shouldReceive('save')->andReturn($token->consume())->once();
        $this->refreshTokenIssueService->shouldReceive('issue')
            ->with($adminUserId)
            ->andReturn(new Ok(['token' => $refreshToken, 'plainToken' => 'plain-refresh']))
            ->once();
        $this->accessTokenIssueService->shouldReceive('issue')->with($refreshTokenId)->andReturn($accessToken)->once();
        $this->refreshTokenRepository->shouldReceive('save')->with($refreshToken)->andReturn($refreshToken)->once();

        $result = $this->getInstance()->handle(new RegisterFinishInputData('EEEEEEEE-EEEE-EEEE-EEEE-EEEEEEEEEEEE', 'plain-token', ['id' => 'credential-id']));

        $this->assertTrue($result->isOk());
        $this->assertSame('jwt-value', $result->unwrap()->accessToken->jwt->value);
    }

    #[Test]
    public function doesNotPersistWhenPasskeyVerificationFails(): void
    {
        $state = new PasskeyCeremonyState('EEEEEEEE-EEEE-EEEE-EEEE-EEEEEEEEEEEE', PasskeyCeremonyType::Register, 'invitee@example.com', '名前', 'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB', '{}');

        $this->ceremonyStore->shouldReceive('pull')->with('EEEEEEEE-EEEE-EEEE-EEEE-EEEEEEEEEEEE')->andReturn($state)->once();
        $this->passkeyAuthenticator->shouldReceive('finishRegistration')->andThrow(new RuntimeException('failed'))->once();
        $this->transaction->shouldReceive('scope')->never();
        $this->adminUserRepository->shouldReceive('register')->never();
        $this->passkeyRepository->shouldReceive('save')->never();
        $this->registrationTokenRepository->shouldReceive('save')->never();

        $result = $this->getInstance()->handle(new RegisterFinishInputData('EEEEEEEE-EEEE-EEEE-EEEE-EEEEEEEEEEEE', 'plain-token', ['id' => 'credential-id']));

        $this->assertTrue($result->isErr());
        $this->assertInstanceOf(BusinessLogicError::class, $result->unwrapErr());
    }

    #[Test]
    public function doesNotPersistWhenRefreshTokenIssueFails(): void
    {
        $adminUserId = 'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB';
        $state = new PasskeyCeremonyState('EEEEEEEE-EEEE-EEEE-EEEE-EEEEEEEEEEEE', PasskeyCeremonyType::Register, 'invitee@example.com', '名前', $adminUserId, '{"challenge":"challenge"}');
        $verification = new PasskeyRegistrationResult('credential-id', 'public-key', 'user-handle', '00000000-0000-0000-0000-000000000000', [], null, null, 123);
        $token = $this->buildToken('invitee@example.com');
        $adminUser = $this->createAdminUser($adminUserId, 'invitee@example.com', name: '名前');

        $this->ceremonyStore->shouldReceive('pull')->with('EEEEEEEE-EEEE-EEEE-EEEE-EEEEEEEEEEEE')->andReturn($state)->once();
        $this->passkeyAuthenticator->shouldReceive('finishRegistration')
            ->with(['id' => 'credential-id'], '{"challenge":"challenge"}')
            ->andReturn($verification)
            ->once();
        $this->transaction->shouldReceive('scope')
            ->withArgs(fn (Closure $_): bool => true)
            ->andReturnUsing(fn (Closure $arg) => $arg())
            ->once();
        $this->consumeService->shouldReceive('verify')
            ->withArgs(fn (string $plainToken, Email $email): bool => $plainToken === 'plain-token'
                && $email->value === 'invitee@example.com')
            ->andReturn(new Ok($token))
            ->once();
        $this->integrityService->shouldReceive('prepareForCreateWithId')
            ->with($adminUserId, '名前', 'invitee@example.com', Role::General->value, [])
            ->andReturn(new Ok($adminUser))
            ->once();
        $this->refreshTokenIssueService->shouldReceive('issue')
            ->with($adminUserId)
            ->andReturn(new Err(new EntityRuleViolationError('refresh_token', 'refresh token issue failed')))
            ->once();
        $this->adminUserRepository->shouldReceive('register')->never();
        $this->passkeyRepository->shouldReceive('save')->never();
        $this->registrationTokenRepository->shouldReceive('save')->never();
        $this->refreshTokenRepository->shouldReceive('save')->never();

        $result = $this->getInstance()->handle(new RegisterFinishInputData('EEEEEEEE-EEEE-EEEE-EEEE-EEEEEEEEEEEE', 'plain-token', ['id' => 'credential-id']));

        $this->assertTrue($result->isErr());
    }

    private function buildToken(string $email): RegistrationToken
    {
        return new RegistrationToken(
            RegistrationTokenId::reconstruct('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'),
            RegistrationHashedTokenValue::reconstruct('hashed'),
            Email::reconstruct($email),
            Role::General,
            Permissions::reconstruct([]),
            RegistrationExpiredAt::reconstruct(new DateTimeImmutable('+7 days')),
            RegistrationConsumptionStatus::Unused,
        );
    }

    private function getInstance(): RegisterFinishUseCase
    {
        return new RegisterFinishUseCase(
            $this->transaction,
            $this->adminUserRepository,
            $this->integrityService,
            $this->consumeService,
            $this->registrationTokenRepository,
            $this->passkeyAuthenticator,
            $this->ceremonyStore,
            $this->passkeyRepository,
            $this->refreshTokenIssueService,
            $this->accessTokenIssueService,
            $this->refreshTokenRepository,
            $this->uuidGenerator,
            $this->clock,
        );
    }
}
