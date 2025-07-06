<?php

declare(strict_types=1);

namespace Tests\Unit\User\Application\Interactors;

use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
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
    private MockInterface&UserRepositoryInterface $repository;

    private MockInterface&UserFactoryInterface $factory;

    protected function setUp(): void
    {
        parent::setUp();

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
        $this->repository->shouldReceive('findByEmail')
            ->with(Mockery::on(fn (Email $arg) => $arg->value === 'example@example.com'))
            ->andReturnNull()
            ->once();

        $uuid = $this->generateUuid();

        $this->factory->shouldReceive('create')
            ->with('example@example.com', 'plainpassword')
            ->andReturn(
                new User(
                    new UserId($uuid),
                    new Email('example@example.com'),
                    new HashedPassword('hashedpassowrd')
                )
            )
            ->once();

        $this->repository->shouldReceive('insert')
            ->with(
                Mockery::on(
                    fn (User $arg) => $arg->userId->value === $uuid
                        && $arg->email->value === 'example@example.com'
                        && $arg->hashedPassword->value !== 'plainpassword'
                )
            )
            ->once();

        $result = $this->getInstance()->handle(new CreateInputData('example@example.com', 'plainpassword'));

        $this->assertTrue($result->isOk());
    }

    #[Test]
    public function createFailsIfEmailAlreadyExists(): void
    {
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
        return new CreateInteractor($this->repository, $this->factory);
    }
}
