<?php

declare(strict_types=1);

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\GrpcServiceProvider::class,
    App\Providers\ProdServiceProvider::class,
    \App\Providers\RequestServiceProvider::class,
    \JourneyLog\Adapter\Web\Providers\CreateServiceProvider::class,
    \JourneyLog\Adapter\Web\Providers\EditServiceProvider::class,
    \JourneyLog\Adapter\Web\Providers\DeleteServiceProvider::class,
    \JourneyLogLinkType\Adapter\Web\Providers\CreateServiceProvider::class,
    \JourneyLogLinkType\Adapter\Web\Providers\EditServiceProvider::class,
    \JourneyLogLinkType\Adapter\Web\Providers\DeleteServiceProvider::class,
];
