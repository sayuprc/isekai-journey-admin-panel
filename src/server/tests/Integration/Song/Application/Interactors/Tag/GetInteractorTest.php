<?php

declare(strict_types=1);

namespace Tests\Integration\Song\Application\Interactors\Tag;

use PHPUnit\Framework\Attributes\Test;
use Song\Application\Interactors\Tag\GetInteractor;
use Song\Application\UseCase\Tag\Get\GetInputData;
use Song\Domain\Models\Tag\SongTag;
use Song\Domain\Models\Tag\SongTagId;
use Song\Domain\Models\Tag\SongTagName;
use Song\Infrastructures\SongTagRepository;
use Support\Domain\ValueObjects\OrderNo;
use Support\UseCase\Error\NotFoundError;
use Tests\Support\DatabaseTestCase;

class GetInteractorTest extends DatabaseTestCase
{
    #[Test]
    public function getSongTag(): void
    {
        $songTagId = $this->generateUuid();

        $this->app->make(SongTagRepository::class)->save(new SongTag(
            SongTagId::reconstruct($songTagId),
            SongTagName::reconstruct('ロック'),
            OrderNo::reconstruct(10),
        ));

        $result = $this->getInstance()->handle(new GetInputData($songTagId));

        $this->assertTrue($result->isOk());
        $this->assertSame($songTagId, $result->unwrap()->tag->songTagId->value);
        $this->assertSame('ロック', $result->unwrap()->tag->name->value);
        $this->assertSame(10, $result->unwrap()->tag->orderNo->value);
    }

    #[Test]
    public function getSongTagFailsIfNotFound(): void
    {
        $songTagId = $this->generateUuid();

        $result = $this->getInstance()->handle(new GetInputData($songTagId));

        $this->assertTrue($result->isErr());
        $error = $result->unwrapErr();
        $this->assertInstanceOf(NotFoundError::class, $error);
        $this->assertSame('楽曲タグ', $error->resourceName);
        $this->assertSame($songTagId, $error->identifier);
    }

    private function getInstance(): GetInteractor
    {
        $this->privilegedContext();

        return $this->app->make(GetInteractor::class);
    }
}
