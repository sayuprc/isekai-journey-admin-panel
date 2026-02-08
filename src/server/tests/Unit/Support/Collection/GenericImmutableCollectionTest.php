<?php

declare(strict_types=1);

namespace Tests\Unit\Support\Collection;

use PHPUnit\Framework\Attributes\Test;
use Support\Collection\GenericImmutableCollection;
use Tests\TestCase;

class GenericImmutableCollectionTest extends TestCase
{
    #[Test]
    public function map(): void
    {
        $collection = new GenericImmutableCollection([1, 2, 3]);

        $result = $collection->map(fn (int $i): int => $i + 2);

        $this->assertEquals(new GenericImmutableCollection([3, 4, 5]), $result);
    }

    #[Test]
    public function toArray(): void
    {
        $collection = new GenericImmutableCollection([1, 2, 3]);

        $this->assertEquals([1, 2, 3], $collection->toArray());
    }
}
