<?php

declare(strict_types=1);

namespace Tests\Unit\Support\Result;

use LogicException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use stdClass;
use Support\Result\Err;
use Support\Result\Result;
use Tests\TestCase;

class ErrTest extends TestCase
{
    #[Test]
    public function isImplementsSpecificInterface(): void
    {
        $this->assertInstanceOf(Result::class, new Err(null));
    }

    #[Test]
    public function isOk(): void
    {
        $this->assertFalse(new Err(null)->isOk());
    }

    #[Test]
    #[DataProvider('provideGetErr')]
    public function getErr(mixed $value): void
    {
        $this->assertSame($value, new Err($value)->getErr());
    }

    public static function provideGetErr(): array
    {
        return [
            [null],
            [1],
            ['string value'],
            [new stdClass()],
            [new ErrValue(1, 'name')],
        ];
    }

    #[Test]
    public function throwInGetValue(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Cannot get value from Err result');

        new Err(null)->getValue();
    }
}

class ErrValue
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
    ) {
    }
}
