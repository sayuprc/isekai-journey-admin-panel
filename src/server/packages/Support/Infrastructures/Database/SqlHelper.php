<?php

declare(strict_types=1);

namespace Support\Infrastructures\Database;

/**
 * SQL クエリ生成時に使用する汎用ヘルパークラス
 */
class SqlHelper
{
    /**
     * @pure
     */
    public static function escapeLike(string $keyword): string
    {
        return addcslashes($keyword, '%_\\');
    }
}
