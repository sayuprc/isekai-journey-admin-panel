<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\Auth;

use App\Http\Presenters\Api\Support\ResolvesUseCaseError;
use App\Http\Responses\ApiError;
use Auth\Application\Admin\UseCase\Recovery\RecoveryStartOutputData;
use Illuminate\Http\JsonResponse;
use OpenAPI\Admin\Client\Model\RecoveryStartResponse;
use ResultType\Result;
use Support\UseCase\Error\InvalidInputError;
use Support\UseCase\Error\UseCaseError;

class RecoveryStartPresenter
{
    use ResolvesUseCaseError;

    /**
     * @param Result<RecoveryStartOutputData, UseCaseError> $result
     */
    public function present(Result $result): JsonResponse
    {
        [$data, $status] = $result->match(
            static fn (RecoveryStartOutputData $output) => [
                new RecoveryStartResponse()
                    ->setAuthCeremonyId($output->authCeremonyId)
                    ->setPublicKey((object)$output->publicKey),
                200,
            ],
            static function (UseCaseError $error) {
                if ($error instanceof InvalidInputError) {
                    return ApiError::validationFailed($error->errors);
                }

                return ApiError::businessRuleViolation('リカバリーに失敗しました。入力内容を確認してください。');
            },
        );

        return response()->json($data, $status);
    }
}
