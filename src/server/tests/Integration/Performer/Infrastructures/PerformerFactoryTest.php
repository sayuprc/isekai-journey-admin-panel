<?php

declare(strict_types=1);

namespace Tests\Integration\Performer\Infrastructures;

use Performer\Domain\Models\PerformerId;
use Performer\Domain\Models\PerformerName;
use Performer\Infrastructures\PerformerFactory;
use PHPUnit\Framework\Attributes\Test;
use Support\Domain\ValueObjects\OrderNo;
use Tests\TestCase;

class PerformerFactoryTest extends TestCase
{
    #[Test]
    public function create(): void
    {
        $performer = $this->getInstance()->create(
            PerformerId::reconstruct('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'),
            PerformerName::reconstruct('ヰ世界情緒'),
            OrderNo::reconstruct(1),
        );

        $this->assertSame('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', $performer->performerId->value);
        $this->assertSame('ヰ世界情緒', $performer->performerName->value);
        $this->assertSame(1, $performer->orderNo->value);
    }

    private function getInstance(): PerformerFactory
    {
        return $this->app->make(PerformerFactory::class);
    }
}
