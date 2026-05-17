<?php

declare(strict_types=1);

namespace Tests\Unit\AdminUser\Domain\Models\Invitation;

use AdminUser\Domain\Models\Invitation\PlainToken;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PlainTokenTest extends TestCase
{
    #[Test]
    #[DataProvider('validProvider')]
    public function createSucceedsForValidValue(string $value): void
    {
        $result = PlainToken::create($value);

        $this->assertTrue($result->isOk());
        $this->assertSame($value, $result->unwrap()->value);
    }

    public static function validProvider(): array
    {
        return [
            ['0123456789abcdef0123456789abcdef0123456789abcdef0123456789abcdef'],
            [str_repeat('a', 64)],
        ];
    }

    #[Test]
    #[DataProvider('invalidProvider')]
    public function createFailsForInvalidValue(string $value): void
    {
        $result = PlainToken::create($value);

        $this->assertTrue($result->isErr());
    }

    public static function invalidProvider(): array
    {
        return [
            '空文字' => [''],
            '短すぎる' => [str_repeat('a', 63)],
            '長すぎる' => [str_repeat('a', 65)],
            'hex 以外の文字' => [str_repeat('g', 64)],
            '大文字を含む' => [str_repeat('A', 64)],
        ];
    }
}
