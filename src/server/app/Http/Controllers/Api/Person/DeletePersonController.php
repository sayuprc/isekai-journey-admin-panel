<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Person;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\Person\DeletePresenter;
use Illuminate\Http\JsonResponse;
use Person\Application\UseCase\Delete\DeleteInputData;
use Person\Application\UseCase\Delete\DeleteUseCase;

class DeletePersonController extends Controller
{
    public function __construct(
        private readonly DeleteUseCase $useCase,
        private readonly DeletePresenter $presenter,
    ) {
    }

    public function handle(string $personId): JsonResponse
    {
        return $this->presenter->present($this->useCase->handle(new DeleteInputData($personId)));
    }
}
