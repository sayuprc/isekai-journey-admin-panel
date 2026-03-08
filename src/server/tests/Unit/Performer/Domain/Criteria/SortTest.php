<?php

declare(strict_types=1);

namespace Tests\Unit\Performer\Domain\Criteria;

use Performer\Domain\Criteria\Sort;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SortTest extends TestCase
{
    #[Test]
    public function isNameReturnsTrueForName(): void
    {
        $this->assertTrue(Sort::Name->isName());
    }

    #[Test]
    public function isNameReturnsFalseForOrderNo(): void
    {
        $this->assertFalse(Sort::OrderNo->isName());
    }

    #[Test]
    public function isOrderNoReturnsTrueForOrderNo(): void
    {
        $this->assertTrue(Sort::OrderNo->isOrderNo());
    }

    #[Test]
    public function isOrderNoReturnsFalseForName(): void
    {
        $this->assertFalse(Sort::Name->isOrderNo());
    }
}
