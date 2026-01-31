<?php

declare(strict_types=1);

namespace Tests\Unit\User\Application\Interactors;

use Closure;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Support\Contracts\TransactionInterface;
use Support\Contracts\UuidGeneratorInterface;
use Tests\Support\Domain\EntityFactory;
use Tests\TestCase;
use User\Application\Interactors\CreateInteractor;
use User\Application\UseCase\Create\CreateInputData;
use User\Domain\Models\Email;
use User\Domain\Models\PlainPassword;
use User\Domain\Models\User;
use User\Domain\Models\UserFactoryInterface;
use User\Domain\Models\UserId;
use User\Domain\Models\UserRepositoryInterface;

class CreateInteractorTest extends TestCase
{
    use EntityFactory;

    private MockInterface&TransactionInterface $transaction;

    private MockInterface&UserRepositoryInterface $repository;

    private MockInterface&UserFactoryInterface $factory;

    private MockInterface&UuidGeneratorInterface $generator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->transaction = Mockery::mock(TransactionInterface::class);
        $this->repository = Mockery::mock(UserRepositoryInterface::class);
        $this->factory = Mockery::mock(UserFactoryInterface::class);
        $this->generator = Mockery::mock(UuidGeneratorInterface::class);
    }

    #[Test]
    public function canCreate(): void
    {
        $this->generator->shouldReceive('generate')
            ->andReturn($uuid = $this->generateUuid())
            ->once();

        $this->transaction->shouldReceive('scope')
            ->with(Mockery::on(fn (Closure $_) => true))
            ->andReturnUsing(fn (Closure $arg) => $arg())
            ->once();

        $this->factory->shouldReceive('create')
            ->with(
                Mockery::on(fn (UserId $arg) => $arg->value === $uuid),
                Mockery::on(fn (Email $arg) => $arg->value === 'example@example.com'),
                Mockery::on(fn (PlainPassword $arg) => $arg->value === 'plainpassword'),
            )
            ->andReturn($user = $this->createUser($uuid, 'example@example.com', 'hashedpassword'))
            ->once();

        $this->repository->shouldReceive('findByEmail')
            ->with(Mockery::on(fn (Email $arg) => $arg->value === 'example@example.com'))
            ->andReturnNull()
            ->once();

        $this->repository->shouldReceive('save')
            ->with(
                Mockery::on(
                    fn (User $arg) => $arg->userId->value === $uuid
                        && $arg->email->value === 'example@example.com'
                        && $arg->hashedPassword->value !== 'plainpassword',
                ),
            )
            ->andReturn($user)
            ->once();

        $result = $this->getInstance()->handle(new CreateInputData('example@example.com', 'plainpassword'));

        $this->assertTrue($result->isOk());
    }

    #[Test]
    public function createFailsIfEmailAlreadyExists(): void
    {
        $this->generator->shouldReceive('generate')
            ->andReturn($uuid = $this->generateUuid())
            ->once();

        $this->transaction->shouldReceive('scope')
            ->with(Mockery::on(fn (Closure $_) => true))
            ->andReturnUsing(fn (Closure $arg) => $arg())
            ->once();

        $this->factory->shouldReceive('create')
            ->with(
                Mockery::on(fn (UserId $arg) => $arg->value === $uuid),
                Mockery::on(fn (Email $arg) => $arg->value === 'example@example.com'),
                Mockery::on(fn (PlainPassword $arg) => $arg->value === 'plainpassword'),
            )
            ->andReturn($user = $this->createUser($uuid, 'example@example.com', 'hashedpassword'))
            ->once();

        $this->repository->shouldReceive('findByEmail')
            ->with(Mockery::on(fn (Email $arg) => $arg->value === 'example@example.com'))
            ->andReturn($user)
            ->once();

        $result = $this->getInstance()->handle(new CreateInputData('example@example.com', 'plainpassword'));

        $this->assertTrue($result->isErr());
    }

    private function getInstance(): CreateInteractor
    {
        return new CreateInteractor(
            $this->transaction,
            $this->repository,
            $this->factory,
            $this->generator,
        );
    }
}
