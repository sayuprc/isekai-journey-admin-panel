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
        $result = $this->getInstance()->create('ヰ世界情緒', 1);

        $this->assertTrue($result->isOk());

        $performer = $result->unwrap();

        $this->assertSame('ヰ世界情緒', $performer->performerName->value);
    }

    #[Test]
    public function reconstitute(): void
    {
        $result = $this->getInstance()->reconstitute('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', 'ヰ世界情緒', 1);

        $this->assertTrue($result->isOk());

        $performer = $result->unwrap();

        $this->assertSame('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', $performer->performerId->value);
        $this->assertSame('ヰ世界情緒', $performer->performerName->value);
        $this->assertSame(1, $performer->orderNo->value);
    }

    private function getInstance(): PerformerFactory
    {
        return $this->app->make(PerformerFactory::class);
    }
}
