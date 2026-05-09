<?php

declare(strict_types=1);

namespace Tests\Integration\Release\Application\Admin\UseCase\Create;

use App\Models\Release\Release as ModelsRelease;
use PHPUnit\Framework\Attributes\Test;
use Release\Application\Admin\UseCase\Create\CreateInputData;
use Release\Application\Admin\UseCase\Create\CreateUseCase;
use Release\Domain\Models\ReleaseDistributionType;
use Release\Domain\Models\ReleaseType;
use Support\UseCase\Error\InvalidInputError;
use Tests\Support\DatabaseTestCase;

class CreateUseCaseTest extends DatabaseTestCase
{
    #[Test]
    public function canCreate(): void
    {
        $result = $this->getInstance()->handle(new CreateInputData(
            title: '観測された春',
            typeValue: ReleaseType::Album->value,
            distributionTypeValue: ReleaseDistributionType::Digital->value,
            releasedOn: '2026-05-09',
            description: '説明',
            isDisplay: true,
        ));

        $this->assertTrue($result->isOk());
        $this->assertSame('観測された春', $result->unwrap()->release->title->value);
        $this->assertCount(0, $result->unwrap()->release->trackEntries->toGeneric());

        $this->assertDatabaseHas(ModelsRelease::class, [
            'title' => '観測された春',
            'type' => ReleaseType::Album->value,
            'distribution_type' => ReleaseDistributionType::Digital->value,
            'description' => '説明',
            'is_display' => true,
        ]);
    }

    #[Test]
    public function createFailsWhenReleasedOnIsInvalid(): void
    {
        $result = $this->getInstance()->handle(new CreateInputData(
            title: '観測された春',
            typeValue: ReleaseType::Album->value,
            distributionTypeValue: ReleaseDistributionType::Digital->value,
            releasedOn: 'invalid-date',
            description: '説明',
            isDisplay: true,
        ));

        $this->assertTrue($result->isErr());
        $error = $result->unwrapErr();
        $this->assertInstanceOf(InvalidInputError::class, $error);
        $this->assertSame(['releasedOn' => ['発売日が不正です']], $error->errors);
    }

    private function getInstance(): CreateUseCase
    {
        $this->privilegedContext();

        return $this->app->make(CreateUseCase::class);
    }
}
