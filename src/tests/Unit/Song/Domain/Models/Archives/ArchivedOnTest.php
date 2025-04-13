<?php

declare(strict_types=1);

namespace Tests\Unit\Song\Domain\Models\Archives;

use DateTime;
use PHPUnit\Framework\Attributes\Test;
use Song\Domain\Models\Archives\ArchivedOn;
use Support\Domain\ValueObjects\DateTime\DateValueObject;
use Tests\TestCase;

class ArchivedOnTest extends TestCase
{
    #[Test]
    public function isExtendsSpecificClass(): void
    {
        $this->assertInstanceOf(DateValueObject::class, new ArchivedOn(new DateTime()));
    }
}
