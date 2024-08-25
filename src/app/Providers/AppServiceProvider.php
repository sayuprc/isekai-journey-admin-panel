<?php

declare(strict_types=1);

namespace App\Providers;

use Generated\IsekaiJourney\JourneyLog\JourneyLogServiceClient;
use Grpc\ChannelCredentials;
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
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        URL::forceScheme(str_starts_with(config('app.url'), 'https') ? 'https' : 'http');
    }
}
