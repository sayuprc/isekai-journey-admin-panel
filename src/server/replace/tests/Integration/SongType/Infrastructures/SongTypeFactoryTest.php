<?php

declare(strict_types=1);

namespace Tests\Integration\SongType\Infrastructures;

use PHPUnit\Framework\Attributes\Test;
use SongType\Infrastructures\SongTypeFactory;
use Tests\TestCase;

class SongTypeFactoryTest extends TestCase
{
    #[Test]
    public function create(): void
    {
        $songType = $this->getInstance()->create('オリジナル', 1);

        $this->assertSame('オリジナル', $songType->songTypeName->value);
        $this->assertSame(1, $songType->orderNo->value);
    }

    #[Test]
    public function reconstitute(): void
    {
        $songType = $this->getInstance()->reconstitute('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', 'オリジナル', 1);

        $this->assertSame('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', $songType->songTypeId->value);
        $this->assertSame('オリジナル', $songType->songTypeName->value);
        $this->assertSame(1, $songType->orderNo->value);
    }

    private function getInstance(): SongTypeFactory
    {
        return $this->container->get(SongTypeFactory::class);
    }
}
