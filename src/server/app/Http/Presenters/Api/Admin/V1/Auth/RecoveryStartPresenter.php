<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\Auth;

use App\Http\Presenters\Api\Support\ResolvesUseCaseError;
use Auth\Application\Admin\UseCase\Recovery\RecoveryStartOutputData;
use Illuminate\Http\JsonResponse;
use OpenAPI\Client\Model\ErrorResponse;
use OpenAPI\Client\Model\RecoveryStartResponse;
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
            fn (RecoveryStartOutputData $output) => [
                new RecoveryStartResponse()
                    ->setAuthCeremonyId($output->authCeremonyId)
                    ->setPublicKey((object)$output->publicKey),
                200,
            ],
            function (UseCaseError $error) {
                if ($error instanceof InvalidInputError) {
                    return [$this->toValidationError($error), 422];
                }

                return [new ErrorResponse()->setMessage('リカバリーに失敗しました。入力内容を確認してください。'), 400];
            },
        );

        return response()->json($data, $status);
    }
}
