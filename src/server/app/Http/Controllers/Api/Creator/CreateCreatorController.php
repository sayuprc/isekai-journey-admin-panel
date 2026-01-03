<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Creator;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\Creator\CreatePresenter;
use Creator\Application\UseCase\Create\CreateInputData;
use Creator\Application\UseCase\Create\CreateUseCaseInterface;
use Illuminate\Http\JsonResponse;

class CreateCreatorController extends Controller
{
    public function __construct(
        private readonly CreateUseCaseInterface $interactor,
        private readonly CreatePresenter $presenter,
    ) {
    }

    public function handle(CreateInputData $inputData): JsonResponse
    {
        return $this->presenter->present($this->interactor->handle($inputData));
    }
}
