<?php

declare(strict_types=1);

namespace Tests\Unit\AdminUser\Application\UseCase;

use AdminUser\Application\UseCase\IssueRegistrationToken\IssueRegistrationTokenInputData;
use AdminUser\Application\UseCase\IssueRegistrationToken\IssueRegistrationTokenUseCase;
use AdminUser\Domain\Models\AdminUserRegistrationToken;
use AdminUser\Domain\Models\AdminUserRegistrationTokenRepositoryInterface;
use AdminUser\Domain\Models\Role;
use AdminUser\Domain\Services\RegistrationTokenIssueService;
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
use Support\UseCase\Error\InvalidInputError;
use Tests\Support\Domain\EntityFactory;
use Tests\TestCase;

class IssueRegistrationTokenUseCaseTest extends TestCase
{
    use EntityFactory;

    private MockInterface&TransactionInterface $transaction;

    private MockInterface&RegistrationTokenIssueService $service;

    private AdminUserRegistrationTokenRepositoryInterface&MockInterface $repository;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->transaction = Mockery::mock(TransactionInterface::class);
        $this->service = Mockery::mock(RegistrationTokenIssueService::class);
        $this->repository = Mockery::mock(AdminUserRegistrationTokenRepositoryInterface::class);
    }

    #[Test]
    public function canIssue(): void
    {
        $plainToken = 'plain-token';
        $token = $this->createAdminUserRegistrationToken(
            'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA',
            'テストユーザー',
            'invite@example.com',
            Role::General,
            [],
            'hashed-token',
            new DateTimeImmutable('2026-01-01 01:00:00'),
        );

        $this->transaction->shouldReceive('scope')
            ->withArgs(fn (Closure $_) => true)
            ->andReturnUsing(fn (Closure $arg) => $arg())
            ->once();

        $this->service->shouldReceive('issue')
            ->with('テストユーザー', 'invite@example.com', Role::General->value, [], 60)
            ->andReturn(new Ok(['token' => $token, 'plainToken' => $plainToken]))
            ->once();

        $this->repository->shouldReceive('save')
            ->withArgs(fn (AdminUserRegistrationToken $arg): bool => $arg === $token)
            ->andReturn($token)
            ->once();

        $result = $this->getInstance()->handle(
            new IssueRegistrationTokenInputData('テストユーザー', 'invite@example.com', Role::General->value, [], 60),
        );

        $this->assertTrue($result->isOk());
        $this->assertSame($plainToken, $result->unwrap()->plainToken);
    }

    #[Test]
    public function issueFailsWhenServiceReturnsValidationError(): void
    {
        $this->transaction->shouldReceive('scope')
            ->withArgs(fn (Closure $_) => true)
            ->andReturnUsing(fn (Closure $arg) => $arg())
            ->once();

        $this->service->shouldReceive('issue')
            ->with('テストユーザー', 'invite@example.com', Role::General->value, [], 0)
            ->andReturn(new Err(new DomainValidationError(['expiresInMinutes' => ['invalid']])))
            ->once();

        $result = $this->getInstance()->handle(
            new IssueRegistrationTokenInputData('テストユーザー', 'invite@example.com', Role::General->value, [], 0),
        );

        $this->assertTrue($result->isErr());
        $this->assertInstanceOf(InvalidInputError::class, $result->unwrapErr());
    }

    private function getInstance(): IssueRegistrationTokenUseCase
    {
        return new IssueRegistrationTokenUseCase(
            $this->transaction,
            $this->service,
            $this->repository,
        );
    }
}
