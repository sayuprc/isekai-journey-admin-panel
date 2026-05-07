<?php

declare(strict_types=1);

namespace App\Providers\Domain;

use Illuminate\Support\ServiceProvider;
use Media\Application\Admin\Query\MediaDetailQueryServiceInterface;
use Media\Domain\Models\MediaRepositoryInterface;
use Media\Infrastructures\Admin\MediaDetailQueryService;
use Media\Infrastructures\MediaRepository;
use Override;

class MediaServiceProvider extends ServiceProvider
{
    #[Override]
    public function register(): void
    {
        $this->app->bind(MediaRepositoryInterface::class, MediaRepository::class);
        $this->app->bind(MediaDetailQueryServiceInterface::class, MediaDetailQueryService::class);
    }
}
