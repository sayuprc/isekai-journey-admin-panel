<?php

declare(strict_types=1);

namespace Tests\Unit\Creator\Application\UseCase\List;

use Creator\Application\UseCase\List\ListUseCase;
use Creator\Domain\Models\CreatorRepositoryInterface;
use Mockery;
use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\Domain\EntityFactory;
use Tests\TestCase;

class ListUseCaseTest extends TestCase
{
    use EntityFactory;

    private CreatorRepositoryInterface&MockInterface $repository;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(CreatorRepositoryInterface::class);
    }

    #[Test]
    public function emptyCreators(): void
    {
        $this->repository->shouldReceive('all')
            ->andReturn([])
            ->once();

        $result = $this->getInstance()->handle();
        $this->assertTrue($result->isOk());

        $response = $result->unwrap();

        $this->assertCount(0, $response->creators);
    }

    #[Test]
    public function nonEmptyCreators(): void
    {
        $this->repository->shouldReceive('all')
            ->andReturn([
                $this->createCreator('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', 'クリエイターA', 1),
                $this->createCreator('BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB', 'クリエイターB', 1),
            ])
            ->once();

        $result = $this->getInstance()->handle();
        $this->assertTrue($result->isOk());

        $response = $result->unwrap();

        $this->assertCount(2, $response->creators);

        $this->assertSame('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', $response->creators[0]->creatorId->value);
        $this->assertSame('クリエイターA', $response->creators[0]->name->value);
        $this->assertSame('BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB', $response->creators[1]->creatorId->value);
        $this->assertSame('クリエイターB', $response->creators[1]->name->value);
    }

    private function getInstance(): ListUseCase
    {
        return new ListUseCase(
            $this->privilegedContext(),
            $this->repository,
        );
    }
}
