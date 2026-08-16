<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin\V1\Place;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\Admin\V1\Place\DeletePresenter;
use Illuminate\Http\JsonResponse;
use Place\Application\Admin\UseCase\Delete\DeleteInputData;
use Place\Application\Admin\UseCase\Delete\DeleteUseCase;

class DeletePlaceController extends Controller
{
    public function __construct(
        private readonly DeleteUseCase $useCase,
        private readonly DeletePresenter $presenter,
    ) {
    }

    public function handle(string $placeId): JsonResponse
    {
        $this->useCase->handle(new DeleteInputData($placeId));

        return $this->presenter->present();
    }
}
