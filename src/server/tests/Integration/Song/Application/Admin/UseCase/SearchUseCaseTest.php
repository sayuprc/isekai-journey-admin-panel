<?php

declare(strict_types=1);

namespace Tests\Integration\Song\Application\Admin\UseCase;

use PHPUnit\Framework\Attributes\Test;
use Song\Application\Admin\UseCase\Search\SearchInputData;
use Song\Application\Admin\UseCase\Search\SearchUseCase;
use Song\Domain\Models\SongType;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class SearchUseCaseTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;

    #[Test]
    public function searchWithoutFilters(): void
    {
        $uuid = $this->generateUuid();

        $this->storeSongs(
            $this->createSong($uuid, '描き続けた君へ', 'オリジナル楽曲', SongType::Original, true, 1, [], [], [], []),
        );

        $result = $this->getInstance()->handle(new SearchInputData());

        $this->assertTrue($result->isOk());

        $output = $result->unwrap();

        $this->assertCount(1, $output->songs);
        $this->assertSame($uuid, $output->songs[0]->songId);
        $this->assertSame('描き続けた君へ', $output->songs[0]->title);
        $this->assertSame(1, $output->maxPage);
    }

    #[Test]
    public function searchWithTitle(): void
    {
        $uuid1 = $this->generateUuid();
        $uuid2 = $this->generateUuid();

        $this->storeSongs(
            $this->createSong($uuid1, '描き続けた君へ', 'オリジナル楽曲', SongType::Original, true, 1, [], [], [], []),
            $this->createSong($uuid2, '全部夢だった！', 'カバー楽曲', SongType::Cover, true, 2, [], [], [], []),
        );

        $result = $this->getInstance()->handle(new SearchInputData(title: '描き続けた君へ'));

        $this->assertTrue($result->isOk());

        $output = $result->unwrap();

        $this->assertCount(1, $output->songs);
        $this->assertSame($uuid1, $output->songs[0]->songId);
        $this->assertSame('描き続けた君へ', $output->songs[0]->title);
    }

    #[Test]
    public function searchWithType(): void
    {
        $uuid1 = $this->generateUuid();
        $uuid2 = $this->generateUuid();

        $this->storeSongs(
            $this->createSong($uuid1, '描き続けた君へ', 'オリジナル楽曲', SongType::Original, true, 1, [], [], [], []),
            $this->createSong($uuid2, '全部夢だった！', 'カバー楽曲', SongType::Cover, true, 2, [], [], [], []),
        );

        $result = $this->getInstance()->handle(new SearchInputData(type: SongType::Original->value));

        $this->assertTrue($result->isOk());

        $output = $result->unwrap();

        $this->assertCount(1, $output->songs);
        $this->assertSame($uuid1, $output->songs[0]->songId);
        $this->assertSame(SongType::Original, $output->songs[0]->type);
    }

    #[Test]
    public function searchWithTitleNotFound(): void
    {
        $uuid = $this->generateUuid();

        $this->storeSongs(
            $this->createSong($uuid, '描き続けた君へ', 'オリジナル楽曲', SongType::Original, true, 1, [], [], [], []),
        );

        $result = $this->getInstance()->handle(new SearchInputData(title: '存在しないタイトル'));

        $this->assertTrue($result->isOk());

        $output = $result->unwrap();

        $this->assertCount(0, $output->songs);
        $this->assertSame(0, $output->maxPage);
    }

    private function getInstance(): SearchUseCase
    {
        $this->privilegedContext();

        return $this->app->make(SearchUseCase::class);
    }
}
