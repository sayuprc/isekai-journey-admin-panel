<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\SongType;

use App\Http\Middleware\Group\SongType;
use App\Http\Presenters\Api\SongType\DeletePresenter;
use App\Http\Responses\JsonResponse;
use SongType\Application\UseCase\Delete\DeleteInputData;
use SongType\Application\UseCase\Delete\DeleteUseCaseInterface;
use Tempest\Http\Method;

class DeleteSongTypeController
{
    public function __construct(
        private readonly DeleteUseCaseInterface $interactor,
        private readonly DeletePresenter $presenter,
    ) {
    }

    #[SongType(Method::DELETE, '/{songTypeId}')]
    public function handle(string $songTypeId): JsonResponse
    {
        $this->interactor->handle(new DeleteInputData($songTypeId));

        return $this->presenter->present();
    }
}
