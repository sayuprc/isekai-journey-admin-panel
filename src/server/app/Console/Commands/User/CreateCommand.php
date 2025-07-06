<?php

declare(strict_types=1);

namespace App\Console\Commands\User;

use Illuminate\Console\Command;
use User\Application\UseCase\Create\CreateInputData;
use User\Application\UseCase\Create\CreateUseCaseInterface;

class CreateCommand extends Command
{
    protected $signature = 'user:create {email} {password}';

    protected $description = 'ユーザーを作成する';

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
            $this->error($result->unwrapErr());

            return Command::FAILURE;
        }

        $this->info('ユーザーを作成しました');

        return Command::SUCCESS;
    }
}
