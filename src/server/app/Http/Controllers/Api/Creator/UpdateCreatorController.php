<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Creator;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\Creator\UpdatePresenter;
use Creator\Application\UseCase\Update\UpdateInputData;
use Creator\Application\UseCase\Update\UpdateUseCase;
use Illuminate\Http\JsonResponse;

class UpdateCreatorController extends Controller
{
    public function __construct(
        private readonly UpdateUseCase $useCase,
        private readonly UpdatePresenter $presenter,
    ) {
    }

    public function handle(UpdateInputData $inputData): JsonResponse
    {
        return $this->presenter->present($this->useCase->handle($inputData));
    }
}
