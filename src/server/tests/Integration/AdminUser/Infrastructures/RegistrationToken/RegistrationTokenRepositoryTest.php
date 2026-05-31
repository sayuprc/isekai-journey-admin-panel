<?php

declare(strict_types=1);

namespace Tests\Integration\AdminUser\Infrastructures\RegistrationToken;

use AdminUser\Domain\Models\Email;
use AdminUser\Domain\Models\Permissions;
use AdminUser\Domain\Models\RegistrationToken\ConsumptionStatus;
use AdminUser\Domain\Models\RegistrationToken\ExpiredAt;
use AdminUser\Domain\Models\RegistrationToken\HashedTokenValue;
use AdminUser\Domain\Models\RegistrationToken\RegistrationToken;
use AdminUser\Domain\Models\RegistrationToken\RegistrationTokenId;
use AdminUser\Domain\Models\Role;
use AdminUser\Infrastructures\RegistrationToken\RegistrationTokenRepository;
use App\Models\AdminUser\RegistrationToken as ModelsRegistrationToken;
use App\Models\AdminUser\RegistrationTokenPermission;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Support\Contracts\Uuid\UuidConverterInterface;
use Tests\Support\DatabaseTestCase;

class RegistrationTokenRepositoryTest extends DatabaseTestCase
{
    #[Test]
    public function findByEmailForUpdate(): void
    {
        $repository = $this->getInstance();

        $token = $this->buildToken('invitee@example.com', 'hashed-token-value', ConsumptionStatus::Unused, ['read_admin_user']);

        $repository->save($token);

        $found = $repository->findByEmailForUpdate(Email::reconstruct('invitee@example.com'));

        $this->assertNotNull($found);
        $this->assertTrue($found->equals($token));
        $this->assertSame('invitee@example.com', $found->email->value);
        $this->assertSame('hashed-token-value', $found->token->value);
        $this->assertSame(Role::General, $found->role);
        $this->assertSame(['read_admin_user'], $found->permissions->toArray());
        $this->assertSame(ConsumptionStatus::Unused, $found->status);
    }

    #[Test]
    public function findByEmailForUpdateNotFound(): void
    {
        $found = $this->getInstance()->findByEmailForUpdate(Email::reconstruct('no-such@example.com'));

        $this->assertNull($found);
    }

    #[Test]
    public function findByEmailForUpdateReturnsLatestRowWhenMultipleExist(): void
    {
        $repository = $this->getInstance();

        $older = $this->buildToken('invitee@example.com', 'old-hashed-token', ConsumptionStatus::Consumed);
        $repository->save($older);

        $newerId = $this->generateUuid();
        $converter = $this->app->make(UuidConverterInterface::class);
        ModelsRegistrationToken::query()->insert([
            'admin_user_registration_token_id' => $converter->toBin($newerId),
            'token' => 'new-hashed-token',
            'email' => 'invitee@example.com',
            'role' => Role::General->value,
            'expired_at' => new DateTimeImmutable('+7 days')->format('Y-m-d H:i:s'),
            'status' => ConsumptionStatus::Unused->value,
            'created_at' => new DateTimeImmutable('+1 hour')->format('Y-m-d H:i:s'),
            'updated_at' => new DateTimeImmutable('+1 hour')->format('Y-m-d H:i:s'),
        ]);

        $found = $repository->findByEmailForUpdate(Email::reconstruct('invitee@example.com'));

        $this->assertNotNull($found);
        $this->assertSame($newerId, $found->registrationTokenId->value);
        $this->assertSame('new-hashed-token', $found->token->value);
        $this->assertSame(ConsumptionStatus::Unused, $found->status);
    }

    #[Test]
    public function findUnusedByEmailForUpdateReturnsAllUnusedRows(): void
    {
        $repository = $this->getInstance();

        $consumed = $this->buildToken('invitee@example.com', 'consumed-hashed-token', ConsumptionStatus::Consumed);
        $unused = $this->buildToken('invitee@example.com', 'unused-hashed-token', ConsumptionStatus::Unused);
        $otherEmail = $this->buildToken('other@example.com', 'other-hashed-token', ConsumptionStatus::Unused);

        $repository->save($consumed);
        $repository->save($unused);
        $repository->save($otherEmail);

        $found = $repository->findUnusedByEmailForUpdate(Email::reconstruct('invitee@example.com'));

        $this->assertCount(1, $found);
        $this->assertTrue($found[0]->equals($unused));
    }

    #[Test]
    public function findByEmailForUpdateIssuesSelectForUpdate(): void
    {
        $repository = $this->getInstance();

        $token = $this->buildToken('invitee@example.com', 'hashed-token-value', ConsumptionStatus::Unused);
        $repository->save($token);

        DB::enableQueryLog();

        $repository->findByEmailForUpdate(Email::reconstruct('invitee@example.com'));

        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        $selectQueries = array_values(array_filter(
            $queries,
            fn (array $query): bool => str_starts_with(strtolower((string)$query['query']), 'select')
                && str_contains((string)$query['query'], 'admin_user_registration_tokens'),
        ));

        $this->assertNotSame([], $selectQueries);
        $this->assertStringContainsString('for update', strtolower((string)$selectQueries[0]['query']));
    }

    #[Test]
    public function saveConsumedTokenUpdatesStatus(): void
    {
        $repository = $this->getInstance();

        $token = $this->buildToken('invitee@example.com', 'hashed-token-value', ConsumptionStatus::Unused);
        $repository->save($token);

        $repository->save($token->consume());

        $found = $repository->findByEmailForUpdate(Email::reconstruct('invitee@example.com'));

        $this->assertNotNull($found);
        $this->assertSame(ConsumptionStatus::Consumed, $found->status);
        $this->assertSame(1, ModelsRegistrationToken::query()->count());
    }

    #[Test]
    public function savePreservesExistingPermissionsOnUpdate(): void
    {
        $repository = $this->getInstance();

        $token = $this->buildToken('invitee@example.com', 'hashed-token-value', ConsumptionStatus::Unused, ['read_admin_user']);
        $repository->save($token);

        $repository->save($token->consume());

        $permissions = RegistrationTokenPermission::query()
            ->where('admin_user_registration_token_id', $this->app->make(UuidConverterInterface::class)->toBin($token->registrationTokenId->value))
            ->pluck('permission')
            ->all();

        $this->assertEqualsCanonicalizing(['read_admin_user'], $permissions);
    }

    /**
     * @param list<string> $permissions
     */
    private function buildToken(string $email, string $hashedToken, ConsumptionStatus $status, array $permissions = []): RegistrationToken
    {
        return new RegistrationToken(
            RegistrationTokenId::reconstruct($this->generateUuid()),
            HashedTokenValue::reconstruct($hashedToken),
            Email::reconstruct($email),
            Role::General,
            Permissions::reconstruct($permissions),
            ExpiredAt::reconstruct(new DateTimeImmutable('+7 days')),
            $status,
        );
    }

    private function getInstance(): RegistrationTokenRepository
    {
        return $this->app->make(RegistrationTokenRepository::class);
    }
}
