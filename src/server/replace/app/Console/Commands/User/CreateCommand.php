<?php

declare(strict_types=1);

namespace App\Console\Commands\User;

use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;
use User\Application\UseCase\Create\CreateInputData;
use User\Application\UseCase\Create\CreateUseCaseInterface;

readonly class CreateCommand
{
    use HasConsole;

    public function __construct(private CreateUseCaseInterface $interactor)
    {
    }

    #[ConsoleCommand(name: 'user:create', description: '管理ユーザーを作成する')]
    public function __invoke(string $email, string $password): void
    {
        $result = $this->interactor->handle(new CreateInputData($email, $password));

        $result->match(
            fn () => $this->console->success('管理ユーザーを作成しました'),
            fn (string $message) => $this->console->error($message),
        );
    }
}
