<?php

declare(strict_types=1);

namespace Tests\Integration\Release\Application\Admin\UseCase\Search;

use PHPUnit\Framework\Attributes\Test;
use Release\Application\Admin\UseCase\Search\SearchInputData;
use Release\Application\Admin\UseCase\Search\SearchUseCase;
use Release\Domain\Models\ReleaseDistributionType;
use Release\Domain\Models\ReleaseType;
use Support\Domain\SearchCriteria\PerPage;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class SearchUseCaseTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;

    #[Test]
    public function searchWithFilters(): void
    {
        $target = $this->createRelease(
            $this->generateUuid(),
            '観測された夏',
            ReleaseType::Album,
            ReleaseDistributionType::Digital,
            true,
        );
        $other = $this->createRelease(
            $this->generateUuid(),
            '別のリリース',
            ReleaseType::Single,
            ReleaseDistributionType::Physical,
            false,
        );

        $this->storeReleases($target, $other);

        $result = $this->getInstance()->handle(new SearchInputData(
            title: '観測',
            type: ReleaseType::Album->value,
            distributionType: ReleaseDistributionType::Digital->value,
            isDisplay: true,
        ));

        $this->assertTrue($result->isOk());
        $this->assertEquals([$target], $result->unwrap()->releases);
        $this->assertSame(1, $result->unwrap()->maxPage);
    }

    #[Test]
    public function searchReturnsMaxPage(): void
    {
        $releases = [];

        for ($i = 1; $i <= 26; $i++) {
            $releases[] = $this->createRelease(
                $this->generateUuid(),
                sprintf('Release %02d', $i),
                ReleaseType::Single,
                ReleaseDistributionType::Digital,
                true,
            );
        }

        $this->storeReleases(...$releases);

        $result = $this->getInstance()->handle(new SearchInputData(
            page: 1,
            perPage: PerPage::TwentyFive,
        ));

        $this->assertTrue($result->isOk());
        $this->assertCount(25, $result->unwrap()->releases);
        $this->assertSame(2, $result->unwrap()->maxPage);
    }

    private function getInstance(): SearchUseCase
    {
        $this->privilegedContext();

        return $this->app->make(SearchUseCase::class);
    }
}
