<?php

declare(strict_types=1);

namespace App\Console\Commands\AdminUser;

use AdminUser\Application\UseCase\IssueRegistrationToken\IssueRegistrationTokenInputData;
use AdminUser\Application\UseCase\IssueRegistrationToken\IssueRegistrationTokenUseCase;
use AdminUser\Domain\Models\Permission;
use AdminUser\Domain\Models\Role;
use Illuminate\Console\Command;
use Override;
use Support\UseCase\Error\InvalidInputError;
use Support\UseCase\Error\UseCaseError;

class InviteCommand extends Command
{
    #[Override]
    protected $signature = 'admin:invite
        {name : 管理ユーザー名}
        {email : メールアドレス}
        {role : role.general | role.console | role.privilege}
        {permissions?* : 一般ロールに付与する権限}
        {--expires-in-minutes=60 : トークン有効期限（分）}';

    #[Override]
    protected $description = '管理ユーザー登録用トークンを発行する';

    public function handle(IssueRegistrationTokenUseCase $useCase): int
    {
        $name = $this->argument('name');
        if (mb_trim($name) === '') {
            $this->error('管理ユーザー名を入力してください');

            return Command::FAILURE;
        }

        $email = $this->argument('email');
        if (mb_trim($email) === '') {
            $this->error('メールアドレスを入力してください');

            return Command::FAILURE;
        }

        $role = $this->resolveRole($this->argument('role'));
        if (is_null($role)) {
            $this->error('不正なロールです');

            return Command::FAILURE;
        }

        $permissions = $this->argument('permissions');
        assert(array_is_list($permissions));

        foreach ($permissions as $permission) {
            if (is_null(Permission::tryFrom($permission))) {
                $this->error("不正な権限です: {$permission}");

                return Command::FAILURE;
            }
        }

        $expiresInMinutes = (int)$this->option('expires-in-minutes');

        $result = $useCase->handle(
            new IssueRegistrationTokenInputData(
                $name,
                $email,
                $role->value,
                $permissions,
                $expiresInMinutes,
            ),
        );

        if ($result->isErr()) {
            $this->error($this->resolveErrorMessage($result->unwrapErr()));

            return Command::FAILURE;
        }

        $output = $result->unwrap();

        $this->info('管理ユーザー登録トークンを発行しました');
        $this->line('token: ' . $output->plainToken);
        $this->line('expires_in_minutes: ' . $expiresInMinutes);
        $this->line('role: ' . $role->name);

        return Command::SUCCESS;
    }

    private function resolveRole(string $value): ?Role
    {
        return match ($value) {
            'role.general', 'general' => Role::General,
            'role.console', 'console' => Role::Console,
            'role.privilege', 'privilege' => Role::Privilege,
            default => null,
        };
    }

    private function resolveErrorMessage(UseCaseError $error): string
    {
        if (! $error instanceof InvalidInputError) {
            return '';
        }

        foreach ($error->errors as $messages) {
            if ($messages !== []) {
                return $messages[0];
            }
        }

        return '';
    }
}
