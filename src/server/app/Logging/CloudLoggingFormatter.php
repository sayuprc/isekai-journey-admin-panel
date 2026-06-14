<?php

declare(strict_types=1);

namespace App\Logging;

use Monolog\Formatter\GoogleCloudLoggingFormatter;
use Monolog\LogRecord;

/**
 * Cloud Logging 互換の構造化ログを出力するフォーマッタ。
 *
 * 標準の {@see GoogleCloudLoggingFormatter} は severity / time を付与するが、
 * トレース相関に使う特殊フィールドはペイロード直下に置く必要がある。
 * Cloud Logging は context / extra に入れ子になった値を解釈しないため、
 * 共有コンテキストへ積んだ trace / spanId をトップレベルへ昇格させる。
 *
 * @see https://cloud.google.com/logging/docs/structured-logging
 */
final class CloudLoggingFormatter extends GoogleCloudLoggingFormatter
{
    private const HOISTED_KEYS = [
        'logging.googleapis.com/trace',
        'logging.googleapis.com/spanId',
    ];

    /**
     * @return array<array-key, mixed>
     */
    protected function normalizeRecord(LogRecord $record): array
    {
        $normalized = parent::normalizeRecord($record);

        $context = $normalized['context'] ?? null;

        if (! is_array($context)) {
            return $normalized;
        }

        foreach (self::HOISTED_KEYS as $key) {
            $value = $context[$key] ?? null;

            unset($context[$key]);

            // ワーカー再利用で前リクエストの値が残るのを避けるため null は捨てる
            if (! is_null($value) && $value !== '') {
                $normalized[$key] = $value;
            }
        }

        $normalized['context'] = $context;

        return $normalized;
    }
}
