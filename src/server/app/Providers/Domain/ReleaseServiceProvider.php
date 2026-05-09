<?php

declare(strict_types=1);

namespace App\Providers\Domain;

use Illuminate\Support\ServiceProvider;
use Override;
use Release\Application\Admin\Query\ReleaseDetailQueryServiceInterface;
use Release\Domain\Models\ReleaseRepositoryInterface;
use Release\Infrastructures\Admin\ReleaseDetailQueryService;
use Release\Infrastructures\ReleaseRepository;

class ReleaseServiceProvider extends ServiceProvider
{
    #[Override]
    public function register(): void
    {
        $this->app->bind(ReleaseRepositoryInterface::class, ReleaseRepository::class);
        $this->app->bind(ReleaseDetailQueryServiceInterface::class, ReleaseDetailQueryService::class);
    }
}
