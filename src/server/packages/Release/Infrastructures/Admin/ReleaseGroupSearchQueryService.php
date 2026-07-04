<?php

declare(strict_types=1);

namespace Release\Infrastructures\Admin;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Override;
use Release\Application\Admin\Query\ReleaseGroupSearchQueryServiceInterface;
use Release\Application\Admin\Query\ReleaseGroupSummary;
use Release\Domain\Criteria\ReleaseGroupSearchCriteria;
use Support\Contracts\Uuid\UuidConverterInterface;
use Support\Infrastructures\Database\SqlHelper;

readonly class ReleaseGroupSearchQueryService implements ReleaseGroupSearchQueryServiceInterface
{
    public function __construct(private UuidConverterInterface $converter)
    {
    }

    #[Override]
    public function search(ReleaseGroupSearchCriteria $criteria): array
    {
        $offset = ($criteria->page - 1) * $criteria->perPage->value;

        /** @var Collection<int, object{release_group_id: string, title: string, type: int, description: string, is_display: int, first_released_on: string|null}> $rows */
        $rows = $this->buildSearchQuery($criteria)
            ->leftJoin('releases', 'releases.release_group_id', '=', 'release_groups.release_group_id')
            ->groupBy(
                'release_groups.release_group_id',
                'release_groups.title',
                'release_groups.type',
                'release_groups.description',
                'release_groups.is_display',
            )
            // 最古発売日の降順（リリース未登録のグループは末尾）、同日はタイトル昇順。
            ->orderByDesc('first_released_on')
            ->orderBy('release_groups.title')
            ->limit($criteria->perPage->value)
            ->offset($offset)
            ->get([
                'release_groups.release_group_id',
                'release_groups.title',
                'release_groups.type',
                'release_groups.description',
                'release_groups.is_display',
                DB::raw('MIN(releases.released_on) as first_released_on'),
            ]);

        return array_values(
            $rows
                ->map(fn (object $row): ReleaseGroupSummary => new ReleaseGroupSummary(
                    $this->converter->toUuid($row->release_group_id),
                    $row->title,
                    $row->type,
                    $row->description,
                    (bool)$row->is_display,
                    $row->first_released_on,
                ))
                ->all(),
        );
    }

    #[Override]
    public function maxPage(ReleaseGroupSearchCriteria $criteria): int
    {
        $count = $this->buildSearchQuery($criteria)->count();

        return (int)ceil($count / $criteria->perPage->value);
    }

    private function buildSearchQuery(ReleaseGroupSearchCriteria $criteria): Builder
    {
        $query = DB::table('release_groups');

        if ($criteria->title->isPresent()) {
            $keyword = SqlHelper::escapeLike($criteria->title->get());
            $query = $query->where('release_groups.title', 'LIKE', '%' . $keyword . '%');
        }

        if ($criteria->type->isPresent()) {
            $query = $query->where('release_groups.type', '=', $criteria->type->get()->value);
        }

        if ($criteria->isDisplay->isPresent()) {
            $query = $query->where('release_groups.is_display', '=', $criteria->isDisplay->get());
        }

        return $query;
    }
}
