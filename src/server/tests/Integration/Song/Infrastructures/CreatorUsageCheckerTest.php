<?php

declare(strict_types=1);

namespace Tests\Integration\Song\Infrastructures;

use Creator\Domain\Models\CreatorId;
use PHPUnit\Framework\Attributes\Test;
use Song\DebugInfrastructures\FileSongRepository;
use Song\Infrastructures\CreatorUsageChecker;
use SongType\Domain\Models\SongType;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\FileRepositoryTransaction;
use Tests\TestCase;

class CreatorUsageCheckerTest extends TestCase
{
    use EntityFactory;
    use FileRepositoryTransaction;

    #[Test]
    public function isUsed(): void
    {
        $creatorId = $this->generateUuid();

        $this->factory(FileSongRepository::class, $this->createSong(
            $this->generateUuid(),
            '曲名',
            '説明',
            SongType::Original,
            1,
            [['creatorId' => $creatorId, 'orderNo' => 1]],
            [],
            [],
        )->toArray());

        $result = $this->getInstance()->isUsed(CreatorId::reconstruct($creatorId));

        $this->assertTrue($result);
    }

    #[Test]
    public function isNotUsed(): void
    {
        $creatorId = $this->generateUuid();

        $result = $this->getInstance()->isUsed(CreatorId::reconstruct($creatorId));

        $this->assertFalse($result);
    }

    private function getInstance(): CreatorUsageChecker
    {
        return $this->app->make(CreatorUsageChecker::class);
    }
}
