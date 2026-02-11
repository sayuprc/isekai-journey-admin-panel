<?php

declare(strict_types=1);

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\DatabaseServiceProvider::class,
    App\Providers\Domain\AuthServiceProvider::class,
    App\Providers\Domain\CreatorServiceProvider::class,
    App\Providers\Domain\PerformerServiceProvider::class,
    App\Providers\Domain\SongServiceProvider::class,
    App\Providers\Domain\SongTypeServiceProvider::class,
    App\Providers\Domain\SupportServiceProvider::class,
    App\Providers\Domain\AdminUserServiceProvider::class,
];
