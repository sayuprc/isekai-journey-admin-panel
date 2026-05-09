<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin\V1\Release;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\Admin\V1\Release\CreatePresenter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Release\Application\Admin\UseCase\Create\CreateInputData;
use Release\Application\Admin\UseCase\Create\CreateUseCase;
use Support\Contracts\MapperInterface;

class CreateReleaseController extends Controller
{
    public function __construct(
        private readonly MapperInterface $mapper,
        private readonly CreateUseCase $useCase,
        private readonly CreatePresenter $presenter,
    ) {
    }

    public function handle(Request $request): JsonResponse
    {
        return $this->buildInput($request)
            |> $this->useCase->handle(...)
            |> $this->presenter->present(...);
    }

    private function buildInput(Request $request): CreateInputData
    {
        return $this->mapper->map(
            CreateInputData::class,
            [
                ...$request->all(),
                'description' => $request->string('description')->toString(),
                'trackEntries' => $request->array('trackEntries'),
            ],
        );
    }
}
