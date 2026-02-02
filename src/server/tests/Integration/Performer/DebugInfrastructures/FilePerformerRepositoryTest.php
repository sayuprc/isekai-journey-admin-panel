<?php

declare(strict_types=1);

namespace Tests\Integration\Performer\DebugInfrastructures;

use Performer\DebugInfrastructures\FilePerformerRepository;
use Performer\Domain\Models\Performer;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\FileRepositoryTransaction;
use Tests\TestCase;

class FilePerformerRepositoryTest extends TestCase
{
    use EntityFactory;
    use FileRepositoryTransaction;

    #[Test]
    public function all(): void
    {
        $performer1 = $this->createPerformer($this->generateUuid(), 'ヰ世界情緒', 1);
        $performer2 = $this->createPerformer($this->generateUuid(), '春猿火', 2);

        $this->store($performer1, $performer2);

        $performers = $this->getInstance()->all();

        $this->assertCount(2, $performers);
        $this->assertEquals([$performer1, $performer2], $performers);
    }

    #[Test]
    public function find(): void
    {
        $performer = $this->createPerformer($this->generateUuid(), 'ヰ世界情緒', 1);

        $this->store($performer);

        $found = $this->getInstance()->find($performer->performerId);

        $this->assertNotNull($found);
        $this->assertEquals($performer, $found);
    }

    #[Test]
    public function findByName(): void
    {
        $performer = $this->createPerformer($this->generateUuid(), 'ヰ世界情緒', 1);

        $this->store($performer);

        $found = $this->getInstance()->findByName($performer->performerName);

        $this->assertNotNull($found);
        $this->assertEquals($performer, $found);
    }

    #[Test]
    public function save(): void
    {
        $performer = $this->createPerformer($this->generateUuid(), 'ヰ世界情緒', 1);

        $this->getInstance()->save($performer);

        $found = $this->getInstance()->find($performer->performerId);

        $this->assertNotNull($found);
        $this->assertEquals($performer, $found);
    }

    #[Test]
    public function deleting(): void
    {
        $performer = $this->createPerformer($this->generateUuid(), 'ヰ世界情緒', 1);

        $this->store($performer);

        $this->getInstance()->delete($performer->performerId);

        $found = $this->getInstance()->find($performer->performerId);

        $this->assertNull($found);
    }

    private function store(Performer ...$performers): void
    {
        array_map(
            fn (Performer $performer) => $this->factory(FilePerformerRepository::class, $performer->performerId->value, $performer),
            $performers,
        );
    }

    private function getInstance(): FilePerformerRepository
    {
        return $this->app->make(FilePerformerRepository::class);
    }
}
