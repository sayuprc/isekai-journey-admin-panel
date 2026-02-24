<?php

declare(strict_types=1);

namespace Tests\Integration\SongType\Application\Interactors;

use AdminUser\Domain\Models\Permission;
use PHPUnit\Framework\Attributes\Test;
use SongType\Application\Interactors\ListInteractor;
use SongType\Domain\Models\SongType;
use Support\UseCase\Error\AuthenticationError;
use Support\UseCase\Error\AuthorizationError;
use Tests\TestCase;

class ListInteractorTest extends TestCase
{
    #[Test]
    public function canList(): void
    {
        $this->permissionContext(Permission::ReadSongType);

        $result = $this->getInstance()->handle();

        $this->assertTrue($result->isOk());

        $output = $result->unwrap();

        $this->assertCount(6, $output->songTypes);
        $this->assertSame(SongType::Original, $output->songTypes[0]);
        $this->assertSame(SongType::Cover, $output->songTypes[1]);
        $this->assertSame(SongType::Collaboration, $output->songTypes[2]);
        $this->assertSame(SongType::Lineage, $output->songTypes[3]);
        $this->assertSame(SongType::Derivative, $output->songTypes[4]);
        $this->assertSame(SongType::Amplified, $output->songTypes[5]);
    }

    #[Test]
    public function cannotListWithoutPermission(): void
    {
        $this->permissionContext();

        $result = $this->getInstance()->handle();

        $this->assertTrue($result->isErr());
        $this->assertInstanceOf(AuthorizationError::class, $result->unwrapErr());
    }

    #[Test]
    public function cannotListUnauthenticated(): void
    {
        $result = $this->getInstance()->handle();

        $this->assertTrue($result->isErr());
        $this->assertInstanceOf(AuthenticationError::class, $result->unwrapErr());
    }

    private function getInstance(): ListInteractor
    {
        return $this->app->make(ListInteractor::class);
    }
}
