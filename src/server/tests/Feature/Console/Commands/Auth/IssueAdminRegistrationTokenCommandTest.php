<?php

declare(strict_types=1);

namespace Tests\Feature\Console\Commands\Auth;

use AdminUser\Domain\Models\Role;
use App\Models\Auth\AdminRegistrationToken;
use Auth\Domain\Services\Token\RefreshToken\RandomTokenGeneratorInterface;
use Auth\Domain\Services\Token\RefreshToken\TokenHasherInterface;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\DatabaseTestCase;

class IssueAdminRegistrationTokenCommandTest extends DatabaseTestCase
{
    #[Test]
    public function canIssueAdminRegistrationToken(): void
    {
        $plainToken = 'plain-registration-token';

        $this->app->instance(
            RandomTokenGeneratorInterface::class,
            Mockery::mock(RandomTokenGeneratorInterface::class, function (Mockery\MockInterface $mock) use ($plainToken): void {
                $mock->shouldReceive('generate')
                    ->once()
                    ->andReturn($plainToken);
            }),
        );

        $this->artisan('admin:invite example@example.com')
            ->expectsOutput('登録トークン: ' . $plainToken)
            ->expectsOutput('管理画面ユーザー登録トークンを発行しました')
            ->assertSuccessful();

        $stored = AdminRegistrationToken::query()->sole();
        $this->assertSame('example@example.com', $stored->email);
        $this->assertSame(Role::General->value, $stored->role);
        $this->assertNotSame($plainToken, $stored->token);
        $this->assertTrue($this->app->make(TokenHasherInterface::class)->verify($plainToken, $stored->token));
    }

    #[Test]
    public function canIssuePrivilegeAdminRegistrationToken(): void
    {
        $plainToken = 'privilege-registration-token';

        $this->app->instance(
            RandomTokenGeneratorInterface::class,
            Mockery::mock(RandomTokenGeneratorInterface::class, function (Mockery\MockInterface $mock) use ($plainToken): void {
                $mock->shouldReceive('generate')
                    ->once()
                    ->andReturn($plainToken);
            }),
        );

        $this->artisan('admin:invite privilege@example.com --privilege')
            ->expectsOutput('登録トークン: ' . $plainToken)
            ->expectsOutput('管理画面ユーザー登録トークンを発行しました')
            ->assertSuccessful();

        $stored = AdminRegistrationToken::query()->sole();
        $this->assertSame(Role::Privilege->value, $stored->role);
    }
}
