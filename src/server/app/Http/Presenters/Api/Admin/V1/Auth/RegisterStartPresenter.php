<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\Auth;

use App\Http\Presenters\Api\Support\ResolvesUseCaseError;
use Auth\Application\Admin\UseCase\RegisterStart\RegisterStartOutputData;
use Illuminate\Http\JsonResponse;
use OpenAPI\Admin\Client\Model\ErrorResponse;
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
            function (UseCaseError $error) {
                if ($error instanceof InvalidInputError) {
                    return [$this->toValidationError($error), 422];
                }

                return [new ErrorResponse()->setMessage('登録に失敗しました。入力内容を確認してください。'), 400];
            },
        );

        return response()->json($data, $status);
    }
}
