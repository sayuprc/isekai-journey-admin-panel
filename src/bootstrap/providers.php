<?php

declare(strict_types=1);

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\GrpcServiceProvider::class,
    App\Providers\ProdServiceProvider::class,
    \Auth\Adapter\Web\Providers\LoginServiceProvider::class,
    App\Features\JourneyLog\Adapter\Web\Providers\CreateServiceProvider::class,
    App\Features\JourneyLog\Adapter\Web\Providers\EditServiceProvider::class,
    App\Features\JourneyLog\Adapter\Web\Providers\DeleteServiceProvider::class,
    App\Features\JourneyLogLinkType\Adapter\Web\Providers\CreateServiceProvider::class,
    App\Features\JourneyLogLinkType\Adapter\Web\Providers\EditServiceProvider::class,
    App\Features\JourneyLogLinkType\Adapter\Web\Providers\DeleteServiceProvider::class,
];
