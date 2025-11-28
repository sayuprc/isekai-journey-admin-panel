<?php

declare(strict_types=1);

namespace Tests\Unit\Performer\Infrastructures;

use Mockery;
use Mockery\MockInterface;
use Performer\Infrastructures\PerformerFactory;
use PHPUnit\Framework\Attributes\Test;
use Support\Contracts\UuidGeneratorInterface;
use Tests\TestCase;

class PerformerFactoryTest extends TestCase
{
    private MockInterface&UuidGeneratorInterface $uuid;

    private PerformerFactory $factory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->uuid = Mockery::mock(UuidGeneratorInterface::class);

        $this->factory = new PerformerFactory($this->uuid);
    }

    #[Test]
    public function create(): void
    {
        $this->uuid->shouldReceive('generate')
            ->andReturn('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA')
            ->once();

        $performer = $this->factory->create('パフォーマー', 1);

        $this->assertSame('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', $performer->performerId->value);
        $this->assertSame('パフォーマー', $performer->performerName->value);
        $this->assertSame(1, $performer->orderNo->value);
    }
}
