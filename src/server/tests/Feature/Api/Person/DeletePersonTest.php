<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Person;

use Person\Route\PersonRouteMap;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\Api\WithAuth;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class DeletePersonTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;
    use WithAuth;

    #[Test]
    public function canDelete(): void
    {
        $uuid = $this->generateUuid();

        $this->storePersons($this->createPerson($uuid, '人物', 1));

        $this->withAuth()
            ->delete(route(PersonRouteMap::Delete, $uuid))
            ->assertStatus(204);
    }

    #[Test]
    public function invalidPersonId(): void
    {
        $this->withAuth()
            ->delete(route(PersonRouteMap::Delete, 'invalid-id'))
            ->assertStatus(422)
            ->assertExactJson([
                'errors' => [
                    [
                        'field' => '',
                        'message' => '予期せぬエラー',
                    ],
                ],
            ]);
    }
}
