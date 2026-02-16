<?php

declare(strict_types=1);

namespace App\Console\Commands\AdminUser;

use AdminUser\Application\UseCase\Create\CreateInputData;
use AdminUser\Application\UseCase\Create\CreateUseCaseInterface;
use Illuminate\Console\Command;
use Support\UseCase\Error\InvalidInputError;
use Support\UseCase\Error\UseCaseError;

class CreateCommand extends Command
{
    protected $signature = 'admin:create {email} {password}';

    protected $description = '管理ユーザーを作成する';

    public function handle(CreateUseCaseInterface $interactor): int
    {
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

        $result = $interactor->handle(new CreateInputData($email, $password));

        if ($result->isErr()) {
            $this->error($this->resolveErrorMessage($result->unwrapErr()));

            return Command::FAILURE;
        }

        $this->info('管理ユーザーを作成しました');

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
