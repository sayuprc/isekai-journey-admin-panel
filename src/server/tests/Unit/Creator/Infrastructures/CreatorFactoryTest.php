<?php

declare(strict_types=1);

namespace Tests\Unit\Creator\Infrastructures;

use Creator\Domain\Models\CreatorId;
use Creator\Domain\Models\CreatorName;
use Creator\Infrastructures\CreatorFactory;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreatorFactoryTest extends TestCase
{
    #[Test]
    public function create(): void
    {
        $creator = $this->getInstance()->create(
            CreatorId::create('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA')->unwrap(),
            CreatorName::create('クリエイター')->unwrap(),
        );

        $this->assertSame('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', $creator->creatorId->value);
        $this->assertSame('クリエイター', $creator->creatorName->value);
    }

    private function getInstance(): CreatorFactory
    {
        return new CreatorFactory();
    }
}
