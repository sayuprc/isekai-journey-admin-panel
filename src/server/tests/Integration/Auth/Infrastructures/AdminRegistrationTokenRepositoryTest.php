<?php

declare(strict_types=1);

namespace Tests\Integration\Auth\Infrastructures;

use AdminUser\Domain\Models\Email;
use AdminUser\Domain\Models\Role;
use App\Models\Auth\AdminRegistrationToken as AuthAdminRegistrationToken;
use Auth\Domain\Models\AdminRegistrationToken\AdminRegistrationToken;
use Auth\Domain\Models\AdminRegistrationToken\AdminRegistrationTokenRepositoryInterface;
use Auth\Domain\Models\AdminRegistrationToken\ConsumptionStatus;
use Auth\Domain\Services\Token\RefreshToken\TokenHasherInterface;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Support\Contracts\Uuid\UuidConverterInterface;
use Tests\Support\DatabaseTestCase;

class AdminRegistrationTokenRepositoryTest extends DatabaseTestCase
{
    #[Test]
    public function findAvailableByEmail(): void
    {
        Carbon::setTestNow('2026-01-01 00:00:00');

        $token = $this->createToken(
            'example@example.com',
            Role::General,
            'token-value',
            now()->addMinutes(30)->toDateTimeImmutable(),
            ConsumptionStatus::Unused,
        );

        $repository = $this->repository();
        $repository->save($token);

        $found = $repository->findAvailableByEmail(Email::reconstruct('example@example.com'));

        $this->assertCount(1, $found);
        $this->assertEquals($token, $found[0]);
    }

    #[Test]
    public function findAvailableByEmailReturnsEmptyWhenNotFound(): void
    {
        $found = $this->repository()->findAvailableByEmail(Email::reconstruct('missing@example.com'));

        $this->assertSame([], $found);
    }

    #[Test]
    public function findAvailableByEmailSkipsExpiredToken(): void
    {
        Carbon::setTestNow('2026-01-01 00:00:00');

        $repository = $this->repository();
        $repository->save(
            $this->createToken(
                'example@example.com',
                Role::General,
                'token-value',
                now()->subMinute()->toDateTimeImmutable(),
                ConsumptionStatus::Unused,
            ),
        );

        $found = $repository->findAvailableByEmail(Email::reconstruct('example@example.com'));

        $this->assertSame([], $found);
    }

    #[Test]
    public function findAvailableByEmailSkipsConsumedToken(): void
    {
        Carbon::setTestNow('2026-01-01 00:00:00');

        $repository = $this->repository();
        $repository->save(
            $this->createToken(
                'example@example.com',
                Role::General,
                'token-value',
                now()->addMinutes(30)->toDateTimeImmutable(),
                ConsumptionStatus::Consumed,
            ),
        );

        $found = $repository->findAvailableByEmail(Email::reconstruct('example@example.com'));

        $this->assertSame([], $found);
    }

    #[Test]
    public function saveUpdatesExisting(): void
    {
        Carbon::setTestNow('2026-01-01 00:00:00');

        $token = $this->createToken(
            'example@example.com',
            Role::General,
            'token-value',
            now()->addMinutes(30)->toDateTimeImmutable(),
            ConsumptionStatus::Unused,
        );

        $repository = $this->repository();
        $repository->save($token);
        $repository->save($token->consume());

        $found = $repository->findAvailableByEmail(Email::reconstruct('example@example.com'));

        $this->assertSame([], $found);
    }

    #[Test]
    public function saveStoresHashedTokenDirectly(): void
    {
        Carbon::setTestNow('2026-01-01 00:00:00');

        $plainToken = 'plain-registration-token';
        $hasher = $this->app->make(TokenHasherInterface::class);
        $hashedToken = $hasher->hash($plainToken);

        $token = $this->createToken(
            'example@example.com',
            Role::Privilege,
            $hashedToken,
            now()->addMinutes(30)->toDateTimeImmutable(),
            ConsumptionStatus::Unused,
        );

        $this->repository()->save($token);

        $converter = $this->app->make(UuidConverterInterface::class);
        $stored = AuthAdminRegistrationToken::query()
            ->where('admin_registration_token_id', $converter->toBin($token->adminRegistrationTokenId->value))
            ->first();

        $this->assertNotNull($stored);
        $this->assertSame(Role::Privilege->value, $stored->role);
        $this->assertEquals($hashedToken, $stored->token);
        $this->assertTrue($hasher->verify($plainToken, $stored->token));
    }

    private function repository(): AdminRegistrationTokenRepositoryInterface
    {
        return $this->app->make(AdminRegistrationTokenRepositoryInterface::class);
    }

    private function createToken(
        string $email,
        Role $role,
        string $token,
        \DateTimeImmutable $expiredAt,
        ConsumptionStatus $status,
    ): AdminRegistrationToken {
        return AdminRegistrationToken::reconstruct(
            $this->generateUuid(),
            $email,
            $role->value,
            $token,
            $expiredAt,
            $status->value,
        );
    }
}
