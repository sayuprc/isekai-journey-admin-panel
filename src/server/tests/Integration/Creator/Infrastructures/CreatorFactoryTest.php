<?php

declare(strict_types=1);

namespace Tests\Integration\Creator\Infrastructures;

use Creator\Infrastructures\CreatorFactory;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreatorFactoryTest extends TestCase
{
    #[Test]
    public function create(): void
    {
        $creator = $this->getInstance()->create('ヰ世界情緒');

        $this->assertSame('ヰ世界情緒', $creator->creatorName->value);
    }

    #[Test]
    public function reconstitute(): void
    {
        $creator = $this->getInstance()->reconstitute('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', 'ヰ世界情緒');

        $this->assertSame('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', $creator->creatorId->value);
        $this->assertSame('ヰ世界情緒', $creator->creatorName->value);
    }

    private function getInstance(): CreatorFactory
    {
        return $this->app->make(CreatorFactory::class);
    }
}
