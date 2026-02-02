<?php

declare(strict_types=1);

namespace Tests\Unit\User\Application\Interactors;

use Closure;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use ResultType\Err;
use ResultType\Ok;
use Support\Contracts\TransactionInterface;
use Tests\Support\Domain\EntityFactory;
use Tests\TestCase;
use User\Application\Interactors\CreateInteractor;
use User\Application\UseCase\Create\CreateInputData;
use User\Domain\Models\User;
use User\Domain\Models\UserRepositoryInterface;
use User\Domain\Services\UserIntegrityService;

class CreateInteractorTest extends TestCase
{
    use EntityFactory;

    private MockInterface&TransactionInterface $transaction;

    private MockInterface&UserRepositoryInterface $repository;

    private MockInterface&UserIntegrityService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->transaction = Mockery::mock(TransactionInterface::class);
        $this->repository = Mockery::mock(UserRepositoryInterface::class);
        $this->service = Mockery::mock(UserIntegrityService::class);
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
            ->with($email, $password)
            ->andReturn(new Ok($user = $this->createUser($uuid, $email, $password)))
            ->once();

        $this->repository->shouldReceive('save')
            ->withArgs(
                fn (User $arg): bool => $arg->userId->value === $uuid
                    && $arg->email->value === $email,
            )
            ->andReturn($user)
            ->once();

        $result = $this->getInstance()->handle(new CreateInputData($email, $password));

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
            ->with($email, $password)
            ->andReturn(new Err(''))
            ->once();

        $result = $this->getInstance()->handle(new CreateInputData($email, $password));

        $this->assertTrue($result->isErr());
    }

    private function getInstance(): CreateInteractor
    {
        return new CreateInteractor(
            $this->transaction,
            $this->repository,
            $this->service,
        );
    }
}
