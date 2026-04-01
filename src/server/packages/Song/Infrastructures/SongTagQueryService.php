<?php

declare(strict_types=1);

namespace Song\Infrastructures;

use App\Models\Song\SongTag as ModelsSongTag;
use Illuminate\Database\Eloquent\Builder;
use Override;
use Song\Application\Query\SongTagQueryServiceInterface;
use Song\Domain\Criteria\SongTagSearchCriteria;
use Song\Domain\Models\Tag\SongTag;
use Song\Domain\Models\Tag\SongTagId;
use Song\Domain\Models\Tag\SongTagName;
use Support\Contracts\Uuid\UuidConverterInterface;
use Support\Domain\ValueObjects\OrderNo;
use Support\Infrastructures\Database\SqlHelper;

readonly class SongTagQueryService implements SongTagQueryServiceInterface
{
    public function __construct(private UuidConverterInterface $converter)
    {
    }

    #[Override]
    public function search(SongTagSearchCriteria $criteria): array
    {
        $offset = ($criteria->page - 1) * $criteria->perPage->value;

        return $this->buildQuery($criteria)
            ->orderBy($criteria->sort->value, $criteria->order->value)
            ->limit($criteria->perPage->value)
            ->offset($offset)
            ->get()
            ->map($this->hydrate(...))
            ->all();
    }

    #[Override]
    public function maxPage(SongTagSearchCriteria $criteria): int
    {
        return (int)ceil($this->buildQuery($criteria)->count() / $criteria->perPage->value);
    }

    /**
     * @return Builder<ModelsSongTag>
     */
    private function buildQuery(SongTagSearchCriteria $criteria): Builder
    {
        $query = ModelsSongTag::query();

        if ($criteria->name->isPresent()) {
            $keyword = SqlHelper::escapeLike(mb_strtolower($criteria->name->get()));
            $query = $query->whereLike('name_lower', $keyword . '%');
        }

        return $query;
    }

    private function hydrate(ModelsSongTag $row): SongTag
    {
        return new SongTag(
            SongTagId::reconstruct($this->converter->toUuid($row->song_tag_id)),
            SongTagName::reconstruct($row->name),
            OrderNo::reconstruct($row->order_no),
        );
    }
}
