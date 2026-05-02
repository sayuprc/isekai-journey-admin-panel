<?php

declare(strict_types=1);

namespace Tests\Integration\Song\Application\UseCase\Tag;

use App\Models\Song\SongTag as ModelsSongTag;
use PHPUnit\Framework\Attributes\Test;
use Song\Application\UseCase\Tag\Create\CreateInputData;
use Song\Application\UseCase\Tag\Create\CreateUseCase;
use Song\Infrastructures\Tag\SongTagRepository;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;

class CreateUseCaseTest extends DatabaseTestCase
{
    use EntityFactory;

    #[Test]
    public function create(): void
    {
        $result = $this->getInstance()->handle(new CreateInputData('派生曲'));

        $this->assertTrue($result->isOk());

        $tags = ModelsSongTag::query()->orderBy('order_no')->get();
        $this->assertCount(1, $tags);
        $this->assertSame('派生曲', $tags->first()->name);
        $this->assertSame(10, $tags->first()->order_no);
    }

    #[Test]
    public function createAppendsOrderNoFromCurrentMax(): void
    {
        $this->app->make(SongTagRepository::class)->save(
            $this->createSongTag($this->generateUuid(), '既存タグ', 40),
        );

        $result = $this->getInstance()->handle(new CreateInputData('派生曲'));

        $this->assertTrue($result->isOk());

        $tags = ModelsSongTag::query()->orderBy('order_no')->get();
        $this->assertCount(2, $tags);
        $this->assertSame('派生曲', $tags->last()->name);
        $this->assertSame(50, $tags->last()->order_no);
    }

    private function getInstance(): CreateUseCase
    {
        $this->privilegedContext();

        return $this->app->make(CreateUseCase::class);
    }
}
