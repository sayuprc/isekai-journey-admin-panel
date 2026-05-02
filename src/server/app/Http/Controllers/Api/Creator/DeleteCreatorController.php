<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Creator;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\Creator\DeletePresenter;
use Creator\Application\UseCase\Delete\DeleteInputData;
use Creator\Application\UseCase\Delete\DeleteUseCase;
use Illuminate\Http\JsonResponse;

class DeleteCreatorController extends Controller
{
    public function __construct(
        private readonly DeleteUseCase $useCase,
        private readonly DeletePresenter $presenter,
    ) {
    }

    public function handle(string $creatorId): JsonResponse
    {
        return $this->presenter->present($this->useCase->handle(new DeleteInputData($creatorId)));
    }
}
