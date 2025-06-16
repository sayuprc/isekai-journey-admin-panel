<?php

declare(strict_types=1);

namespace Tests\Unit\Creator\Infrastructures;

use Creator\Infrastructures\CreatorFactory;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Support\Contracts\UuidGeneratorInterface;
use Tests\TestCase;

class CreatorFactoryTest extends TestCase
{
    private MockInterface&UuidGeneratorInterface $uuid;

    private CreatorFactory $factory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->uuid = Mockery::mock(UuidGeneratorInterface::class);

        $this->factory = new CreatorFactory($this->uuid);
    }

    #[Test]
    public function create(): void
    {
        $this->uuid->shouldReceive('generate')
            ->andReturn('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA')
            ->once();

        $creator = $this->factory->create('クリエイター');

        $this->assertSame('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', $creator->creatorId->value);
        $this->assertSame('クリエイター', $creator->creatorName->value);
    }

    #[Test]
    public function reconstitute(): void
    {
        $creator = $this->factory->reconstitute('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', 'クリエイター');

        $this->assertSame('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', $creator->creatorId->value);
        $this->assertSame('クリエイター', $creator->creatorName->value);
    }
}
