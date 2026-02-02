<?php

declare(strict_types=1);

namespace Tests\Integration\Creator\DebugInfrastructures;

use Creator\DebugInfrastructures\FileCreatorRepository;
use Creator\Domain\Models\Creator;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\FileRepositoryTransaction;
use Tests\TestCase;

class FileCreatorRepositoryTest extends TestCase
{
    use EntityFactory;
    use FileRepositoryTransaction;

    #[Test]
    public function all(): void
    {
        $creator1 = $this->createCreator($this->generateUuid(), 'ヰ世界情緒');
        $creator2 = $this->createCreator($this->generateUuid(), '香椎モイミ');

        $this->store($creator1, $creator2);

        $creators = $this->getInstance()->all();

        $this->assertCount(2, $creators);
        $this->assertEquals([$creator1, $creator2], $creators);
    }

    #[Test]
    public function find(): void
    {
        $creator = $this->createCreator($this->generateUuid(), 'ヰ世界情緒');

        $this->store($creator);

        $found = $this->getInstance()->find($creator->creatorId);

        $this->assertNotNull($found);
        $this->assertEquals($creator, $found);
    }

    #[Test]
    public function findByName(): void
    {
        $creator = $this->createCreator($this->generateUuid(), 'ヰ世界情緒');

        $this->store($creator);

        $found = $this->getInstance()->findByName($creator->creatorName);

        $this->assertNotNull($found);
        $this->assertEquals($creator, $found);
    }

    #[Test]
    public function save(): void
    {
        $creator = $this->createCreator($this->generateUuid(), 'ヰ世界情緒');

        $this->getInstance()->save($creator);

        $found = $this->getInstance()->find($creator->creatorId);

        $this->assertNotNull($found);
        $this->assertEquals($creator, $found);
    }

    #[Test]
    public function deleting(): void
    {
        $creator = $this->createCreator($this->generateUuid(), 'ヰ世界情緒');

        $this->store($creator);

        $this->getInstance()->delete($creator->creatorId);

        $found = $this->getInstance()->find($creator->creatorId);

        $this->assertNull($found);
    }

    private function store(Creator ...$creators): void
    {
        array_map(
            fn (Creator $creator) => $this->factory(FileCreatorRepository::class, $creator->creatorId->value, $creator),
            $creators,
        );
    }

    private function getInstance(): FileCreatorRepository
    {
        return $this->app->make(FileCreatorRepository::class);
    }
}
