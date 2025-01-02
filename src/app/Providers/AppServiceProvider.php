<?php

declare(strict_types=1);

namespace App\Providers;

use Generated\IsekaiJourney\JourneyLog\JourneyLogServiceClient;
use Generated\IsekaiJourney\JourneyLogLinkType\JourneyLogLinkTypeServiceClient;
use Generated\IsekaiJourney\Song\SongServiceClient;
use Grpc\ChannelCredentials;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use JourneyLog\Domain\Repositories\JourneyLogRepositoryInterface;
use JourneyLog\Infrastructures\Repositories\JourneyLogRepository;
use JourneyLogLinkType\Domain\Repositories\JourneyLogLinkTypeRepositoryInterface;
use JourneyLogLinkType\Infrastructures\Repositories\JourneyLogLinkTypeRepository;
use Shared\Application\Uuid\DummyUuidGenerator;
use Shared\Uuid\UuidGeneratorInterface;
use Song\Domain\Repositories\SongRepositoryInterface;
use Song\Infrastructures\Repositories\SongRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $config = fn (): array => [
            config('grpc.api_url'),
            ChannelCredentials::createSsl(file_get_contents(config('grpc.root_ca'))),
        ];

        $this->app->bind(JourneyLogServiceClient::class, function () use ($config): JourneyLogServiceClient {
            [$url, $credentials] = $config();

            return new JourneyLogServiceClient($url, ['credentials' => $credentials]);
        });

        $this->app->bind(JourneyLogLinkTypeServiceClient::class, function () use ($config): JourneyLogLinkTypeServiceClient {
            [$url, $credentials] = $config();

            return new JourneyLogLinkTypeServiceClient($url, ['credentials' => $credentials]);
        });

        $this->app->bind(SongServiceClient::class, function () use ($config): SongServiceClient {
            [$url, $credentials] = $config();

            return new SongServiceClient($url, ['credentials' => $credentials]);
        });

        $this->app->bind(JourneyLogRepositoryInterface::class, JourneyLogRepository::class);

        $this->app->bind(JourneyLogLinkTypeRepositoryInterface::class, JourneyLogLinkTypeRepository::class);

        $this->app->bind(SongRepositoryInterface::class, SongRepository::class);

        $this->app->bind(UuidGeneratorInterface::class, DummyUuidGenerator::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        URL::forceScheme(str_starts_with(config('app.url'), 'https') ? 'https' : 'http');
    }
}
