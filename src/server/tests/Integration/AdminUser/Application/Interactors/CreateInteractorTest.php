<?php

declare(strict_types=1);

namespace Tests\Integration\AdminUser\Application\Interactors;

use AdminUser\Application\Interactors\CreateInteractor;
use AdminUser\Application\UseCase\Create\CreateInputData;
use AdminUser\Domain\Models\Role;
use App\Models\AdminUser\AdminUser as ModelsAdminUser;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\DatabaseTestCase;

class CreateInteractorTest extends DatabaseTestCase
{
    #[Test]
    public function canCreate(): void
    {
        $result = $this->getInstance()->handle(new CreateInputData('テストユーザー', 'example@example.com', 'plain', Role::General->value, []));

        $this->assertTrue($result->isOk());

        $adminUsers = ModelsAdminUser::query()->get()->all();
        $this->assertCount(1, $adminUsers);
        $this->assertSame('example@example.com', array_first($adminUsers)->email);
        $this->assertSame(Role::General->value, array_first($adminUsers)->role);
    }

    private function getInstance(): CreateInteractor
    {
        return $this->app->make(CreateInteractor::class);
    }
}
