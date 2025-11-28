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
        $creator = $this->getInstance()->create('ヰ世界情緒', 1);

        $this->assertSame('ヰ世界情緒', $creator->performerName->value);
    }

    private function getInstance(): PerformerFactory
    {
        return $this->app->make(PerformerFactory::class);
    }
}
