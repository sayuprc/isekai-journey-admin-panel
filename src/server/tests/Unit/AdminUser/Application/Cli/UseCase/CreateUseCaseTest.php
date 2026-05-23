<?php

declare(strict_types=1);

namespace Tests\Unit\AdminUser\Application\Cli\UseCase;

use AdminUser\Application\Cli\UseCase\Create\CreateInputData;
use AdminUser\Application\Cli\UseCase\Create\CreateUseCase;
use AdminUser\Domain\Models\AdminUser;
use AdminUser\Domain\Models\AdminUserRepositoryInterface;
use AdminUser\Domain\Models\Role;
use AdminUser\Domain\Services\AdminUserIntegrityService;
use Closure;
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

class CreateUseCaseTest extends TestCase
{
    use EntityFactory;

    private MockInterface&TransactionInterface $transaction;

    private AdminUserRepositoryInterface&MockInterface $repository;

    private AdminUserIntegrityService&MockInterface $service;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->transaction = Mockery::mock(TransactionInterface::class);
        $this->repository = Mockery::mock(AdminUserRepositoryInterface::class);
        $this->service = Mockery::mock(AdminUserIntegrityService::class);
    }

    #[Test]
    public function canCreate(): void
    {
        $uuid = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';
        $email = 'example@example.com';
        $this->transaction->shouldReceive('scope')
            ->withArgs(fn (Closure $_) => true)
            ->andReturnUsing(fn (Closure $arg) => $arg())
            ->once();

        $this->service->shouldReceive('prepareForCreate')
            ->with('テストユーザー', $email, Role::General->value, [])
            ->andReturn(new Ok($user = $this->createAdminUser($uuid, $email, Role::General, [])))
            ->once();

        $this->repository->shouldReceive('register')
            ->withArgs(
                fn (AdminUser $userArg): bool => $userArg->adminUserId->value === $uuid
                    && $userArg->email->value === $email,
            )
            ->andReturn($user)
            ->once();

        $result = $this->getInstance()->handle(new CreateInputData('テストユーザー', $email, Role::General->value, []));

        $this->assertTrue($result->isOk());
    }

    #[Test]
    public function createFailsIfEmailAlreadyExists(): void
    {
        $email = 'example@example.com';
        $this->transaction->shouldReceive('scope')
            ->withArgs(fn (Closure $_) => true)
            ->andReturnUsing(fn (Closure $arg) => $arg())
            ->once();

        $this->service->shouldReceive('prepareForCreate')
            ->with('テストユーザー', $email, Role::General->value, [])
            ->andReturn(new Err(new DomainValidationError([])))
            ->once();

        $result = $this->getInstance()->handle(new CreateInputData('テストユーザー', $email, Role::General->value, []));

        $this->assertTrue($result->isErr());
        $this->assertInstanceOf(InvalidInputError::class, $result->unwrapErr());
    }

    private function getInstance(): CreateUseCase
    {
        return new CreateUseCase(
            $this->transaction,
            $this->repository,
            $this->service,
        );
    }
}
