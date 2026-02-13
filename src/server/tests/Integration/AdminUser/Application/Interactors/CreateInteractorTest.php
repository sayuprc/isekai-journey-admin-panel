<?php

declare(strict_types=1);

namespace Tests\Integration\AdminUser\Application\Interactors;

use AdminUser\Application\Interactors\CreateInteractor;
use AdminUser\Application\UseCase\Create\CreateInputData;
use AdminUser\DebugInfrastructures\FileAdminUserRepository;
use AdminUser\Domain\Models\AdminUser;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\FileRepositoryTransaction;
use Tests\TestCase;

class CreateInteractorTest extends TestCase
{
    use EntityFactory;
    use FileRepositoryTransaction;

    #[Test]
    public function canCreate(): void
    {
        $result = $this->getInstance()->handle(new CreateInputData('example@example.com', 'plain'));

        $this->assertTrue($result->isOk());

        $users = $this->getAll(AdminUser::class, FileAdminUserRepository::class);
        $this->assertCount(1, $users);
        $this->assertSame('example@example.com', $users[array_key_first($users)]->email->value);
        $this->assertNotSame('plain', $users[array_key_first($users)]->hashedPassword->value);
    }

    private function getInstance(): CreateInteractor
    {
        return $this->app->make(CreateInteractor::class);
    }
}
