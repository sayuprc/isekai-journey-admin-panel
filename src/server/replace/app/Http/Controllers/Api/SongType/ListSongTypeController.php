<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\SongType;

use App\Http\Middleware\Group\SongType;
use App\Http\Presenters\Api\SongType\ListPresenter;
use App\Http\Responses\JsonResponse;
use SongType\Application\UseCase\List\ListUseCaseInterface;
use Tempest\Http\Method;

class ListSongTypeController
{
    public function __construct(
        private readonly ListUseCaseInterface $interactor,
        private readonly ListPresenter $presenter,
    ) {
    }

    #[SongType(Method::GET, '/')]
    public function handle(): JsonResponse
    {
        return $this->presenter->present($this->interactor->handle());
    }
}
