<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\SongType;

use App\Http\Middleware\Group\SongType;
use App\Http\Presenters\Api\SongType\GetPresenter;
use App\Http\Responses\JsonResponse;
use SongType\Application\UseCase\Get\GetInputData;
use SongType\Application\UseCase\Get\GetUseCaseInterface;
use Tempest\Http\Method;

class GetSongTypeController
{
    public function __construct(
        private readonly GetUseCaseInterface $interactor,
        private readonly GetPresenter $presenter,
    ) {
    }

    #[SongType(Method::GET, '/{songTypeId}')]
    public function handle(string $songTypeId): JsonResponse
    {
        return $this->presenter->present($this->interactor->handle(new GetInputData($songTypeId)));
    }
}
