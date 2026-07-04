<?php

declare(strict_types=1);

namespace Release\Infrastructures\Admin;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Override;
use Release\Application\Admin\Query\ReleaseGroupDetailQueryServiceInterface;
use Release\Application\Admin\Query\ReleaseGroupReferencedRelease;
use Release\Domain\Models\ReleaseGroupId;
use Support\Contracts\Uuid\UuidConverterInterface;

readonly class ReleaseGroupDetailQueryService implements ReleaseGroupDetailQueryServiceInterface
{
    public function __construct(private UuidConverterInterface $converter)
    {
    }

    #[Override]
    public function findReferencedReleases(ReleaseGroupId $releaseGroupId): array
    {
        /** @var Collection<int, object{release_id: string, name: string, released_on: string, is_display: int}> $releaseRows */
        $releaseRows = DB::table('releases')
            ->where('release_group_id', $this->converter->toBin($releaseGroupId->value))
            ->orderBy('released_on')
            ->orderBy('name')
            ->get(['release_id', 'name', 'released_on', 'is_display']);

        if ($releaseRows->isEmpty()) {
            return [];
        }

        /** @var Collection<int, object{release_id: string, format: int}> $mediumRows */
        $mediumRows = DB::table('release_media')
            ->whereIn('release_id', $releaseRows->pluck('release_id')->all())
            ->orderBy('position')
            ->get(['release_id', 'format']);

        $formatsByRelease = [];

        foreach ($mediumRows as $mediumRow) {
            $formatsByRelease[$mediumRow->release_id][] = $mediumRow->format;
        }

        return array_values(
            $releaseRows
                ->map(fn (object $row): ReleaseGroupReferencedRelease => new ReleaseGroupReferencedRelease(
                    $this->converter->toUuid($row->release_id),
                    $row->name,
                    $row->released_on,
                    (bool)$row->is_display,
                    $formatsByRelease[$row->release_id] ?? [],
                ))
                ->all(),
        );
    }
}
