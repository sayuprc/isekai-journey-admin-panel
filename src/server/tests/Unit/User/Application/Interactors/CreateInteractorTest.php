<?php

declare(strict_types=1);

namespace Tests\Unit\User\Application\Interactors;

use Closure;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Support\Contracts\TransactionInterface;
use Tests\TestCase;
use User\Application\Interactors\CreateInteractor;
use User\Application\UseCase\Create\CreateInputData;
use User\Application\UseCase\Create\CreateUseCaseInterface;
use User\Domain\Models\Email;
use User\Domain\Models\HashedPassword;
use User\Domain\Models\User;
use User\Domain\Models\UserFactoryInterface;
use User\Domain\Models\UserId;
use User\Domain\Models\UserRepositoryInterface;

class CreateInteractorTest extends TestCase
{
    private MockInterface&TransactionInterface $transaction;

    private MockInterface&UserRepositoryInterface $repository;

    private MockInterface&UserFactoryInterface $factory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->transaction = Mockery::mock(TransactionInterface::class);
        $this->repository = Mockery::mock(UserRepositoryInterface::class);
        $this->factory = Mockery::mock(UserFactoryInterface::class);
    }

    #[Test]
    public function isImplementsSpecificInterface(): void
    {
        $this->assertInstanceOf(CreateUseCaseInterface::class, $this->getInstance());
    }

    #[Test]
    public function canCreate(): void
    {
        $this->transaction->shouldReceive('scope')
            ->with(Mockery::on(fn (Closure $_) => true))
            ->andReturnUsing(fn (Closure $arg) => $arg())
            ->once();

        $this->repository->shouldReceive('findByEmail')
            ->with(Mockery::on(fn (Email $arg) => $arg->value === 'example@example.com'))
            ->andReturnNull()
            ->once();

        $uuid = $this->generateUuid();

        $this->factory->shouldReceive('create')
            ->with('example@example.com', 'plainpassword')
            ->andReturn(
                $user = new User(
                    new UserId($uuid),
                    new Email('example@example.com'),
                    new HashedPassword('hashedpassword')
                )
            )
            ->once();

        $this->repository->shouldReceive('save')
            ->with(
                Mockery::on(
                    fn (User $arg) => $arg->userId->value === $uuid
                        && $arg->email->value === 'example@example.com'
                        && $arg->hashedPassword->value !== 'plainpassword'
                )
            )
            ->andReturn($user)
            ->once();

        $result = $this->getInstance()->handle(new CreateInputData('example@example.com', 'plainpassword'));

        $this->assertTrue($result->isOk());
    }

    #[Test]
    public function createFailsIfEmailAlreadyExists(): void
    {
        $this->transaction->shouldReceive('scope')
            ->with(Mockery::on(fn (Closure $_) => true))
            ->andReturnUsing(fn (Closure $arg) => $arg())
            ->once();

        $this->repository->shouldReceive('findByEmail')
            ->with(Mockery::on(fn (Email $arg) => $arg->value === 'example@example.com'))
            ->andReturn(
                new User(
                    new UserId($this->generateUuid()),
                    new Email('example@example.com'),
                    new HashedPassword('hashedpassowrd')
                )
            )
            ->once();

        $result = $this->getInstance()->handle(new CreateInputData('example@example.com', 'plainpassword'));

        $this->assertTrue($result->isErr());
    }

    private function getInstance(): CreateInteractor
    {
        return new CreateInteractor($this->transaction, $this->repository, $this->factory);
    }
}
