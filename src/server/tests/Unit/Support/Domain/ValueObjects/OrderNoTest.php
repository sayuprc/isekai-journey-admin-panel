<?php

declare(strict_types=1);

namespace Tests\Unit\Support\Domain\ValueObjects;

use PHPUnit\Framework\Attributes\Test;
use Support\Domain\ValueObjects\Numeric\PositiveIntegerValueObject;
use Support\Domain\ValueObjects\OrderNo;
use Tests\TestCase;

class OrderNoTest extends TestCase
{
    #[Test]
    public function isExtendsSpecificClass(): void
    {
        $this->assertInstanceOf(PositiveIntegerValueObject::class, new OrderNo(1));
    }
}
