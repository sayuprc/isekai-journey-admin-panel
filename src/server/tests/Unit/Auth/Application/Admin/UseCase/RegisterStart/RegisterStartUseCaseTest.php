<?php

declare(strict_types=1);

namespace Tests\Unit\Auth\Application\Admin\UseCase\RegisterStart;

use AdminUser\Domain\Models\Email;
use AdminUser\Domain\Models\Permissions;
use AdminUser\Domain\Models\RegistrationToken\ConsumptionStatus;
use AdminUser\Domain\Models\RegistrationToken\ExpiredAt;
use AdminUser\Domain\Models\RegistrationToken\HashedTokenValue;
use AdminUser\Domain\Models\RegistrationToken\RegistrationToken;
use AdminUser\Domain\Models\RegistrationToken\RegistrationTokenId;
use AdminUser\Domain\Models\Role;
use AdminUser\Domain\Services\AdminUserIntegrityService;
use AdminUser\Domain\Services\RegistrationToken\RegistrationTokenConsumeService;
use Auth\Application\Admin\UseCase\RegisterStart\RegisterStartInputData;
use Auth\Application\Admin\UseCase\RegisterStart\RegisterStartUseCase;
use Auth\Domain\Models\PasskeyCeremonyState;
use Auth\Domain\Models\PasskeyCeremonyStoreInterface;
use Auth\Domain\Models\PasskeyCeremonyType;
use Auth\Domain\Services\PasskeyAuthenticatorInterface;
use Auth\Domain\Services\PasskeyStartResult;
use Auth\Domain\Services\PasskeyUserHandleGeneratorInterface;
use DateTimeImmutable;
use Mockery;
use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\Test;
use ResultType\Err;
use ResultType\Ok;
use Support\Contracts\Uuid\UuidGeneratorInterface;
use Support\Domain\Error\BusinessRuleViolationError;
use Support\UseCase\Error\BusinessLogicError;
use Tests\Support\Domain\EntityFactory;
use Tests\TestCase;

class RegisterStartUseCaseTest extends TestCase
{
    use EntityFactory;

    private MockInterface&RegistrationTokenConsumeService $consumeService;

    private AdminUserIntegrityService&MockInterface $integrityService;

    private MockInterface&PasskeyAuthenticatorInterface $passkeyAuthenticator;

    private MockInterface&PasskeyUserHandleGeneratorInterface $userHandleGenerator;

    private MockInterface&PasskeyCeremonyStoreInterface $ceremonyStore;

    private MockInterface&UuidGeneratorInterface $uuidGenerator;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->consumeService = Mockery::mock(RegistrationTokenConsumeService::class);
        $this->integrityService = Mockery::mock(AdminUserIntegrityService::class);
        $this->passkeyAuthenticator = Mockery::mock(PasskeyAuthenticatorInterface::class);
        $this->userHandleGenerator = Mockery::mock(PasskeyUserHandleGeneratorInterface::class);
        $this->ceremonyStore = Mockery::mock(PasskeyCeremonyStoreInterface::class);
        $this->uuidGenerator = Mockery::mock(UuidGeneratorInterface::class);
    }

    #[Test]
    public function canStartRegistration(): void
    {
        $adminUserId = 'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB';
        $authCeremonyId = 'CCCCCCCC-CCCC-CCCC-CCCC-CCCCCCCCCCCC';
        $token = $this->buildToken('invitee@example.com');
        $adminUser = $this->createAdminUser($adminUserId, 'invitee@example.com', name: '名前');

        $this->consumeService->shouldReceive('verify')
            ->withArgs(fn (string $plainToken, Email $email): bool => $plainToken === 'plain-token'
                && $email->value === 'invitee@example.com')
            ->andReturn(new Ok($token))
            ->once();

        $this->integrityService->shouldReceive('prepareForCreate')
            ->with('名前', 'invitee@example.com', Role::General->value, [])
            ->andReturn(new Ok($adminUser))
            ->once();

        $this->uuidGenerator->shouldReceive('generate')
            ->andReturn($authCeremonyId)
            ->once();

        $this->userHandleGenerator->shouldReceive('generate')
            ->andReturn('fixed-user-handle')
            ->once();

        $this->passkeyAuthenticator->shouldReceive('startRegistration')
            ->withArgs(fn (string $userHandle, string $userName, string $displayName): bool => $userHandle === 'fixed-user-handle'
                && $userName === 'invitee@example.com'
                && $displayName === '名前')
            ->andReturn(new PasskeyStartResult('{"challenge":"challenge"}', ['challenge' => 'challenge']))
            ->once();

        $this->ceremonyStore->shouldReceive('put')
            ->withArgs(fn (PasskeyCeremonyState $state): bool => $state->authCeremonyId === $authCeremonyId
                && $state->type === PasskeyCeremonyType::Register
                && $state->email === 'invitee@example.com'
                && $state->name === '名前'
                && $state->adminUserId === $adminUserId
                && $state->optionsJson === '{"challenge":"challenge"}')
            ->once();

        $result = $this->getInstance()->handle(new RegisterStartInputData('plain-token', 'invitee@example.com', '名前'));

        $this->assertTrue($result->isOk());
        $this->assertSame($authCeremonyId, $result->unwrap()->authCeremonyId);
        $this->assertSame(['challenge' => 'challenge'], $result->unwrap()->publicKey);
    }

    #[Test]
    public function doesNotStoreStateWhenTokenInvalid(): void
    {
        $this->consumeService->shouldReceive('verify')
            ->andReturn(new Err(new BusinessRuleViolationError('token_not_found')))
            ->once();
        $this->ceremonyStore->shouldReceive('put')->never();

        $result = $this->getInstance()->handle(new RegisterStartInputData('plain-token', 'invitee@example.com', '名前'));

        $this->assertTrue($result->isErr());
        $this->assertInstanceOf(BusinessLogicError::class, $result->unwrapErr());
    }

    private function buildToken(string $email): RegistrationToken
    {
        return new RegistrationToken(
            RegistrationTokenId::reconstruct('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'),
            HashedTokenValue::reconstruct('hashed'),
            Email::reconstruct($email),
            Role::General,
            Permissions::reconstruct([]),
            ExpiredAt::reconstruct(new DateTimeImmutable('+7 days')),
            ConsumptionStatus::Unused,
        );
    }

    private function getInstance(): RegisterStartUseCase
    {
        return new RegisterStartUseCase(
            $this->consumeService,
            $this->integrityService,
            $this->passkeyAuthenticator,
            $this->userHandleGenerator,
            $this->ceremonyStore,
            $this->uuidGenerator,
        );
    }
}
