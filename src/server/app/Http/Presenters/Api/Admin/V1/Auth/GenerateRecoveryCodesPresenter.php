<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\Auth;

use App\Http\Presenters\Api\Support\ResolvesUseCaseError;
use Auth\Application\Admin\UseCase\RecoveryCode\Generate\GenerateRecoveryCodesOutputData;
use Illuminate\Http\JsonResponse;
use OpenAPI\Client\Model\GenerateRecoveryCodesResponse;
use ResultType\Result;
use Support\UseCase\Error\UseCaseError;

class GenerateRecoveryCodesPresenter
{
    use ResolvesUseCaseError;

    /**
     * @param Result<GenerateRecoveryCodesOutputData, UseCaseError> $result
     */
    public function present(Result $result): JsonResponse
    {
        [$data, $status] = $result->match(
            fn (GenerateRecoveryCodesOutputData $output) => [
                new GenerateRecoveryCodesResponse()
                    ->setRecoveryCodes($output->plainCodes),
                200,
            ],
            fn (UseCaseError $error) => $this->resolveError($error),
        );

        return response()->json($data, $status);
    }
}
