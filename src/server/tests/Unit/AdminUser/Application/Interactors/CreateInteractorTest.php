<?php

declare(strict_types=1);

namespace Tests\Unit\AdminUser\Application\Interactors;

use AdminUser\Application\Interactors\CreateInteractor;
use AdminUser\Application\UseCase\Create\CreateInputData;
use AdminUser\Domain\Models\AdminUser;
use AdminUser\Domain\Models\AdminUserRepositoryInterface;
use AdminUser\Domain\Models\HashedPassword;
use AdminUser\Domain\Models\Role;
use AdminUser\Domain\Services\AdminUserIntegrityService;
use AdminUser\Domain\Services\HasherInterface;
use Closure;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use ResultType\Err;
use ResultType\Ok;
use Support\Contracts\TransactionInterface;
use Support\Domain\Error\DomainValidationError;
use Support\UseCase\Error\InvalidInputError;
use Tests\Support\Domain\EntityFactory;
use Tests\TestCase;

class CreateInteractorTest extends TestCase
{
    use EntityFactory;

    private MockInterface&TransactionInterface $transaction;

    private HasherInterface&MockInterface $hasher;

    private AdminUserRepositoryInterface&MockInterface $repository;

    private AdminUserIntegrityService&MockInterface $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->transaction = Mockery::mock(TransactionInterface::class);
        $this->hasher = Mockery::mock(HasherInterface::class);
        $this->repository = Mockery::mock(AdminUserRepositoryInterface::class);
        $this->service = Mockery::mock(AdminUserIntegrityService::class);
    }

    #[Test]
    public function canCreate(): void
    {
        $uuid = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';
        $email = 'example@example.com';
        $password = 'plain';

        $this->transaction->shouldReceive('scope')
            ->withArgs(fn (Closure $_) => true)
            ->andReturnUsing(fn (Closure $arg) => $arg())
            ->once();

        $this->service->shouldReceive('prepareForCreate')
            ->with('テストユーザー', $email, Role::General->value, [])
            ->andReturn(new Ok($user = $this->createAdminUser($uuid, $email, Role::General, [])))
            ->once();

        $this->hasher->shouldReceive('hash')
            ->with($password)
            ->andReturn('hashed')
            ->once();

        $this->repository->shouldReceive('register')
            ->withArgs(
                fn (AdminUser $userArg, HashedPassword $passwordArg): bool => $userArg->adminUserId->value === $uuid
                    && $userArg->email->value === $email
                    && $passwordArg->value === 'hashed',
            )
            ->andReturn($user)
            ->once();

        $result = $this->getInstance()->handle(new CreateInputData('テストユーザー', $email, $password, Role::General->value, []));

        $this->assertTrue($result->isOk());
    }

    #[Test]
    public function createFailsIfEmailAlreadyExists(): void
    {
        $email = 'example@example.com';
        $password = 'plain';

        $this->transaction->shouldReceive('scope')
            ->withArgs(fn (Closure $_) => true)
            ->andReturnUsing(fn (Closure $arg) => $arg())
            ->once();

        $this->service->shouldReceive('prepareForCreate')
            ->with('テストユーザー', $email, Role::General->value, [])
            ->andReturn(new Err(new DomainValidationError([])))
            ->once();

        $result = $this->getInstance()->handle(new CreateInputData('テストユーザー', $email, $password, Role::General->value, []));

        $this->assertTrue($result->isErr());
        $this->assertInstanceOf(InvalidInputError::class, $result->unwrapErr());
    }

    private function getInstance(): CreateInteractor
    {
        return new CreateInteractor(
            $this->transaction,
            $this->hasher,
            $this->repository,
            $this->service,
        );
    }
}
