<?php

declare(strict_types=1);

namespace Tests\Unit\Song\Application\Interactors;

use PHPUnit\Framework\Attributes\Test;
use Song\Application\Interactors\ListTypeInteractor;
use Song\Domain\Models\SongType;
use Tests\TestCase;

class ListTypeInteractorTest extends TestCase
{
    #[Test]
    public function nonEmptySongTypes(): void
    {
        $result = $this->getInstance()->handle();

        $this->assertTrue($result->isOk());

        $this->assertCount(2, $types = $result->unwrap()->types);
        $this->assertSame(SongType::cases(), $types);
    }

    private function getInstance(): ListTypeInteractor
    {
        return new ListTypeInteractor($this->privilegedContext());
    }
}
