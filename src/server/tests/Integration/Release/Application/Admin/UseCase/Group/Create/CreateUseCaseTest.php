<?php

declare(strict_types=1);

namespace Tests\Integration\Release\Application\Admin\UseCase\Group\Create;

use PHPUnit\Framework\Attributes\Test;
use Release\Application\Admin\UseCase\Group\Create\CreateInputData;
use Release\Application\Admin\UseCase\Group\Create\CreateUseCase;
use Release\Domain\Models\ReleaseGroupType;
use Support\UseCase\Error\InvalidInputError;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class CreateUseCaseTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;

    #[Test]
    public function canCreate(): void
    {
        $result = $this->getInstance()->handle(new CreateInputData(
            title: '観測された春',
            typeValue: ReleaseGroupType::Album->value,
            description: '1st アルバム',
            isDisplay: true,
        ));

        $this->assertTrue($result->isOk());
        $this->assertSame('観測された春', $result->unwrap()->releaseGroup->title->value);

        $this->assertDatabaseHas('release_groups', [
            'title' => '観測された春',
            'type' => ReleaseGroupType::Album->value,
            'description' => '1st アルバム',
            'is_display' => true,
            // 表示順は自動採番 (既存なしなので 0 + 10)。
            'order_no' => 10,
        ]);
    }

    #[Test]
    public function assignsNextOrderNoFromExistingMax(): void
    {
        $this->storeReleaseGroups(
            $this->createReleaseGroup($this->generateUuid(), '既存の作品', ReleaseGroupType::Single, true, orderNo: 15),
        );

        $result = $this->getInstance()->handle(new CreateInputData(
            title: '観測された春',
            typeValue: ReleaseGroupType::Album->value,
            description: '',
            isDisplay: true,
        ));

        $this->assertTrue($result->isOk());
        $this->assertSame(25, $result->unwrap()->releaseGroup->orderNo->value);
    }

    #[Test]
    public function createFailsWhenTypeIsInvalid(): void
    {
        $result = $this->getInstance()->handle(new CreateInputData(
            title: '観測された春',
            typeValue: 0,
            description: '',
            isDisplay: true,
        ));

        $this->assertTrue($result->isErr());
        $error = $result->unwrapErr();
        $this->assertInstanceOf(InvalidInputError::class, $error);
        $this->assertSame(['typeValue' => ['不正なリリースグループ種別です: 0']], $error->errors);
    }

    private function getInstance(): CreateUseCase
    {
        $this->privilegedContext();

        return $this->app->make(CreateUseCase::class);
    }
}
