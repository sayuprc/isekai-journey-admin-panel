<?php

declare(strict_types=1);

namespace Support\Path;

class Path
{
    private static ?string $root = null;

    public static function setRoot(string $root): void
    {
        self::$root = rtrim($root, DIRECTORY_SEPARATOR);
    }

    public static function base(string $path): string
    {
        if (is_null(self::$root)) {
            throw new UndefinedRootException();
        }

        return self::$root . DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR);
    }
}
