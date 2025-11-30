<?php

declare(strict_types=1);

namespace Tests\Integration\Performer\Infrastructures;

use Performer\Infrastructures\PerformerFactory;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PerformerFactoryTest extends TestCase
{
    #[Test]
    public function create(): void
    {
        $performer = $this->getInstance()->create('ヰ世界情緒', 1);

        $this->assertSame('ヰ世界情緒', $performer->performerName->value);
    }

    #[Test]
    public function reconstitute(): void
    {
        $performer = $this->getInstance()->reconstitute('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', 'ヰ世界情緒', 1);

        $this->assertSame('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', $performer->performerId->value);
        $this->assertSame('ヰ世界情緒', $performer->performerName->value);
        $this->assertSame(1, $performer->orderNo->value);
    }

    private function getInstance(): PerformerFactory
    {
        return $this->app->make(PerformerFactory::class);
    }
}
