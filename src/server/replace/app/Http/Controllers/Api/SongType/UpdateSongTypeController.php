<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\SongType;

use App\Http\Middleware\Group\SongType;
use App\Http\Presenters\Api\SongType\UpdatePresenter;
use App\Http\Requests\SongType\UpdateRequest;
use App\Http\Responses\JsonResponse;
use SongType\Application\UseCase\Update\UpdateUseCaseInterface;
use Tempest\Http\Method;

class UpdateSongTypeController
{
    public function __construct(
        private readonly UpdateUseCaseInterface $interactor,
        private readonly UpdatePresenter $presenter,
    ) {
    }

    #[SongType(Method::PUT, '/{songTypeId}')]
    public function handle(UpdateRequest $request): JsonResponse
    {
        return $this->presenter->present($this->interactor->handle($request->toInputData()));
    }
}
