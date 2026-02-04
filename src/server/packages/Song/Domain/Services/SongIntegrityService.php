<?php

declare(strict_types=1);

namespace Song\Domain\Services;

use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Song\Domain\Models\Creators\Arrangers;
use Song\Domain\Models\Creators\Composers;
use Song\Domain\Models\Creators\Lyricists;
use Song\Domain\Models\Song;
use Song\Domain\Models\SongFactoryInterface;
use Song\Domain\Models\SongId;
use Song\Domain\Models\Title;
use SongType\Domain\Models\SongType;
use Support\Contracts\UuidGeneratorInterface;
use Support\Domain\ValueObjects\OrderNo;

/**
 * TODO エラーハンドリングを強化する
 *
 * @phpstan-type creator array{creatorId: string, orderNo: int}
 */
class SongIntegrityService
{
    public function __construct(
        private readonly UuidGeneratorInterface $generator,
        private readonly SongFactoryInterface $factory,
    ) {
    }

    /**
     * @param array<int, creator> $arrangers
     * @param array<int, creator> $composers
     * @param array<int, creator> $lyricists
     *
     * @return Result<Song, string>
     */
    public function prepareForCreate(
        string $title,
        int $songType,
        int $orderNo,
        array $arrangers,
        array $composers,
        array $lyricists,
    ): Result {
        return $this->build(
            $this->generator->generate(),
            $title,
            $songType,
            $orderNo,
            $arrangers,
            $composers,
            $lyricists,
        );
    }

    /**
     * @param array<int, creator> $arrangers
     * @param array<int, creator> $composers
     * @param array<int, creator> $lyricists
     *
     * @return Result<Song, string>
     */
    private function build(
        string $songId,
        string $title,
        int $songType,
        int $orderNo,
        array $arrangers,
        array $composers,
        array $lyricists,
    ): Result {
        return Result::collect7(
            SongId::create($songId),
            Title::create($title),
            $this->toEnum($songType),
            OrderNo::create($orderNo),
            Arrangers::fromArray($arrangers),
            Composers::fromArray($composers),
            Lyricists::fromArray($lyricists),
        )
            ->mapErr(fn (): string => '')
            ->map(fn (array $values): Song => $this->factory->create(...$values));
    }

    /**
     * @return Result<SongType, string>
     */
    private function toEnum(int $songType): Result
    {
        $result = SongType::tryFrom($songType);

        if (is_null($result)) {
            return new Err('');
        }

        return new Ok($result);
    }
}
