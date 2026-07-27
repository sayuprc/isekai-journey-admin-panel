<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\ReleaseGroup;

use App\Http\Presenters\Api\Support\ResolvesUseCaseError;
use Illuminate\Http\JsonResponse;
use OpenAPI\Admin\Client\Model\OrderNoResetResponse;
use Release\Application\Admin\UseCase\Group\ResetOrderNumbers\ResetOrderNumbersOutputData;
use ResultType\Result;
use Support\UseCase\Error\UseCaseError;

class ResetOrderNumbersPresenter
{
    use ResolvesUseCaseError;

    /**
     * @param Result<ResetOrderNumbersOutputData, UseCaseError> $result
     */
    public function present(Result $result): JsonResponse
    {
        [$data, $status] = $result->match(
            static fn (ResetOrderNumbersOutputData $outputData) => [
                new OrderNoResetResponse()->setUpdatedCount($outputData->updatedCount),
                200,
            ],
            fn (UseCaseError $error) => $this->resolveError($error),
        );

        return response()->json($data, $status);
    }
}
