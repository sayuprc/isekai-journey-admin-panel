<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\SongType;

use App\Http\Middleware\Group\SongType;
use App\Http\Presenters\Api\SongType\CreatePresenter;
use App\Http\Requests\SongType\CreateRequest;
use App\Http\Responses\JsonResponse;
use SongType\Application\UseCase\Create\CreateUseCaseInterface;
use Tempest\Http\Method;

class CreateSongTypeController
{
    public function __construct(
        private readonly CreateUseCaseInterface $interactor,
        private readonly CreatePresenter $presenter,
    ) {
    }

    #[SongType(Method::POST, '/')]
    public function handle(CreateRequest $request): JsonResponse
    {
        return $this->presenter->present($this->interactor->handle($request->toInputData()));
    }
}
