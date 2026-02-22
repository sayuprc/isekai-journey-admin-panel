<?php

declare(strict_types=1);

namespace Tests\Unit\Performer\Infrastructures;

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
            PerformerId::create('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA')->unwrap(),
            PerformerName::create('共演者')->unwrap(),
            OrderNo::create(1)->unwrap(),
        );

        $this->assertSame('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', $performer->performerId->value);
        $this->assertSame('共演者', $performer->name->value);
        $this->assertSame(1, $performer->orderNo->value);
    }

    private function getInstance(): PerformerFactory
    {
        return new PerformerFactory();
    }
}
