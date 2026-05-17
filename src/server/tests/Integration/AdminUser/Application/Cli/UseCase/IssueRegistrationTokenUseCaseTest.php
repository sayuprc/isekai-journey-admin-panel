<?php

declare(strict_types=1);

namespace Tests\Integration\AdminUser\Application\Cli\UseCase;

use AdminUser\Application\Cli\UseCase\IssueRegistrationToken\IssueRegistrationTokenInputData;
use AdminUser\Application\Cli\UseCase\IssueRegistrationToken\IssueRegistrationTokenUseCase;
use AdminUser\Domain\Models\Permission;
use AdminUser\Domain\Models\RegistrationToken\ConsumptionStatus;
use AdminUser\Domain\Models\Role;
use App\Models\AdminUser\RegistrationToken as ModelsRegistrationToken;
use App\Models\AdminUser\RegistrationTokenPermission;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\DatabaseTestCase;

class IssueRegistrationTokenUseCaseTest extends DatabaseTestCase
{
    #[Test]
    public function canIssue(): void
    {
        $result = $this->getInstance()->handle(
            new IssueRegistrationTokenInputData('invitee@example.com', Role::General->value, []),
        );

        $this->assertTrue($result->isOk());

        $rows = ModelsRegistrationToken::query()->get()->all();
        $this->assertCount(1, $rows);

        $row = array_first($rows);
        $plain = $result->unwrap()->plainToken;

        $this->assertNotSame($plain, $row->token);
        $this->assertTrue(Hash::check($plain, $row->token));
        $this->assertSame('invitee@example.com', $row->email);
        $this->assertSame(Role::General->value, $row->role);
        $this->assertSame(ConsumptionStatus::Unused->value, $row->status);
        $this->assertGreaterThan(now(), $row->expired_at);
        $this->assertSame(0, RegistrationTokenPermission::query()->count());
    }

    #[Test]
    public function canIssueWithPrivilegeAndPermissions(): void
    {
        $result = $this->getInstance()->handle(
            new IssueRegistrationTokenInputData(
                'priv@example.com',
                Role::Privilege->value,
                [Permission::ReadAdminUser->value, Permission::WriteAdminUser->value],
            ),
        );

        $this->assertTrue($result->isOk());

        $rows = ModelsRegistrationToken::query()->get()->all();
        $this->assertCount(1, $rows);

        $row = array_first($rows);
        $this->assertSame(Role::Privilege->value, $row->role);

        $permissions = RegistrationTokenPermission::query()
            ->where('admin_user_registration_token_id', $row->admin_user_registration_token_id)
            ->pluck('permission')
            ->all();

        $this->assertEqualsCanonicalizing(
            [Permission::ReadAdminUser->value, Permission::WriteAdminUser->value],
            $permissions,
        );
    }

    private function getInstance(): IssueRegistrationTokenUseCase
    {
        return $this->app->make(IssueRegistrationTokenUseCase::class);
    }
}
