<?php

declare(strict_types=1);

namespace Tests\Unit\User\Domain\Services;

use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Support\Contracts\UuidGeneratorInterface;
use Tests\Support\Domain\EntityFactory;
use Tests\TestCase;
use User\Domain\Models\Email;
use User\Domain\Models\PlainPassword;
use User\Domain\Models\UserFactoryInterface;
use User\Domain\Models\UserId;
use User\Domain\Models\UserRepositoryInterface;
use User\Domain\Services\UserIntegrityService;

class UserIntegrityServiceTest extends TestCase
{
    use EntityFactory;

    private MockInterface&UuidGeneratorInterface $generator;

    private MockInterface&UserFactoryInterface $factory;

    private MockInterface&UserRepositoryInterface $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->generator = Mockery::mock(UuidGeneratorInterface::class);
        $this->factory = Mockery::mock(UserFactoryInterface::class);
        $this->repository = Mockery::mock(UserRepositoryInterface::class);
    }

    #[Test]
    public function prepareForCreate(): void
    {
        $email = 'example@example.com';
        $uuid = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';
        $password = 'plain';

        $this->generator->shouldReceive('generate')
            ->with()
            ->andReturn($uuid)
            ->once();

        $expectedUser = $this->createUser($uuid, $email, $password);

        $this->factory->shouldReceive('create')
            ->with(
                Mockery::on(fn (UserId $arg): bool => $arg->value === $uuid),
                Mockery::on(fn (Email $arg): bool => $arg->value === $email),
                Mockery::on(fn (PlainPassword $arg): bool => $arg->value === $password),
            )
            ->andReturn($expectedUser)
            ->once();

        $this->repository->shouldReceive('findByEmail')
            ->with(Mockery::on(fn (Email $arg): bool => $arg->value === $email))
            ->andReturnNull()
            ->once();

        $result = $this->getInstance()->prepareForCreate($email, $password);

        $this->assertTrue($result->isOk());
        $this->assertSame($expectedUser, $result->unwrap());
    }

    #[Test]
    public function prepareForCreateDuplicateEmail(): void
    {
        $email = 'example@example.com';
        $uuid = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';
        $password = 'plain';

        $this->generator->shouldReceive('generate')
            ->with()
            ->andReturn($uuid)
            ->once();

        $expectedUser = $this->createUser($uuid, $email, $password);

        $this->factory->shouldReceive('create')
            ->with(
                Mockery::on(fn (UserId $arg): bool => $arg->value === $uuid),
                Mockery::on(fn (Email $arg): bool => $arg->value === $email),
                Mockery::on(fn (PlainPassword $arg): bool => $arg->value === $password),
            )
            ->andReturn($expectedUser)
            ->once();

        $existingUser = $this->createUser('BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB', $email, $password);

        $this->repository->shouldReceive('findByEmail')
            ->with(Mockery::on(fn (Email $arg): bool => $arg->value === $email))
            ->andReturn($existingUser)
            ->once();

        $result = $this->getInstance()->prepareForCreate($email, $password);

        $this->assertTrue($result->isErr());
        $this->assertSame('すでに使われているメールアドレスです "example@example.com"', $result->unwrapErr());
    }

    private function getInstance(): UserIntegrityService
    {
        return new UserIntegrityService(
            $this->generator,
            $this->factory,
            $this->repository,
        );
    }
}
