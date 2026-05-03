<?php

declare(strict_types=1);

namespace App\Console\Commands\AdminUser;

use AdminUser\Application\UseCase\Create\CreateInputData;
use AdminUser\Application\UseCase\Create\CreateUseCase;
use AdminUser\Domain\Models\Permission;
use AdminUser\Domain\Models\Role;
use Illuminate\Console\Command;
use Override;
use Support\UseCase\Error\BusinessLogicError;
use Support\UseCase\Error\InvalidInputError;
use Support\UseCase\Error\UseCaseError;

class CreateCommand extends Command
{
    #[Override]
    protected $signature = 'admin:create {name} {email} {password} {--p|privilege} {permissions?*}';

    #[Override]
    protected $description = '管理ユーザーを作成する';

    public function handle(CreateUseCase $useCase): int
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

        $password = $this->argument('password');

        if (mb_trim($password) === '') {
            $this->error('パスワードを入力してください');

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

        $result = $useCase->handle(new CreateInputData($name, $email, $password, $role->value, $permissions));

        if ($result->isErr()) {
            $this->error($this->resolveErrorMessage($result->unwrapErr()));

            return Command::FAILURE;
        }

        $this->info('管理ユーザーを作成しました');

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
