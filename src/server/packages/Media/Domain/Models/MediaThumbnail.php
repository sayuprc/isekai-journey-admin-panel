<?php

declare(strict_types=1);

namespace Media\Domain\Models;

use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Support\Domain\Error\EntityRuleViolationError;
use Support\Domain\ValueObjects\String\StringValueObject;

readonly class MediaThumbnail extends StringValueObject
{
    /**
     * @return Result<self, EntityRuleViolationError>
     */
    public static function fromYouTubeUrl(MediaUrl $url): Result
    {
        $query = parse_url($url->value, PHP_URL_QUERY);

        if (! is_string($query)) {
            return new Err(new EntityRuleViolationError('thumbnailUrl', 'YouTube の動画IDを取得できません'));
        }

        parse_str($query, $params);
        $videoId = $params['v'] ?? null;

        if (! is_string($videoId) || $videoId === '') {
            return new Err(new EntityRuleViolationError('thumbnailUrl', 'YouTube の動画IDを取得できません'));
        }

        return new Ok(self::reconstruct("https://i.ytimg.com/vi/{$videoId}/sddefault.jpg"));
    }
}
