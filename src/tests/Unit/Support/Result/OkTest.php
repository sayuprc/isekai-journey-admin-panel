<?php

declare(strict_types=1);

namespace Tests\Unit\Support\Result;

use LogicException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use stdClass;
use Support\Result\Ok;
use Support\Result\Result;
use Tests\TestCase;

class OkTest extends TestCase
{
    #[Test]
    public function isImplementsSpecificInterface(): void
    {
        $this->assertInstanceOf(Result::class, new Ok(null));
    }

    #[Test]
    public function isOk(): void
    {
        $this->assertTrue(new Ok(null)->isOk());
    }

    #[Test]
    #[DataProvider('provideGetValue')]
    public function getValue(mixed $value): void
    {
        $this->assertSame($value, new Ok($value)->getValue());
    }

    public static function provideGetValue(): array
    {
        return [
            [null],
            [1],
            ['string value'],
            [new stdClass()],
            [new OkValue(1, 'name')],
        ];
    }

    #[Test]
    public function throwInGetErr(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Cannot get error from Ok result');

        new Ok(null)->getErr();
    }
}

class OkValue
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
    ) {
    }
}
