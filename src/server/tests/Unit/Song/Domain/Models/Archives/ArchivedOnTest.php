<?php

declare(strict_types=1);

namespace Tests\Unit\Song\Domain\Models\Archives;

use DateType\ImmutableDate;
use PHPUnit\Framework\Attributes\Test;
use Song\Domain\Models\Archives\ArchivedOn;
use Support\Domain\ValueObjects\Date\ImmutableDateValueObject;
use Tests\TestCase;

class ArchivedOnTest extends TestCase
{
    #[Test]
    public function isExtendsSpecificClass(): void
    {
        $this->assertInstanceOf(ImmutableDateValueObject::class, new ArchivedOn(new ImmutableDate()));
    }
}
