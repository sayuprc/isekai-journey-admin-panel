<?php

declare(strict_types=1);

namespace Tests\Unit\AdminUser\Domain\Services;

use AdminUser\Domain\Models\AdminUserRepositoryInterface;
use AdminUser\Domain\Models\Email;
use AdminUser\Domain\Models\Role;
use AdminUser\Domain\Services\AdminUserIntegrityService;
use DateTimeImmutable;
use Mockery;
use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\Test;
use Support\Contracts\ClockInterface;
use Support\Contracts\Uuid\UuidGeneratorInterface;
use Support\Domain\Error\BusinessRuleViolationError;
use Tests\Support\Domain\EntityFactory;
use Tests\TestCase;

class AdminUserIntegrityServiceTest extends TestCase
{
    use EntityFactory;

    private ClockInterface&MockInterface $clock;

    private MockInterface&UuidGeneratorInterface $generator;

    private AdminUserRepositoryInterface&MockInterface $repository;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->clock = Mockery::mock(ClockInterface::class);
        $this->generator = Mockery::mock(UuidGeneratorInterface::class);
        $this->repository = Mockery::mock(AdminUserRepositoryInterface::class);
    }

    #[Test]
    public function prepareForCreate(): void
    {
        $adminUserName = 'テストユーザー';
        $email = 'example@example.com';
        $uuid = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';
        $now = new DateTimeImmutable('2026-01-01 00:00:00');
        $role = Role::General;
        $permissions = [];

        $this->generator->shouldReceive('generate')
            ->with()
            ->andReturn($uuid)
            ->once();

        $this->clock->shouldReceive('now')
            ->with()
            ->andReturn($now)
            ->once();

        $expectedUser = $this->createAdminUser($uuid, $email, $role, $permissions, $now);

        $this->repository->shouldReceive('findByEmail')
            ->with(Mockery::on(fn (Email $arg): bool => $arg->value === $email))
            ->andReturnNull()
            ->once();

        $result = $this->getInstance()->prepareForCreate($adminUserName, $email, $role->value, $permissions);

        $this->assertTrue($result->isOk());
        $this->assertEquals($expectedUser, $result->unwrap());
    }

    #[Test]
    public function prepareForCreateDuplicateEmail(): void
    {
        $adminUserName = 'テストユーザー';
        $email = 'example@example.com';
        $uuid = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';
        $now = new DateTimeImmutable('2026-01-01 00:00:00');
        $role = Role::General;
        $permissions = [];

        $this->generator->shouldReceive('generate')
            ->with()
            ->andReturn($uuid)
            ->once();

        $this->clock->shouldReceive('now')
            ->with()
            ->andReturn($now)
            ->once();

        $expectedUser = $this->createAdminUser($uuid, $email, $role, $permissions, $now);

        $existingUser = $this->createAdminUser('BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB', $email, $role, $permissions, $now);

        $this->repository->shouldReceive('findByEmail')
            ->with(Mockery::on(fn (Email $arg): bool => $arg->value === $email))
            ->andReturn($existingUser)
            ->once();

        $result = $this->getInstance()->prepareForCreate($adminUserName, $email, $role->value, $permissions);

        $this->assertTrue($result->isErr());
        $error = $result->unwrapErr();
        $this->assertInstanceOf(BusinessRuleViolationError::class, $error);
        $this->assertSame('すでに使われているメールアドレスです "example@example.com"', $error->message);
    }

    private function getInstance(): AdminUserIntegrityService
    {
        return new AdminUserIntegrityService(
            $this->clock,
            $this->generator,
            $this->repository,
        );
    }
}
