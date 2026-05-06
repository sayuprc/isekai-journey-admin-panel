<?php

declare(strict_types=1);

namespace Media\Application\UseCase\Delete;

use AdminUser\Domain\Models\Permission;
use Media\Domain\Models\MediaId;
use Media\Domain\Models\MediaRepositoryInterface;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Support\UseCase\Authorizer\UseCaseAuthorizer;
use Support\UseCase\Error\BusinessLogicError;
use Support\UseCase\Error\InvalidInputError;
use Support\UseCase\Error\UseCaseError;

readonly class DeleteUseCase
{
    public function __construct(
        private UseCaseAuthorizer $authorizer,
        private MediaRepositoryInterface $repository,
    ) {
    }

    /**
     * @return Result<null, UseCaseError>
     */
    public function handle(DeleteInputData $inputData): Result
    {
        return $this->authorizer->require(Permission::WriteMedia)
            ->andThen(fn () => $this->deleteMedia($inputData));
    }

    /**
     * @return Result<null, UseCaseError>
     */
    private function deleteMedia(DeleteInputData $inputData): Result
    {
        return MediaId::create($inputData->mediaId)
            ->mapErr(fn (): UseCaseError => new InvalidInputError(['mediaId' => ['IDが不正です']]))
            ->andThen(function (MediaId $mediaId): Result {
                if ($this->repository->isUsed($mediaId)) {
                    return new Err(new BusinessLogicError('このメディアは楽曲に使用されているため削除できません'));
                }

                $this->repository->delete($mediaId);

                return new Ok(null);
            });
    }
}
