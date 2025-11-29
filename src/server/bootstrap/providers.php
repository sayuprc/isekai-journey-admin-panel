<?php

declare(strict_types=1);

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\DatabaseServiceProvider::class,
    App\Providers\Domain\CreatorServiceProvider::class,
    App\Providers\Domain\JourneyLogLinkTypeServiceProvider::class,
    App\Providers\Domain\JourneyLogServiceProvider::class,
    App\Providers\Domain\PerformerServiceProvider::class,
    App\Providers\Domain\SongServiceProvider::class,
    App\Providers\Domain\SongTypeServiceProvider::class,
    App\Providers\Domain\SupportServiceProvider::class,
    App\Providers\Domain\UserServiceProvider::class,
];
