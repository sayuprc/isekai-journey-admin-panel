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
        $result = $this->getInstance()->create('ヰ世界情緒');

        $this->assertTrue($result->isOk());

        $creator = $result->unwrap();

        $this->assertSame('ヰ世界情緒', $creator->creatorName->value);
    }

    #[Test]
    public function reconstitute(): void
    {
        $result = $this->getInstance()->reconstitute('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', 'ヰ世界情緒');

        $this->assertTrue($result->isOk());

        $creator = $result->unwrap();

        $this->assertSame('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', $creator->creatorId->value);
        $this->assertSame('ヰ世界情緒', $creator->creatorName->value);
    }

    private function getInstance(): CreatorFactory
    {
        return $this->app->make(CreatorFactory::class);
    }
}
