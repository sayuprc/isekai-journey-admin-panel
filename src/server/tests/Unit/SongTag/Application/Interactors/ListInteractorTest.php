<?php

declare(strict_types=1);

namespace Tests\Unit\SongTag\Application\Interactors;

use Mockery;
use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\Test;
use Song\Domain\Models\TagRepositoryInterface;
use SongTag\Application\Interactors\ListInteractor;
use Tests\Support\Domain\EntityFactory;
use Tests\TestCase;

class ListInteractorTest extends TestCase
{
    use EntityFactory;

    private MockInterface&TagRepositoryInterface $repository;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(TagRepositoryInterface::class);
    }

    #[Test]
    public function emptyTags(): void
    {
        $this->repository->shouldReceive('all')
            ->andReturn([])
            ->once();

        $result = $this->getInstance()->handle();
        $this->assertTrue($result->isOk());

        $response = $result->unwrap();
        $this->assertCount(0, $response->tags);
    }

    #[Test]
    public function nonEmptyTags(): void
    {
        $this->repository->shouldReceive('all')
            ->andReturn([
                $this->createSongTag('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', 'タグA', 1),
                $this->createSongTag('BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB', 'タグB', 2),
            ])
            ->once();

        $result = $this->getInstance()->handle();
        $this->assertTrue($result->isOk());

        $response = $result->unwrap();
        $this->assertCount(2, $response->tags);
        $this->assertSame('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', $response->tags[0]->tagId->value);
        $this->assertSame('タグA', $response->tags[0]->name->value);
        $this->assertSame('BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB', $response->tags[1]->tagId->value);
        $this->assertSame('タグB', $response->tags[1]->name->value);
    }

    private function getInstance(): ListInteractor
    {
        return new ListInteractor(
            $this->privilegedContext(),
            $this->repository,
        );
    }
}
