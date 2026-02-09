<?php

declare(strict_types=1);

namespace Tests\Unit\Creator\Application\Interactors;

use Creator\Application\Interactors\DeleteInteractor;
use Creator\Application\UseCase\Delete\DeleteInputData;
use Creator\Domain\Models\CreatorId;
use Creator\Domain\Models\CreatorRepositoryInterface;
use Creator\Domain\Services\CreatorUsageCheckerInterface;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DeleteInteractorTest extends TestCase
{
    private CreatorRepositoryInterface&MockInterface $repository;

    private CreatorUsageCheckerInterface&MockInterface $usageChecker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(CreatorRepositoryInterface::class);
        $this->usageChecker = Mockery::mock(CreatorUsageCheckerInterface::class);
    }

    #[Test]
    public function canDelete(): void
    {
        $creatorId = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';

        $this->usageChecker->shouldReceive('isUsed')
            ->withArgs(fn (CreatorId $arg): bool => $arg->value === $creatorId)
            ->andReturn(false)
            ->once();

        $this->repository->shouldReceive('delete')
            ->withArgs(fn (CreatorId $arg): bool => $arg->value === $creatorId)
            ->once();

        $result = $this->getInstance()->handle(new DeleteInputData($creatorId));

        $this->assertTrue($result->isOk());
    }

    #[Test]
    public function cannotDeleteWhenUsed(): void
    {
        $creatorId = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';

        $this->usageChecker->shouldReceive('isUsed')
            ->withArgs(fn (CreatorId $arg): bool => $arg->value === $creatorId)
            ->andReturn(true)
            ->once();

        $this->repository->shouldNotReceive('delete');

        $result = $this->getInstance()->handle(new DeleteInputData($creatorId));

        $this->assertTrue($result->isErr());
        $this->assertSame('このクリエイターは楽曲に使用されているため削除できません', $result->unwrapErr());
    }

    private function getInstance(): DeleteInteractor
    {
        return new DeleteInteractor(
            $this->repository,
            $this->usageChecker,
        );
    }
}
