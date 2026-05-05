<?php

declare(strict_types=1);

namespace Media\Domain\Models;

use Media\Domain\Criteria\MediaSearchCriteria;

interface MediaRepositoryInterface
{
    public function find(MediaId $mediaId): ?Media;

    /**
     * @return list<Media>
     */
    public function search(MediaSearchCriteria $criteria): array;

    public function maxPage(MediaSearchCriteria $criteria): int;

    /**
     * @return list<Media>
     */
    public function findByIds(MediaId ...$mediaIds): array;

    public function save(Media $media): Media;
}
