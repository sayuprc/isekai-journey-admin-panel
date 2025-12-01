<?php

declare(strict_types=1);

namespace Tests\Unit\SongType\Application\Interactors;

use PHPUnit\Framework\Attributes\Test;
use SongType\Application\Interactors\ListInteractor;
use SongType\Application\UseCase\List\ListUseCaseInterface;
use SongType\Domain\Models\SongType;
use Tests\TestCase;

class ListInteractorTest extends TestCase
{
    private ListInteractor $interactor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->interactor = new ListInteractor();
    }

    #[Test]
    public function isImplementsSpecificInterface(): void
    {
        $this->assertInstanceOf(ListUseCaseInterface::class, $this->interactor);
    }

    #[Test]
    public function nonEmptySongTypes(): void
    {
        $response = $this->interactor->handle();

        $this->assertCount(6, $songTypes = $response->songTypes);

        $this->assertSame(SongType::Original, $songTypes[0]);
        $this->assertSame(SongType::Cover, $songTypes[1]);
        $this->assertSame(SongType::Collaboration, $songTypes[2]);
        $this->assertSame(SongType::Lineage, $songTypes[3]);
        $this->assertSame(SongType::Derivative, $songTypes[4]);
        $this->assertSame(SongType::Amplified, $songTypes[5]);
    }
}
