<?php

declare(strict_types=1);

namespace App\Console\Commands\AdminUser;

use AdminUser\Application\Cli\UseCase\RevokeRegistrationToken\RevokeRegistrationTokenInputData;
use AdminUser\Application\Cli\UseCase\RevokeRegistrationToken\RevokeRegistrationTokenUseCase;
use Illuminate\Console\Command;
use Override;
use Support\UseCase\Error\InvalidInputError;
use Support\UseCase\Error\UseCaseError;

class RevokeInviteCommand extends Command
{
    #[Override]
    protected $signature = 'admin:invite:revoke {email}';

    #[Override]
    protected $description = '未使用の管理ユーザー登録トークンを無効化する';

    public function handle(RevokeRegistrationTokenUseCase $useCase): int
    {
        $email = $this->argument('email');

        if (mb_trim($email) === '') {
            $this->error('メールアドレスを入力してください');

            return Command::FAILURE;
        }

        $result = $useCase->handle(new RevokeRegistrationTokenInputData($email));

        if ($result->isErr()) {
            $this->error($this->resolveErrorMessage($result->unwrapErr()));

            return Command::FAILURE;
        }

        $this->info(sprintf('無効化した登録トークン数: %d', $result->unwrap()->revokedCount));

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
}
