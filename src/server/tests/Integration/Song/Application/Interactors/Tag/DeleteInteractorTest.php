<?php

declare(strict_types=1);

namespace Tests\Integration\Song\Application\Interactors\Tag;

use App\Models\Song\SongTag as ModelsSongTag;
use PHPUnit\Framework\Attributes\Test;
use Song\Application\Interactors\Tag\DeleteInteractor;
use Song\Application\UseCase\Tag\Delete\DeleteInputData;
use Song\Domain\Models\Tag\SongTag;
use Song\Domain\Models\Tag\SongTagId;
use Song\Domain\Models\Tag\SongTagName;
use Song\Infrastructures\SongTagRepository;
use Support\Domain\ValueObjects\OrderNo;
use Tests\Support\DatabaseTestCase;

class DeleteInteractorTest extends DatabaseTestCase
{
    #[Test]
    public function canDelete(): void
    {
        $songTagId = $this->generateUuid();

        $this->app->make(SongTagRepository::class)->save(new SongTag(
            SongTagId::reconstruct($songTagId),
            SongTagName::reconstruct('ロック'),
            OrderNo::reconstruct(10),
        ));

        $result = $this->getInstance()->handle(new DeleteInputData($songTagId));

        $this->assertTrue($result->isOk());
        $this->assertCount(0, ModelsSongTag::query()->get());
    }

    private function getInstance(): DeleteInteractor
    {
        $this->privilegedContext();

        return $this->app->make(DeleteInteractor::class);
    }
}
