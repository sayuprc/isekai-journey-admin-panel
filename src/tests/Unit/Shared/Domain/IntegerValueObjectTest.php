<?php

declare(strict_types=1);

namespace Tests\Unit\Shared\Domain;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Shared\Domain\IntegerValueObject;
use Tests\TestCase;

class IntegerValueObjectTest extends TestCase
{
    #[Test]
    #[DataProvider('validData')]
    public function canBeInstanced(int $value): void
    {
        $string = new IntegerObject($value);

        $this->assertSame($value, $string->value);
    }

    public static function validData(): array
    {
        return [
            [-1],
            [0],
            [1],
        ];
    }
}

class IntegerObject extends IntegerValueObject
{
}
