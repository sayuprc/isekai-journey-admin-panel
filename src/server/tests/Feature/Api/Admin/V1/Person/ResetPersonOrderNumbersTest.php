<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Admin\V1\Person;

use Person\Domain\Models\PersonId;
use Person\Infrastructures\PersonRepository;
use Person\Route\PersonRouteMap;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\Api\Admin\WithAuth;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;

class ResetPersonOrderNumbersTest extends DatabaseTestCase
{
    use EntityFactory;
    use WithAuth;

    #[Test]
    public function resetsOrderNumbersInStepsOfTen(): void
    {
        $repository = $this->app->make(PersonRepository::class);

        $id1 = $this->generateUuid();
        $id2 = $this->generateUuid();
        $id3 = $this->generateUuid();

        $repository->save($this->createPerson($id1, '人物A', 3));
        $repository->save($this->createPerson($id2, '人物B', 7));
        $repository->save($this->createPerson($id3, '人物C', 100));

        $this->withAuth()
            ->postJson(route(PersonRouteMap::ResetOrderNumbers))
            ->assertStatus(200)
            ->assertExactJson([
                'updatedCount' => 3,
            ]);

        $this->assertSame(10, $repository->find(PersonId::reconstruct($id1))?->orderNo->value);
        $this->assertSame(20, $repository->find(PersonId::reconstruct($id2))?->orderNo->value);
        $this->assertSame(30, $repository->find(PersonId::reconstruct($id3))?->orderNo->value);
    }

    #[Test]
    public function returnsZeroWhenAlreadySpaced(): void
    {
        $repository = $this->app->make(PersonRepository::class);

        $repository->save($this->createPerson($this->generateUuid(), '人物A', 10));
        $repository->save($this->createPerson($this->generateUuid(), '人物B', 20));

        $this->withAuth()
            ->postJson(route(PersonRouteMap::ResetOrderNumbers))
            ->assertStatus(200)
            ->assertExactJson([
                'updatedCount' => 0,
            ]);
    }
}
