<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\Auth;

use App\Http\Presenters\Api\Support\ResolvesUseCaseError;
use App\Http\Responses\ApiError;
use Auth\Application\Admin\UseCase\RegisterStart\RegisterStartOutputData;
use Illuminate\Http\JsonResponse;
use OpenAPI\Admin\Client\Model\RegisterStartResponse;
use ResultType\Result;
use Support\UseCase\Error\InvalidInputError;
use Support\UseCase\Error\UseCaseError;

class RegisterStartPresenter
{
    use ResolvesUseCaseError;

    /**
     * @param Result<RegisterStartOutputData, UseCaseError> $result
     */
    public function present(Result $result): JsonResponse
    {
        [$data, $status] = $result->match(
            static fn (RegisterStartOutputData $output) => [
                new RegisterStartResponse()
                    ->setAuthCeremonyId($output->authCeremonyId)
                    ->setPublicKey((object)$output->publicKey),
                200,
            ],
            static function (UseCaseError $error) {
                if ($error instanceof InvalidInputError) {
                    return ApiError::validationFailed($error->errors);
                }

                return ApiError::businessRuleViolation('登録に失敗しました。入力内容を確認してください。');
            },
        );

        return response()->json($data, $status);
    }
}
