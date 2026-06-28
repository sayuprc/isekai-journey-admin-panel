<?php

declare(strict_types=1);

namespace Media\Domain\Models;

enum MediaPlatform: int
{
    case YouTube = 1;

    case X = 2;

    case Other = 99;

    public function getName(): string
    {
        return match ($this) {
            self::YouTube => 'YouTube',
            self::X => 'X',
            self::Other => 'その他',
        };
    }

    public function equals(self $other): bool
    {
        return $this === $other;
    }

    public static function fromUrl(MediaUrl $url): self
    {
        $host = strtolower((string)(parse_url($url->value, PHP_URL_HOST) ?? ''));

        if ($host === 'youtube.com' || str_ends_with($host, '.youtube.com')) {
            return self::YouTube;
        }

        if (
            $host === 'x.com'
            || str_ends_with($host, '.x.com')
            || $host === 'twitter.com'
            || str_ends_with($host, '.twitter.com')
        ) {
            return self::X;
        }

        return self::Other;
    }
}
