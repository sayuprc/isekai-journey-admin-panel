<?php

declare(strict_types=1);

namespace Tests\Unit\Song\Infrastructures;

use Creator\Domain\Models\CreatorId;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Song\Domain\Models\SongRepositoryInterface;
use Song\Infrastructures\CreatorUsageChecker;
use Tests\TestCase;

class CreatorUsageCheckerTest extends TestCase
{
    private MockInterface&SongRepositoryInterface $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(SongRepositoryInterface::class);
    }

    #[Test]
    public function isUsed(): void
    {
        $creatorId = CreatorId::reconstruct('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA');

        $this->repository->shouldReceive('isCreatorUsed')
            ->with($creatorId)
            ->andReturn(true)
            ->once();

        $result = $this->getInstance()->isUsed($creatorId);

        $this->assertTrue($result);
    }

    #[Test]
    public function isNotUsed(): void
    {
        $creatorId = CreatorId::reconstruct('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA');

        $this->repository->shouldReceive('isCreatorUsed')
            ->with($creatorId)
            ->andReturn(false)
            ->once();

        $result = $this->getInstance()->isUsed($creatorId);

        $this->assertFalse($result);
    }

    private function getInstance(): CreatorUsageChecker
    {
        return new CreatorUsageChecker($this->repository);
    }
}
