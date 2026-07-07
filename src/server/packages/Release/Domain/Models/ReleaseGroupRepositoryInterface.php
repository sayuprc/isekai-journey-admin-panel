<?php

declare(strict_types=1);

namespace Release\Domain\Models;

interface ReleaseGroupRepositoryInterface
{
    public function find(ReleaseGroupId $releaseGroupId): ?ReleaseGroup;

    /**
     * 新規作成用の表示順を採番する (既存の最大 order_no + 10)
     */
    public function nextOrderNo(): int;

    public function save(ReleaseGroup $releaseGroup): ReleaseGroup;

    public function delete(ReleaseGroupId $releaseGroupId): void;
}
