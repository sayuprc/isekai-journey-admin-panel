<?php

declare(strict_types=1);

namespace Release\Infrastructures;

use App\Models\Release\Release as ModelsRelease;
use DateType\ImmutableDate;
use Illuminate\Support\Facades\DB;
use Release\Domain\Models\Release;
use Release\Domain\Models\ReleaseId;
use Release\Domain\Models\ReleaseRepositoryInterface;
use Support\Contracts\Uuid\UuidConverterInterface;

readonly class ReleaseRepository implements ReleaseRepositoryInterface
{
    public function __construct(private UuidConverterInterface $converter)
    {
    }

    public function find(ReleaseId $releaseId): ?Release
    {
        $found = ModelsRelease::query()
            ->with('trackEntries')
            ->where('release_id', $this->converter->toBin($releaseId->value))
            ->first();

        if (is_null($found)) {
            return null;
        }

        return $this->hydrate($found);
    }

    public function save(Release $release): Release
    {
        $releaseId = $this->converter->toBin($release->releaseId->value);
        $releasePayload = [
            'release_id' => $releaseId,
            'title' => $release->title->value,
            'type' => $release->type->value,
            'distribution_type' => $release->distributionType->value,
            'released_on' => $release->releasedOn->value->format('Y-m-d'),
            'description' => $release->description->value,
            'is_display' => $release->isDisplay,
        ];

        DB::transaction(function () use ($release, $releaseId, $releasePayload): void {
            ModelsRelease::query()->upsert(
                [[
                    ...$releasePayload,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]],
                ['release_id'],
                [
                    'title',
                    'type',
                    'distribution_type',
                    'released_on',
                    'description',
                    'is_display',
                    'updated_at',
                ],
            );

            $model = ModelsRelease::query()
                ->where('release_id', $releaseId)
                ->firstOrFail();

            $model->trackEntries()->delete();

            $entries = array_map(
                fn (array $trackEntry): array => [
                    'release_id' => $releaseId,
                    'song_id' => $this->converter->toBin($trackEntry['song_id']),
                    'track_no' => $trackEntry['track_no'],
                ],
                $release->trackEntries->toArray(),
            );

            if ($entries !== []) {
                $model->trackEntries()->createMany($entries);
            }
        });

        return $release;
    }

    public function delete(ReleaseId $releaseId): void
    {
        ModelsRelease::query()
            ->where('release_id', $this->converter->toBin($releaseId->value))
            ->delete();
    }

    private function hydrate(ModelsRelease $row): Release
    {
        return Release::reconstruct(
            $this->converter->toUuid($row->release_id),
            $row->title,
            $row->type,
            $row->distribution_type,
            ImmutableDate::createFromInterface($row->released_on),
            $row->description,
            $row->is_display,
            array_values($row->trackEntries
                ->map(fn ($trackEntry): array => [
                    'songId' => $this->converter->toUuid($trackEntry->song_id),
                    'trackNo' => $trackEntry->track_no,
                ])
                ->all()),
        );
    }
}
