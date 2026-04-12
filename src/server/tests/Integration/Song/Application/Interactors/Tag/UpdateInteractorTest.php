<?php

declare(strict_types=1);

namespace Tests\Integration\Song\Application\Interactors\Tag;

use App\Models\Song\SongTag as ModelsSongTag;
use PHPUnit\Framework\Attributes\Test;
use Song\Application\Interactors\Tag\UpdateInteractor;
use Song\Application\UseCase\Tag\Update\UpdateInputData;
use Song\Domain\Models\Tag\SongTag;
use Song\Domain\Models\Tag\SongTagId;
use Song\Domain\Models\Tag\SongTagName;
use Song\Infrastructures\SongTagRepository;
use Support\Domain\ValueObjects\OrderNo;
use Tests\Support\DatabaseTestCase;

class UpdateInteractorTest extends DatabaseTestCase
{
    #[Test]
    public function canUpdate(): void
    {
        $songTagId = $this->generateUuid();

        $this->app->make(SongTagRepository::class)->save(new SongTag(
            SongTagId::reconstruct($songTagId),
            SongTagName::reconstruct('ポップ'),
            OrderNo::reconstruct(10),
        ));

        $result = $this->getInstance()->handle(new UpdateInputData($songTagId, 'ロック', 20));

        $this->assertTrue($result->isOk());

        $tags = ModelsSongTag::query()->get()->all();
        $this->assertCount(1, $tags);
        $tag = array_first($tags);
        $this->assertSame('ロック', $tag->name);
        $this->assertSame(20, $tag->order_no);
    }

    private function getInstance(): UpdateInteractor
    {
        $this->privilegedContext();

        return $this->app->make(UpdateInteractor::class);
    }
}
