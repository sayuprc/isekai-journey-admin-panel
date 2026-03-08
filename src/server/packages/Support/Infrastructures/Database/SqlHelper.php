<?php

declare(strict_types=1);

namespace Support\Infrastructures\Database;

/**
 * SQL クエリ生成時に使用する汎用ヘルパークラス
 */
class SqlHelper
{
    /**
     * LIKE 句で使用する特殊文字をエスケープする
     *
     * SQL の LIKE 句で特殊な意味を持つ文字 (%, _, \) をエスケープします。
     * これにより、ユーザー入力を安全に LIKE 検索に使用できます。
     *
     * @pure
     *
     * @param string $keyword エスケープする文字列
     *
     * @return string エスケープされた文字列
     */
    public static function escapeLike(string $keyword): string
    {
        return addcslashes($keyword, '%_\\');
    }
}
