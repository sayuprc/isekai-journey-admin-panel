<?php

declare(strict_types=1);

namespace App\Console\Commands\AdminUser;

use AdminUser\Application\Cli\UseCase\Invite\InviteInputData;
use AdminUser\Application\Cli\UseCase\Invite\InviteUseCase;
use AdminUser\Domain\Models\Permission;
use AdminUser\Domain\Models\Role;
use Illuminate\Console\Command;
use Override;
use Support\UseCase\Error\BusinessLogicError;
use Support\UseCase\Error\InvalidInputError;
use Support\UseCase\Error\UseCaseError;

class InviteCommand extends Command
{
    #[Override]
    protected $signature = 'admin:invite {--p|privilege} {--e|expires-in-hours=24} {permissions?*}';

    #[Override]
    protected $description = '管理ユーザーの招待トークンを発行する';

    public function handle(InviteUseCase $useCase): int
    {
        $expiresInHours = (int)$this->option('expires-in-hours');

        if ($expiresInHours <= 0) {
            $this->error('有効期限は 1 時間以上で指定してください');

            return Command::FAILURE;
        }

        $role = $this->isPrivilege()
            ? Role::Privilege
            : Role::General;

        $permissions = $this->argument('permissions');
        assert(array_is_list($permissions));

        foreach ($permissions as $permission) {
            $result = Permission::tryFrom($permission);
            if (is_null($result)) {
                $this->error("不正な権限です: {$permission}");

                return Command::FAILURE;
            }
        }

        $result = $useCase->handle(new InviteInputData($role->value, $permissions, $expiresInHours));

        if ($result->isErr()) {
            $this->error($this->resolveErrorMessage($result->unwrapErr()));

            return Command::FAILURE;
        }

        $output = $result->unwrap();

        $this->info('管理ユーザーの招待トークンを発行しました');
        $this->info("token: {$output->plainToken->value}");
        $this->info('expires_at: ' . $output->expiresAt->value->format('Y-m-d H:i:s'));

        return Command::SUCCESS;
    }

    private function resolveErrorMessage(UseCaseError $error): string
    {
        if ($error instanceof BusinessLogicError) {
            return $error->message;
        }

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

    private function isPrivilege(): bool
    {
        return (bool)$this->option('privilege');
    }
}
