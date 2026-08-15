<?php

declare(strict_types=1);

namespace Tests;

use AdminUser\Domain\Models\AdminUser;
use AdminUser\Domain\Models\AdminUserRepositoryInterface;
use AdminUser\Domain\Models\Role;
use Auth\Domain\Models\AuthContext;
use Auth\Infrastructures\Auth\UseCaseAuthorizationContext;
use DateTimeImmutable;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Override;
use Support\Contracts\Uuid\UuidConverterInterface;
use Support\Contracts\Uuid\UuidGeneratorInterface;
use Support\UseCase\Authorizer\UseCaseAuthorizer;

abstract class TestCase extends BaseTestCase
{
    /**
     * .env.testing には置かないテスト用の既定値
     * シナリオ依存の値 (JWT 鍵など) は各テストで config()->set する
     *
     * @var array<string, string>
     */
    private const array TESTING_ENV_DEFAULTS = [
        'APP_KEY' => 'base64:AUMNxw7cx4uYZgywdQxCzOiVA9gmSwMJohPE21aWGDM=',
        'APP_URL' => 'https://api.example.test',
        'APP_TIMEZONE' => 'Asia/Tokyo',
        'AUTH_RECOVERY_CODE_PEPPER' => 'testing-recovery-code-pepper',
    ];

    private ?AuthContext $privilegedAuthContext = null;

    #[Override]
    public function createApplication(): Application
    {
        $this->ensureTestingEnvironment();

        return parent::createApplication();
    }

    protected function generateUuid(): string
    {
        return $this->app->make(UuidGeneratorInterface::class)->generate();
    }

    protected function toUuid(string $bin): string
    {
        return $this->app->make(UuidConverterInterface::class)->toUuid($bin);
    }

    protected function privilegedContext(): AuthContext
    {
        if (! is_null($this->privilegedAuthContext)) {
            return $this->privilegedAuthContext;
        }

        $context = $this->app->make(AuthContext::class);

        $user = AdminUser::reconstruct(
            $this->generateUuid(),
            'テストユーザー',
            'test@example.com',
            new DateTimeImmutable(),
            Role::Privilege->value,
            [],
        );

        // 監査ログ機構が admin_users への外部キーを要求するため、
        // DB を使うテスト(DatabaseTransactions を使うテスト)の場合のみ、
        // 認証済みユーザーを実 DB にも登録する
        if (in_array(DatabaseTransactions::class, class_uses_recursive(static::class), true)) {
            $repository = $this->app->make(AdminUserRepositoryInterface::class);

            if (is_null($repository->find($user->adminUserId))) {
                $repository->register($user);
            }
        }

        $context->set($user);

        $this->privilegedAuthContext = $context;

        return $context;
    }

    protected function authorizer(?AuthContext $context = null): UseCaseAuthorizer
    {
        return new UseCaseAuthorizer(new UseCaseAuthorizationContext($context ?? $this->privilegedContext()));
    }

    private function ensureTestingEnvironment(): void
    {
        foreach (self::TESTING_ENV_DEFAULTS as $key => $value) {
            $current = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);

            // phpunit.xml などで既に実値が入っている場合は尊重する
            if (is_string($current) && $current !== '') {
                continue;
            }

            putenv("{$key}={$value}");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }
}
