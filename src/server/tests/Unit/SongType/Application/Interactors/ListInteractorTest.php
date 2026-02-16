<?php

declare(strict_types=1);

namespace Tests\Unit\SongType\Application\Interactors;

use PHPUnit\Framework\Attributes\Test;
use SongType\Application\Interactors\ListInteractor;
use SongType\Domain\Models\SongType;
use Tests\TestCase;

class ListInteractorTest extends TestCase
{
    #[Test]
    public function nonEmptySongTypes(): void
    {
        $result = $this->getInstance()->handle();

        $this->assertTrue($result->isOk());

        $this->assertCount(6, $songTypes = $result->unwrap()->songTypes);

        $this->assertSame(SongType::Original, $songTypes[0]);
        $this->assertSame(SongType::Cover, $songTypes[1]);
        $this->assertSame(SongType::Collaboration, $songTypes[2]);
        $this->assertSame(SongType::Lineage, $songTypes[3]);
        $this->assertSame(SongType::Derivative, $songTypes[4]);
        $this->assertSame(SongType::Amplified, $songTypes[5]);
    }

    private function getInstance(): ListInteractor
    {
        return new ListInteractor();
    }
}
