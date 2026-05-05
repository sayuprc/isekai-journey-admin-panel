<?php

declare(strict_types=1);

namespace Song\Domain\Models\Media;

use Media\Domain\Models\MediaId;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Support\Collection\ImmutableCollection;
use Support\Domain\Error\DomainValidationError;
use Support\Domain\Error\EntityRuleViolationError;
use Support\Domain\ValueObjects\OrderNo;

/**
 * @extends ImmutableCollection<int, SongMediaLink>
 */
readonly class SongMediaLinks extends ImmutableCollection
{
    /**
     * @param list<array{mediaId: string, songMediaType: int, orderNo: int}> $items
     *
     * @return Result<self, DomainValidationError>
     */
    public static function fromArray(array $items): Result
    {
        $links = [];
        $seen = [];

        foreach ($items as $item) {
            $result = Result::collect3(
                MediaId::create($item['mediaId']),
                self::toSongMediaType($item['songMediaType']),
                OrderNo::create($item['orderNo']),
            )->map(fn (array $values): SongMediaLink => new SongMediaLink(...$values));

            if ($result->isErr()) {
                $messages = [];
                foreach ($result->unwrapErr() as $error) {
                    if ($error instanceof EntityRuleViolationError) {
                        $messages[$error->field] ??= [];
                        $messages[$error->field][] = $error->message;
                    }
                }

                return new Err(new DomainValidationError($messages));
            }

            $link = $result->unwrap();

            if (isset($seen[$link->mediaId->value])) {
                return new Err(new DomainValidationError([
                    'media' => ['同じメディアを複数指定することはできません。'],
                ]));
            }

            $seen[$link->mediaId->value] = true;
            $links[] = $link;
        }

        return new Ok(new self($links));
    }

    /**
     * @param list<array{mediaId: string, songMediaType: int, orderNo: int}> $items
     */
    public static function reconstruct(array $items): self
    {
        return new self(array_map(
            fn (array $item): SongMediaLink => SongMediaLink::reconstruct(
                $item['mediaId'],
                $item['songMediaType'],
                $item['orderNo'],
            ),
            $items,
        ));
    }

    /**
     * @return list<array{media_id: string, song_media_type: value-of<SongMediaType>, order_no: int}>
     */
    public function toArray(): array
    {
        return $this->toGeneric()
            ->map(fn (SongMediaLink $item): array => $item->toArray())
            ->toList();
    }

    /**
     * @return Result<SongMediaType, EntityRuleViolationError>
     */
    private static function toSongMediaType(int $type): Result
    {
        $found = SongMediaType::tryFrom($type);

        if (is_null($found)) {
            return new Err(new EntityRuleViolationError('songMediaType', "不正な songMediaType です: {$type}"));
        }

        return new Ok($found);
    }
}
