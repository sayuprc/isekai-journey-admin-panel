<?php

declare(strict_types=1);

namespace App\Providers;

use Generated\IsekaiJourney\JourneyLog\JourneyLogServiceClient;
use Generated\IsekaiJourney\JourneyLogLinkType\JourneyLogLinkTypeServiceClient;
use Generated\IsekaiJourney\Song\SongServiceClient;
use Grpc\ChannelCredentials;
use Illuminate\Support\ServiceProvider;

class GrpcServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(JourneyLogServiceClient::class, function (): JourneyLogServiceClient {
            [$url, $credentials] = $this->getConfig();

            return new JourneyLogServiceClient($url, ['credentials' => $credentials]);
        });

        $this->app->bind(JourneyLogLinkTypeServiceClient::class, function (): JourneyLogLinkTypeServiceClient {
            [$url, $credentials] = $this->getConfig();

            return new JourneyLogLinkTypeServiceClient($url, ['credentials' => $credentials]);
        });

        $this->app->bind(SongServiceClient::class, function (): SongServiceClient {
            [$url, $credentials] = $this->getConfig();

            return new SongServiceClient($url, ['credentials' => $credentials]);
        });
    }

    public function boot(): void
    {
    }

    /**
     * @return array{0: string, 1: ChannelCredentials}
     */
    private function getConfig(): array
    {
        $url = config('grpc.api_url');
        assert(is_string($url));

        $rootCa = config('grpc.root_ca');
        assert(is_string($rootCa));

        return [
            $url,
            ChannelCredentials::createSsl((string)file_get_contents($rootCa)),
        ];
    }
}
