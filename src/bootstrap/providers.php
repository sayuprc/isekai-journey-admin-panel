<?php

declare(strict_types=1);

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\GrpcServiceProvider::class,
    App\Providers\ProdServiceProvider::class,
    App\Features\Auth\Adapter\Web\Providers\LoginServiceProvider::class,
];
