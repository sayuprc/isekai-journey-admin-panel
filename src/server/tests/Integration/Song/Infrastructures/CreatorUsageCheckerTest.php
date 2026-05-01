<?php

declare(strict_types=1);

namespace Tests\Integration\Song\Infrastructures;

use Creator\Domain\Models\CreatorId;
use PHPUnit\Framework\Attributes\Test;
use Song\Domain\Models\SongType;
use Song\Infrastructures\CreatorUsageChecker;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class CreatorUsageCheckerTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;

    #[Test]
    public function isUsed(): void
    {
        $creatorId = $this->generateUuid();

        $this->storeCreators($this->createCreator($creatorId, 'テスト', 1));
        $this->storeSongs($this->createSong(
            $this->generateUuid(),
            '曲名',
            '説明',
            SongType::Original,
            1,
            [['creatorId' => $creatorId, 'orderNo' => 1]],
            [],
            [],
        ));

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
