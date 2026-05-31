<?php

declare(strict_types=1);

namespace Auth\Infrastructures\RecoveryCode;

use Auth\Domain\Services\RecoveryCode\RandomRecoveryCodeGeneratorInterface;
use Override;

readonly class RandomRecoveryCodeGenerator implements RandomRecoveryCodeGeneratorInterface
{
    // 紛らわしい 0/O/1/I/L を除外した文字集合 (Crockford base32 風)。
    private const string ALPHABET = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

    private const int GROUP_LENGTH = 4;

    private const int GROUP_COUNT = 2;

    #[Override]
    public function generate(): string
    {
        $groups = [];

        for ($g = 0; $g < self::GROUP_COUNT; $g++) {
            $chars = '';

            for ($i = 0; $i < self::GROUP_LENGTH; $i++) {
                $chars .= self::ALPHABET[random_int(0, strlen(self::ALPHABET) - 1)];
            }

            $groups[] = $chars;
        }

        return implode('-', $groups);
    }
}
