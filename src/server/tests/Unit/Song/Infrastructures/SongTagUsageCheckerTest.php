<?php

declare(strict_types=1);

namespace Tests\Unit\Song\Infrastructures;

use Mockery;
use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\Test;
use Song\Domain\Models\SongRepositoryInterface;
use Song\Domain\Models\Tag\SongTagId;
use Song\Infrastructures\SongTagUsageChecker;
use Tests\TestCase;

class SongTagUsageCheckerTest extends TestCase
{
    private MockInterface&SongRepositoryInterface $repository;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(SongRepositoryInterface::class);
    }

    #[Test]
    public function isUsed(): void
    {
        $songTagId = SongTagId::reconstruct('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA');

        $this->repository->shouldReceive('isSongTagUsed')
            ->with($songTagId)
            ->andReturn(true)
            ->once();

        $result = $this->getInstance()->isUsed($songTagId);

        $this->assertTrue($result);
    }

    #[Test]
    public function isNotUsed(): void
    {
        $songTagId = SongTagId::reconstruct('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA');

        $this->repository->shouldReceive('isSongTagUsed')
            ->with($songTagId)
            ->andReturn(false)
            ->once();

        $result = $this->getInstance()->isUsed($songTagId);

        $this->assertFalse($result);
    }

    private function getInstance(): SongTagUsageChecker
    {
        return new SongTagUsageChecker($this->repository);
    }
}
