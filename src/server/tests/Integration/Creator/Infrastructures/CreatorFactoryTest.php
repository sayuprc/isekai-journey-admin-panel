<?php

declare(strict_types=1);

namespace Tests\Integration\Creator\Infrastructures;

use Creator\Domain\Models\CreatorId;
use Creator\Domain\Models\CreatorName;
use Creator\Infrastructures\CreatorFactory;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreatorFactoryTest extends TestCase
{
    #[Test]
    public function create(): void
    {
        $creator = $this->getInstance()->create(
            CreatorId::reconstruct('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'),
            CreatorName::reconstruct('ヰ世界情緒'),
        );

        $this->assertSame('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', $creator->creatorId->value);
        $this->assertSame('ヰ世界情緒', $creator->creatorName->value);
    }

    private function getInstance(): CreatorFactory
    {
        return $this->app->make(CreatorFactory::class);
    }
}
