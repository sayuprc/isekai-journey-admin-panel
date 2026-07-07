<?php

declare(strict_types=1);

namespace Tests\Integration\Release\Application\Admin\UseCase\Group\Update;

use PHPUnit\Framework\Attributes\Test;
use Release\Application\Admin\UseCase\Group\Update\UpdateInputData;
use Release\Application\Admin\UseCase\Group\Update\UpdateUseCase;
use Release\Domain\Models\ReleaseGroupType;
use Support\UseCase\Error\NotFoundError;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class UpdateUseCaseTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;

    #[Test]
    public function canUpdate(): void
    {
        $releaseGroupId = $this->generateUuid();

        $this->storeReleaseGroups(
            $this->createReleaseGroup($releaseGroupId, '旧タイトル', ReleaseGroupType::Album, true),
        );

        $result = $this->getInstance()->handle(new UpdateInputData(
            releaseGroupId: $releaseGroupId,
            title: '新タイトル',
            typeValue: ReleaseGroupType::Single->value,
            description: '更新後の説明',
            isDisplay: false,
            orderNo: 3,
        ));

        $this->assertTrue($result->isOk());
        $this->assertSame('新タイトル', $result->unwrap()->releaseGroup->title->value);

        $this->assertDatabaseHas('release_groups', [
            'title' => '新タイトル',
            'type' => ReleaseGroupType::Single->value,
            'description' => '更新後の説明',
            'is_display' => false,
            'order_no' => 3,
        ]);
    }

    #[Test]
    public function notFound(): void
    {
        $result = $this->getInstance()->handle(new UpdateInputData(
            releaseGroupId: $this->generateUuid(),
            title: '新タイトル',
            typeValue: ReleaseGroupType::Album->value,
            description: '説明',
            isDisplay: true,
            orderNo: 1,
        ));

        $this->assertTrue($result->isErr());
        $this->assertInstanceOf(NotFoundError::class, $result->unwrapErr());
    }

    private function getInstance(): UpdateUseCase
    {
        $this->privilegedContext();

        return $this->app->make(UpdateUseCase::class);
    }
}
