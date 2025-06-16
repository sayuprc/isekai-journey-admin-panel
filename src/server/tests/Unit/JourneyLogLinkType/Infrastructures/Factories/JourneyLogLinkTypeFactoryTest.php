<?php

declare(strict_types=1);

namespace Tests\Unit\JourneyLogLinkType\Infrastructures\Factories;

use JourneyLogLinkType\Infrastructures\Factories\JourneyLogLinkTypeFactory;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Support\Contracts\UuidGeneratorInterface;
use Tests\TestCase;

class JourneyLogLinkTypeFactoryTest extends TestCase
{
    private MockInterface&UuidGeneratorInterface $uuid;

    private JourneyLogLinkTypeFactory $factory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->uuid = Mockery::mock(UuidGeneratorInterface::class);

        $this->factory = new JourneyLogLinkTypeFactory($this->uuid);
    }

    #[Test]
    public function create(): void
    {
        $this->uuid->shouldReceive('generate')
            ->andReturn('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA')
            ->once();

        $journeyLogLinkType = $this->factory->create(
            'リンク',
            1,
        );

        $this->assertSame('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', $journeyLogLinkType->journeyLogLinkTypeId->value);
        $this->assertSame('リンク', $journeyLogLinkType->journeyLogLinkTypeName->value);
        $this->assertSame(1, $journeyLogLinkType->orderNo->value);
    }

    #[Test]
    public function reconstitute(): void
    {
        $journeyLogLinkType = $this->factory->reconstitute(
            'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA',
            'リンク',
            1,
        );

        $this->assertSame('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', $journeyLogLinkType->journeyLogLinkTypeId->value);
        $this->assertSame('リンク', $journeyLogLinkType->journeyLogLinkTypeName->value);
        $this->assertSame(1, $journeyLogLinkType->orderNo->value);
    }
}
