<?php

declare(strict_types=1);

namespace App\Console\Commands\AdminUser;

use AdminUser\Application\Cli\UseCase\IssueRegistrationToken\IssueRegistrationTokenInputData;
use AdminUser\Application\Cli\UseCase\IssueRegistrationToken\IssueRegistrationTokenUseCase;
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
    protected $signature = 'admin:invite {email} {--p|privilege} {permissions?*}';

    #[Override]
    protected $description = '管理ユーザー登録トークンを発行する';

    public function handle(IssueRegistrationTokenUseCase $useCase): int
    {
        $email = $this->argument('email');

        if (mb_trim($email) === '') {
            $this->error('メールアドレスを入力してください');

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

        $result = $useCase->handle(new IssueRegistrationTokenInputData($email, $role->value, $permissions));

        if ($result->isErr()) {
            $this->error($this->resolveErrorMessage($result->unwrapErr()));

            return Command::FAILURE;
        }

        $output = $result->unwrap();

        $this->line($output->plainToken);
        $this->info(sprintf('有効期限: %s', $output->token->expiredAt->value->format('Y-m-d H:i:s')));

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
