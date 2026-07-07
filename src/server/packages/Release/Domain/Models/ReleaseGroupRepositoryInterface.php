<?php

declare(strict_types=1);

namespace Release\Domain\Models;

interface ReleaseGroupRepositoryInterface
{
    public function find(ReleaseGroupId $releaseGroupId): ?ReleaseGroup;

    /**
     * 登録済みの最大 order_no を返す (未登録なら 0)
     */
    public function maxOrderNo(): int;

    public function save(ReleaseGroup $releaseGroup): ReleaseGroup;

    public function delete(ReleaseGroupId $releaseGroupId): void;
}
