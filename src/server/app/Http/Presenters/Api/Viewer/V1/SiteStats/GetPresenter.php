<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Viewer\V1\SiteStats;

use App\Http\Presenters\Api\Support\ResolvesUseCaseError;
use Illuminate\Http\JsonResponse;
use ResultType\Result;
use SiteStats\Application\Viewer\UseCase\Get\GetOutputData;
use Support\UseCase\Error\UseCaseError;

class GetPresenter
{
    use ResolvesUseCaseError;

    /**
     * @param Result<GetOutputData, UseCaseError> $result
     */
    public function present(Result $result): JsonResponse
    {
        [$data, $status] = $result->match(
            fn (GetOutputData $outputData) => [
                ['songCount' => $outputData->songCount],
                200,
            ],
            fn (UseCaseError $error) => $this->resolveError($error),
        );

        return response()->json($data, $status);
    }
}
