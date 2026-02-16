<?php

declare(strict_types=1);

namespace Tests\Unit\AdminUser\Domain\Services;

use AdminUser\Domain\Models\AdminUserFactoryInterface;
use AdminUser\Domain\Models\AdminUserId;
use AdminUser\Domain\Models\AdminUserRepositoryInterface;
use AdminUser\Domain\Models\Email;
use AdminUser\Domain\Models\Permissions;
use AdminUser\Domain\Models\PlainPassword;
use AdminUser\Domain\Models\Role;
use AdminUser\Domain\Services\AdminUserIntegrityService;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Support\Contracts\UuidGeneratorInterface;
use Support\Domain\Error\DomainRuleViolationError;
use Tests\Support\Domain\EntityFactory;
use Tests\TestCase;

class AdminUserIntegrityServiceTest extends TestCase
{
    use EntityFactory;

    private MockInterface&UuidGeneratorInterface $generator;

    private AdminUserFactoryInterface&MockInterface $factory;

    private AdminUserRepositoryInterface&MockInterface $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->generator = Mockery::mock(UuidGeneratorInterface::class);
        $this->factory = Mockery::mock(AdminUserFactoryInterface::class);
        $this->repository = Mockery::mock(AdminUserRepositoryInterface::class);
    }

    #[Test]
    public function prepareForCreate(): void
    {
        $email = 'example@example.com';
        $uuid = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';
        $password = 'plain';
        $role = Role::General;
        $permissions = [];

        $this->generator->shouldReceive('generate')
            ->with()
            ->andReturn($uuid)
            ->once();

        $expectedUser = $this->createUser($uuid, $email, $password, $role, $permissions);

        $this->factory->shouldReceive('create')
            ->with(
                Mockery::on(fn (AdminUserId $arg): bool => $arg->value === $uuid),
                Mockery::on(fn (Email $arg): bool => $arg->value === $email),
                Mockery::on(fn (PlainPassword $arg): bool => $arg->value === $password),
                Mockery::on(fn (Role $arg): bool => $arg === $role),
                Mockery::on(fn (Permissions $arg): bool => $arg->toArray() === $permissions),
            )
            ->andReturn($expectedUser)
            ->once();

        $this->repository->shouldReceive('findByEmail')
            ->with(Mockery::on(fn (Email $arg): bool => $arg->value === $email))
            ->andReturnNull()
            ->once();

        $result = $this->getInstance()->prepareForCreate($email, $password, $role->value, $permissions);

        $this->assertTrue($result->isOk());
        $this->assertSame($expectedUser, $result->unwrap());
    }

    #[Test]
    public function prepareForCreateDuplicateEmail(): void
    {
        $email = 'example@example.com';
        $uuid = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';
        $password = 'plain';
        $role = Role::General;
        $permissions = [];

        $this->generator->shouldReceive('generate')
            ->with()
            ->andReturn($uuid)
            ->once();

        $expectedUser = $this->createUser($uuid, $email, $password, $role, $permissions);

        $this->factory->shouldReceive('create')
            ->with(
                Mockery::on(fn (AdminUserId $arg): bool => $arg->value === $uuid),
                Mockery::on(fn (Email $arg): bool => $arg->value === $email),
                Mockery::on(fn (PlainPassword $arg): bool => $arg->value === $password),
                Mockery::on(fn (Role $arg): bool => $arg === $role),
                Mockery::on(fn (Permissions $arg): bool => $arg->toArray() === $permissions),
            )
            ->andReturn($expectedUser)
            ->once();

        $existingUser = $this->createUser('BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB', $email, $password, $role, $permissions);

        $this->repository->shouldReceive('findByEmail')
            ->with(Mockery::on(fn (Email $arg): bool => $arg->value === $email))
            ->andReturn($existingUser)
            ->once();

        $result = $this->getInstance()->prepareForCreate($email, $password, $role->value, $permissions);

        $this->assertTrue($result->isErr());
        $error = $result->unwrapErr();
        $this->assertInstanceOf(DomainRuleViolationError::class, $error);
        $this->assertSame('すでに使われているメールアドレスです "example@example.com"', $error->message);
    }

    private function getInstance(): AdminUserIntegrityService
    {
        return new AdminUserIntegrityService(
            $this->generator,
            $this->factory,
            $this->repository,
        );
    }
}
