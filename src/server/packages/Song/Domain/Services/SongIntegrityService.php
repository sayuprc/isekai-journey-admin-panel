<?php

declare(strict_types=1);

namespace Song\Domain\Services;

use Creator\Domain\Models\CreatorId;
use Creator\Domain\Models\CreatorRepositoryInterface;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Song\Domain\Models\Creators\Arrangers;
use Song\Domain\Models\Creators\Composers;
use Song\Domain\Models\Creators\Lyricists;
use Song\Domain\Models\Description;
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
        private readonly CreatorRepositoryInterface $creatorRepository,
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
        string $description,
        int $songType,
        int $orderNo,
        array $arrangers,
        array $composers,
        array $lyricists,
    ): Result {
        $result = Result::collect3(
            Arrangers::fromArray($arrangers),
            Composers::fromArray($composers),
            Lyricists::fromArray($lyricists),
        );

        if ($result->isErr()) {
            return new Err('');
        }

        $creators = $result->unwrap();

        if (! $this->existsCreators(...$creators)) {
            // CreatorId が不正
            return new Err('');
        }

        return $this->build(
            $this->generator->generate(),
            $title,
            $description,
            $songType,
            $orderNo,
            ...$creators,
        );
    }

    /**
     * @param array<int, creator> $arrangers
     * @param array<int, creator> $composers
     * @param array<int, creator> $lyricists
     *
     * @return Result<Song, string>
     */
    public function prepareForUpdate(
        string $songId,
        string $title,
        string $description,
        int $songType,
        int $orderNo,
        array $arrangers,
        array $composers,
        array $lyricists,
    ): Result {
        $result = Result::collect3(
            Arrangers::fromArray($arrangers),
            Composers::fromArray($composers),
            Lyricists::fromArray($lyricists),
        );

        if ($result->isErr()) {
            return new Err('');
        }

        $creators = $result->unwrap();

        if (! $this->existsCreators(...$creators)) {
            // CreatorId が不正
            return new Err('');
        }

        return $this->build(
            $songId,
            $title,
            $description,
            $songType,
            $orderNo,
            ...$creators,
        );
    }

    /**
     * @return Result<Song, string>
     */
    private function build(
        string $songId,
        string $title,
        string $description,
        int $songType,
        int $orderNo,
        Arrangers $arrangers,
        Composers $composers,
        Lyricists $lyricists,
    ): Result {
        return Result::collect5(
            SongId::create($songId),
            Title::create($title),
            Description::create($description),
            $this->toEnum($songType),
            OrderNo::create($orderNo),
        )
            ->mapErr(fn (): string => '')
            ->map(fn (array $values): Song => $this->factory->create(...[...$values, $arrangers, $composers, $lyricists]));
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

    private function existsCreators(Arrangers $arrangers, Composers $composers, Lyricists $lyricists): bool
    {
        $creatorIds = [];

        foreach ([$arrangers, $composers, $lyricists] as $items) {
            foreach ($items as $item) {
                if (! isset($creatorIds[$item->creatorId->value])) {
                    $creatorIds[$item->creatorId->value] = $item->creatorId;
                }
            }
        }

        $founds = $this->creatorRepository->findByIds(...array_values($creatorIds));

        return count($creatorIds) === count($founds);
    }
}
