<?php

declare(strict_types=1);

namespace Tests\Integration\User\Application\Interactors;

use PHPUnit\Framework\Attributes\Test;
use Tests\Support\FileRepositoryTransaction;
use Tests\TestCase;
use User\Application\Interactors\CreateInteractor;
use User\Application\UseCase\Create\CreateInputData;
use User\DebugInfrastructures\FileUserRepository;
use User\Domain\Models\Email;
use User\Domain\Models\HashedPassword;
use User\Domain\Models\User;
use User\Domain\Models\UserId;

class CreateInteractorTest extends TestCase
{
    use FileRepositoryTransaction;

    #[Test]
    public function canCreate(): void
    {
        $result = $this->getInstance()->handle(new CreateInputData('example@example.com', 'plain'));

        $this->assertTrue($result->isOk());

        /** @var array<User> $users */
        $users = $this->getAll(FileUserRepository::class);
        $this->assertCount(1, $users);
        $this->assertSame('example@example.com', $users[array_key_first($users)]->email->value);
        $this->assertNotSame('plain', $users[array_key_first($users)]->hashedPassword->value);
    }

    #[Test]
    public function createFailsIfEmailAlreadyExists(): void
    {
        $uuid = $this->generateUuid();
        $this->factory(
            FileUserRepository::class,
            $uuid,
            new User(new UserId($uuid), new Email('example@example.com'), new HashedPassword('hashed'))
        );

        $result = $this->getInstance()->handle(new CreateInputData('example@example.com', 'plainpassword'));

        $this->assertTrue($result->isErr());
    }

    private function getInstance(): CreateInteractor
    {
        return $this->app->make(CreateInteractor::class);
    }
}
