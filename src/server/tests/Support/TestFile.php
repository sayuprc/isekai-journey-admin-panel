<?php

declare(strict_types=1);

namespace Tests\Support;

use Closure;

trait TestFile
{
    /**
     * @param Closure(string, mixed): void $closure
     */
    private function runWithTemporaryFile(Closure $closure, string $path, mixed $content = null): void
    {
        file_put_contents($path, $content);

        try {
            $closure($path, $content);
        } finally {
            unlink($path);
        }
    }

    /**
     * @param Closure(string): void $closure
     */
    private function runWithNonexistentFile(Closure $closure, string $path): void
    {
        if (file_exists($path)) {
            unlink($path);
        }

        $closure($path);
    }
}
