<?php

declare(strict_types=1);

namespace Tests\Unit\SongAttribute\Application\Interactors;

use PHPUnit\Framework\Attributes\Test;
use Song\Domain\Models\SongAttribute;
use SongAttribute\Application\Interactors\ListInteractor;
use Tests\TestCase;

class ListInteractorTest extends TestCase
{
    #[Test]
    public function nonEmptySongAttributes(): void
    {
        $result = $this->getInstance()->handle();

        $this->assertTrue($result->isOk());

        $this->assertCount(5, $songAttributes = $result->unwrap()->songAttributes);

        $this->assertSame(SongAttribute::Collaboration, $songAttributes[0]);
        $this->assertSame(SongAttribute::Lineage, $songAttributes[1]);
        $this->assertSame(SongAttribute::Derivative, $songAttributes[2]);
        $this->assertSame(SongAttribute::Amplified, $songAttributes[3]);
        $this->assertSame(SongAttribute::Isotope, $songAttributes[4]);
    }

    private function getInstance(): ListInteractor
    {
        return new ListInteractor($this->privilegedContext());
    }
}
