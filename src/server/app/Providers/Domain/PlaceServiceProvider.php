<?php

declare(strict_types=1);

namespace App\Providers\Domain;

use Illuminate\Support\ServiceProvider;
use Override;
use Place\Domain\Models\PlaceRepositoryInterface;
use Place\Domain\Services\PlaceUsageCheckerInterface;
use Place\Infrastructures\PlaceRepository;
use Place\Infrastructures\PlaceUsageChecker;

class PlaceServiceProvider extends ServiceProvider
{
    #[Override]
    public function register(): void
    {
        $this->app->bind(PlaceRepositoryInterface::class, PlaceRepository::class);
        $this->app->bind(PlaceUsageCheckerInterface::class, PlaceUsageChecker::class);
    }
}
