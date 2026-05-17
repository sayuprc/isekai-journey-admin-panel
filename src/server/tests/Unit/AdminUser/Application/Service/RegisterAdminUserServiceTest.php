<?php

declare(strict_types=1);

namespace Tests\Unit\AdminUser\Application\Service;

use AdminUser\Application\Service\RegisterAdminUserService;
use AdminUser\Domain\Models\AdminUser;
use AdminUser\Domain\Models\AdminUserRepositoryInterface;
use AdminUser\Domain\Models\HashedPassword;
use AdminUser\Domain\Models\Role;
use AdminUser\Domain\Services\AdminUserIntegrityService;
use AdminUser\Domain\Services\HasherInterface;
use Mockery;
use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\Test;
use ResultType\Err;
use ResultType\Ok;
use Support\Domain\Error\DomainValidationError;
use Support\UseCase\Error\InvalidInputError;
use Tests\Support\Domain\EntityFactory;
use Tests\TestCase;

class RegisterAdminUserServiceTest extends TestCase
{
    use EntityFactory;

    private HasherInterface&MockInterface $hasher;

    private AdminUserRepositoryInterface&MockInterface $repository;

    private AdminUserIntegrityService&MockInterface $integrityService;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->hasher = Mockery::mock(HasherInterface::class);
        $this->repository = Mockery::mock(AdminUserRepositoryInterface::class);
        $this->integrityService = Mockery::mock(AdminUserIntegrityService::class);
    }

    #[Test]
    public function canRegister(): void
    {
        $uuid = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';
        $email = 'example@example.com';

        $this->integrityService->shouldReceive('prepareForCreate')
            ->with('テストユーザー', $email, Role::General->value, [])
            ->andReturn(new Ok($user = $this->createAdminUser($uuid, $email, Role::General, [])))
            ->once();

        $this->hasher->shouldReceive('hash')
            ->with('plain')
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

        $result = $this->makeService()->handle('テストユーザー', $email, 'plain', Role::General->value, []);

        $this->assertTrue($result->isOk());
    }

    #[Test]
    public function registerFailsIfPrepareForCreateFails(): void
    {
        $this->integrityService->shouldReceive('prepareForCreate')
            ->with('テストユーザー', 'example@example.com', Role::General->value, [])
            ->andReturn(new Err(new DomainValidationError([])))
            ->once();

        $result = $this->makeService()->handle('テストユーザー', 'example@example.com', 'plain', Role::General->value, []);

        $this->assertTrue($result->isErr());
        $this->assertInstanceOf(InvalidInputError::class, $result->unwrapErr());
    }

    #[Test]
    public function registerFailsIfRequiredFieldsAreBlank(): void
    {
        $result = $this->makeService()->handle('', '', '', Role::General->value, []);

        $this->assertTrue($result->isErr());
        $this->assertEquals(
            new InvalidInputError([
                'name' => ['管理ユーザー名を入力してください'],
                'email' => ['メールアドレスを入力してください'],
                'password' => ['パスワードを入力してください'],
            ]),
            $result->unwrapErr(),
        );
    }

    private function makeService(): RegisterAdminUserService
    {
        return new RegisterAdminUserService(
            $this->hasher,
            $this->repository,
            $this->integrityService,
        );
    }
}
