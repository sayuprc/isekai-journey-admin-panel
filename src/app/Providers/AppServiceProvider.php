<?php

declare(strict_types=1);

namespace App\Providers;

use App\Features\JourneyLog\Domain\Repositories\JourneyLogRepositoryInterface;
use App\Features\JourneyLog\Infrastructures\Repositories\JourneyLogRepository;
use App\Features\JourneyLogLinkType\Domain\Repositories\JourneyLogLinkTypeRepositoryInterface;
use App\Features\JourneyLogLinkType\Infrastructures\Repositories\JourneyLogLinkTypeRepository;
use Generated\IsekaiJourney\JourneyLog\JourneyLogServiceClient;
use Generated\IsekaiJourney\JourneyLogLinkType\JourneyLogLinkTypeServiceClient;
use Grpc\ChannelCredentials;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(JourneyLogServiceClient::class, function (): JourneyLogServiceClient {
            return new JourneyLogServiceClient(config('grpc.api_url'), [
                'credentials' => ChannelCredentials::createSsl(file_get_contents(config('grpc.root_ca'))),
            ]);
        });

        $this->app->bind(JourneyLogLinkTypeServiceClient::class, function (): JourneyLogLinkTypeServiceClient {
            return new JourneyLogLinkTypeServiceClient(config('grpc.api_url'), [
                'credentials' => ChannelCredentials::createSsl(file_get_contents(config('grpc.root_ca'))),
            ]);
        });

        $this->app->bind(JourneyLogRepositoryInterface::class, function (Application $app): JourneyLogRepositoryInterface {
            return $app->make(JourneyLogRepository::class);
        });

        $this->app->bind(JourneyLogLinkTypeRepositoryInterface::class, function (Application $app): JourneyLogLinkTypeRepositoryInterface {
            return $app->make(JourneyLogLinkTypeRepository::class);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        URL::forceScheme(str_starts_with(config('app.url'), 'https') ? 'https' : 'http');
    }
}
