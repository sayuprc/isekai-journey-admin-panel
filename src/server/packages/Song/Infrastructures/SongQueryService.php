<?php

declare(strict_types=1);

namespace Song\Infrastructures;

use App\Models\Song\Song;
use Illuminate\Database\Eloquent\Builder;
use Song\Application\Query\SongQueryServiceInterface;
use Song\Application\Query\SongSummary;
use Song\Domain\Criteria\SongSearchCriteria;
use Song\Domain\Models\SongAttribute;
use Song\Domain\Models\SongType;
use Support\Contracts\Uuid\UuidConverterInterface;
use Support\Infrastructures\Database\SqlHelper;
use Override;

readonly class SongQueryService implements SongQueryServiceInterface
{
    public function __construct(private UuidConverterInterface $converter)
    {
    }

    #[Override]
    public function search(SongSearchCriteria $criteria): array
    {
        $query = $this->buildQuery($criteria);

        $offset = ($criteria->page - 1) * $criteria->perPage->value;

        return $query->orderBy($criteria->sort->value, $criteria->order->value)
            ->limit($criteria->perPage->value)
            ->offset($offset)
            ->get()
            ->map($this->hydrate(...))
            ->all();
    }

    #[Override]
    public function maxPage(SongSearchCriteria $criteria): int
    {
        $query = $this->buildQuery($criteria);

        return (int)ceil($query->count() / $criteria->perPage->value);
    }

    /**
     * @return Builder<Song>
     */
    private function buildQuery(SongSearchCriteria $criteria): Builder
    {
        $query = Song::query()
            ->select(['song_id', 'title', 'type', 'attribute', 'order_no']);

        if ($criteria->title->isPresent()) {
            // 前方一致検索でインデックスを活用
            // 中間一致が必要な場合は、外部の検索エンジン（Elasticsearch など）を利用すること
            $keyword = SqlHelper::escapeLike(mb_strtolower($criteria->title->get()));
            $query = $query->whereLike('title_lower', $keyword . '%');
        }

        if ($criteria->type->isPresent()) {
            $query = $query->where('type', $criteria->type->get());
        }

        if ($criteria->attribute->isPresent()) {
            $query = $query->where('attribute', $criteria->attribute->get());
        }

        return $query;
    }

    private function hydrate(Song $model): SongSummary
    {
        return new SongSummary(
            $this->converter->toUuid($model->song_id),
            $model->title,
            SongType::from($model->type),
            is_null($model->attribute) ? null : SongAttribute::from($model->attribute),
            $model->order_no,
        );
    }
}
