<?php

declare(strict_types=1);

namespace App\Providers\Domain;

use Aws\S3\S3Client;
use Illuminate\Support\ServiceProvider;
use Override;
use Release\Application\Admin\Query\ReleaseDetailQueryServiceInterface;
use Release\Application\Admin\Query\ReleaseGroupDetailQueryServiceInterface;
use Release\Application\Admin\Query\ReleaseGroupSearchQueryServiceInterface;
use Release\Application\Admin\Storage\JacketArtStorageInterface;
use Release\Application\Viewer\Query\ReleaseGroupQueryServiceInterface as ViewerReleaseGroupQueryServiceInterface;
use Release\Domain\Models\ReleaseGroupRepositoryInterface;
use Release\Domain\Models\ReleaseRepositoryInterface;
use Release\Infrastructures\Admin\ReleaseDetailQueryService;
use Release\Infrastructures\Admin\ReleaseGroupDetailQueryService;
use Release\Infrastructures\Admin\ReleaseGroupSearchQueryService;
use Release\Infrastructures\ReleaseGroupRepository;
use Release\Infrastructures\ReleaseRepository;
use Release\Infrastructures\Storage\R2JacketArtStorage;
use Release\Infrastructures\Storage\R2JacketArtStorageConfig;
use Release\Infrastructures\Viewer\ReleaseGroupQueryService as ViewerReleaseGroupQueryService;

class ReleaseServiceProvider extends ServiceProvider
{
    #[Override]
    public function register(): void
    {
        $this->app->bind(
            R2JacketArtStorageConfig::class,
            static fn (): R2JacketArtStorageConfig => new R2JacketArtStorageConfig(
                config()->string('services.r2.access_key_id'),
                config()->string('services.r2.secret_access_key'),
                config()->string('services.r2.endpoint'),
                config()->string('services.r2.bucket'),
                config()->string('services.r2.region', 'auto'),
                config()->string('services.r2.public_url'),
                config()->boolean('services.r2.use_path_style_endpoint', false),
            ),
        );

        $this->app->bind(JacketArtStorageInterface::class, function (): JacketArtStorageInterface {
            $config = $this->app->make(R2JacketArtStorageConfig::class);
            $client = new S3Client([
                'version' => 'latest',
                'region' => $config->region,
                'endpoint' => $config->endpoint,
                // MinIO で path-style が必要な場合だけ有効化する。
                // 'use_path_style_endpoint' => $config->usePathStyleEndpoint,
                'credentials' => [
                    'key' => $config->accessKeyId,
                    'secret' => $config->secretAccessKey,
                ],
            ]);

            return new R2JacketArtStorage($client, $config);
        });
        $this->app->bind(ReleaseRepositoryInterface::class, ReleaseRepository::class);
        $this->app->bind(ReleaseGroupRepositoryInterface::class, ReleaseGroupRepository::class);
        $this->app->bind(ReleaseDetailQueryServiceInterface::class, ReleaseDetailQueryService::class);
        $this->app->bind(ReleaseGroupDetailQueryServiceInterface::class, ReleaseGroupDetailQueryService::class);
        $this->app->bind(ReleaseGroupSearchQueryServiceInterface::class, ReleaseGroupSearchQueryService::class);
        $this->app->bind(ViewerReleaseGroupQueryServiceInterface::class, ViewerReleaseGroupQueryService::class);
    }
}
