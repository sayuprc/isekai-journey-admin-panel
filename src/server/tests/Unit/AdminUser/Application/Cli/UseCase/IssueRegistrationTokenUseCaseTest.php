<?php

declare(strict_types=1);

namespace Tests\Unit\AdminUser\Application\Cli\UseCase;

use AdminUser\Application\Cli\UseCase\IssueRegistrationToken\IssueRegistrationTokenInputData;
use AdminUser\Application\Cli\UseCase\IssueRegistrationToken\IssueRegistrationTokenUseCase;
use AdminUser\Domain\Models\AdminUserRepositoryInterface;
use AdminUser\Domain\Models\Email;
use AdminUser\Domain\Models\Permissions;
use AdminUser\Domain\Models\RegistrationToken\ConsumptionStatus;
use AdminUser\Domain\Models\RegistrationToken\ExpiredAt;
use AdminUser\Domain\Models\RegistrationToken\HashedTokenValue;
use AdminUser\Domain\Models\RegistrationToken\RegistrationToken;
use AdminUser\Domain\Models\RegistrationToken\RegistrationTokenId;
use AdminUser\Domain\Models\RegistrationToken\RegistrationTokenRepositoryInterface;
use AdminUser\Domain\Models\Role;
use AdminUser\Domain\Services\RegistrationToken\RegistrationTokenIssueService;
use Closure;
use DateTimeImmutable;
use Mockery;
use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\Test;
use ResultType\Err;
use ResultType\Ok;
use Support\Contracts\TransactionInterface;
use Support\Domain\Error\DomainValidationError;
use Support\UseCase\Error\BusinessLogicError;
use Support\UseCase\Error\InvalidInputError;
use Tests\Support\Domain\EntityFactory;
use Tests\TestCase;

class IssueRegistrationTokenUseCaseTest extends TestCase
{
    use EntityFactory;

    private MockInterface&TransactionInterface $transaction;

    private AdminUserRepositoryInterface&MockInterface $adminUserRepository;

    private MockInterface&RegistrationTokenIssueService $issueService;

    private MockInterface&RegistrationTokenRepositoryInterface $repository;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->transaction = Mockery::mock(TransactionInterface::class);
        $this->adminUserRepository = Mockery::mock(AdminUserRepositoryInterface::class);
        $this->issueService = Mockery::mock(RegistrationTokenIssueService::class);
        $this->repository = Mockery::mock(RegistrationTokenRepositoryInterface::class);
    }

    #[Test]
    public function canIssue(): void
    {
        $email = 'invitee@example.com';
        $plainToken = 'plain-token';

        $token = new RegistrationToken(
            RegistrationTokenId::reconstruct('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'),
            HashedTokenValue::reconstruct('hashed'),
            Email::reconstruct($email),
            Role::General,
            Permissions::reconstruct([]),
            ExpiredAt::reconstruct(new DateTimeImmutable('+7 days')),
            ConsumptionStatus::Unused,
        );

        $this->transaction->shouldReceive('scope')
            ->withArgs(fn (Closure $_) => true)
            ->andReturnUsing(fn (Closure $arg) => $arg())
            ->once();

        $this->adminUserRepository->shouldReceive('findByEmail')
            ->withArgs(fn (Email $arg): bool => $arg->value === $email)
            ->andReturn(null)
            ->once();

        $this->issueService->shouldReceive('issue')
            ->withArgs(fn (Email $arg, int $role, array $permissions): bool => $arg->value === $email
                && $role === Role::General->value
                && $permissions === [])
            ->andReturn(new Ok(['token' => $token, 'plainToken' => $plainToken]))
            ->once();

        $this->repository->shouldReceive('save')
            ->with($token)
            ->andReturn($token)
            ->once();

        $result = $this->getInstance()->handle(
            new IssueRegistrationTokenInputData($email, Role::General->value, []),
        );

        $this->assertTrue($result->isOk());
        $output = $result->unwrap();
        $this->assertSame($plainToken, $output->plainToken);
        $this->assertTrue($output->token->equals($token));
    }

    #[Test]
    public function issueFailsIfEmailAlreadyExists(): void
    {
        $email = 'existing@example.com';

        $this->transaction->shouldReceive('scope')
            ->withArgs(fn (Closure $_) => true)
            ->andReturnUsing(fn (Closure $arg) => $arg())
            ->once();

        $this->adminUserRepository->shouldReceive('findByEmail')
            ->withArgs(fn (Email $arg): bool => $arg->value === $email)
            ->andReturn($this->createAdminUser('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', $email))
            ->once();

        $result = $this->getInstance()->handle(
            new IssueRegistrationTokenInputData($email, Role::General->value, []),
        );

        $this->assertTrue($result->isErr());
        $this->assertInstanceOf(BusinessLogicError::class, $result->unwrapErr());
    }

    #[Test]
    public function issueFailsIfIssueServiceReturnsErr(): void
    {
        $email = 'invitee@example.com';

        $this->transaction->shouldReceive('scope')
            ->withArgs(fn (Closure $_) => true)
            ->andReturnUsing(fn (Closure $arg) => $arg())
            ->once();

        $this->adminUserRepository->shouldReceive('findByEmail')
            ->withArgs(fn (Email $arg): bool => $arg->value === $email)
            ->andReturn(null)
            ->once();

        $this->issueService->shouldReceive('issue')
            ->andReturn(new Err(new DomainValidationError([])))
            ->once();

        $result = $this->getInstance()->handle(
            new IssueRegistrationTokenInputData($email, Role::General->value, []),
        );

        $this->assertTrue($result->isErr());
        $this->assertInstanceOf(InvalidInputError::class, $result->unwrapErr());
    }

    private function getInstance(): IssueRegistrationTokenUseCase
    {
        return new IssueRegistrationTokenUseCase(
            $this->transaction,
            $this->adminUserRepository,
            $this->issueService,
            $this->repository,
        );
    }
}
