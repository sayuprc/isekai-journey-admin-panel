<?php

declare(strict_types=1);

namespace Tests\Unit\Shared\Domain;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Shared\Domain\StringValueObject;
use Tests\TestCase;

class StringValueObjectTest extends TestCase
{
    #[Test]
    #[DataProvider('validData')]
    public function canBeInstanced(string $value): void
    {
        $string = new StringObject($value);

        $this->assertSame($value, $string->value);
    }

    public static function validData(): array
    {
        return [
            [''],
            ['value'],
        ];
    }
}

class StringObject extends StringValueObject
{
}
