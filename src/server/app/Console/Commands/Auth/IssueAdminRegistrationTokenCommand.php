<?php

declare(strict_types=1);

namespace App\Console\Commands\Auth;

use AdminUser\Domain\Models\Role;
use Auth\Application\Cli\UseCase\IssueAdminRegistrationToken\IssueAdminRegistrationTokenInputData;
use Auth\Application\Cli\UseCase\IssueAdminRegistrationToken\IssueAdminRegistrationTokenUseCase;
use Illuminate\Console\Command;
use Override;
use Support\UseCase\Error\InvalidInputError;
use Support\UseCase\Error\UseCaseError;

class IssueAdminRegistrationTokenCommand extends Command
{
    #[Override]
    protected $signature = 'admin:invite {email} {--p|privilege}';

    #[Override]
    protected $description = '管理画面ユーザー向けの登録トークンを発行する';

    public function handle(IssueAdminRegistrationTokenUseCase $useCase): int
    {
        $email = $this->argument('email');

        if (mb_trim($email) === '') {
            $this->error('メールアドレスを入力してください');

            return Command::FAILURE;
        }

        $result = $useCase->handle(new IssueAdminRegistrationTokenInputData(
            $email,
            $this->isPrivilege() ? Role::Privilege->value : Role::General->value,
        ));

        if ($result->isErr()) {
            $this->error($this->resolveErrorMessage($result->unwrapErr()));

            return Command::FAILURE;
        }

        $this->line('登録トークン: ' . $result->unwrap()->plainToken);
        $this->info('管理画面ユーザー登録トークンを発行しました');

        return Command::SUCCESS;
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

    private function isPrivilege(): bool
    {
        return (bool) $this->option('privilege');
    }
}
