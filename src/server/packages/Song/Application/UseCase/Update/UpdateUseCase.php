<?php

declare(strict_types=1);

namespace Song\Application\UseCase\Update;

use AdminUser\Domain\Models\Permission;
use Auth\Domain\Models\AuthContext;
use LogicException;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Song\Application\Assemble\AssembledSong;
use Song\Application\Assemble\SongAssembler;
use Song\Domain\Models\SongRepositoryInterface;
use Song\Domain\Services\SongIntegrityService;
use Support\Contracts\AuditLog\AuditAction;
use Support\Contracts\AuditLog\AuditLogRecorderInterface;
use Support\Contracts\AuditLog\AuditTargetType;
use Support\Contracts\TransactionInterface;
use Support\Domain\Error\BusinessRuleViolationError;
use Support\Domain\Error\DomainError;
use Support\Domain\Error\DomainValidationError;
use Support\Domain\Error\EntityRuleViolationError;
use Support\UseCase\Authorizer\UseCaseAuthorizer;
use Support\UseCase\Error\BusinessLogicError;
use Support\UseCase\Error\InvalidInputError;
use Support\UseCase\Error\UseCaseError;

readonly class UpdateUseCase
{
    public function __construct(
        private UseCaseAuthorizer $authorizer,
        private TransactionInterface $transaction,
        private SongRepositoryInterface $repository,
        private SongIntegrityService $service,
        private SongAssembler $assembler,
        private AuditLogRecorderInterface $recorder,
        private AuthContext $authContext,
    ) {
    }

    /**
     * @return Result<UpdateOutputData, UseCaseError>
     */
    public function handle(UpdateInputData $inputData): Result
    {
        return $this->authorizer->require(Permission::WriteSong)
            ->andThen(fn () => $this->updateSong($inputData));
    }

    /**
     * @return Result<UpdateOutputData, UseCaseError>
     */
    private function updateSong(UpdateInputData $inputData): Result
    {
        return $this->transaction->scope(function () use ($inputData): Result {
            $result = $this->service->prepareForUpdate(
                $inputData->songId,
                $inputData->title,
                $inputData->description,
                $inputData->lyricsLink,
                $inputData->typeValue,
                $inputData->isDisplay,
                $inputData->orderNo,
                $inputData->tags,
                $inputData->persons,
                $inputData->media,
            );

            if ($result->isErr()) {
                return new Err($this->handleError($result->unwrapErr()));
            }

            $song = $this->repository->save($result->unwrap());
            $assembled = $this->assembler->assemble($song);

            $actor = $this->authContext->get();
            assert(! is_null($actor));

            $this->recorder->record(
                $actor->adminUserId->value,
                AuditAction::Update,
                AuditTargetType::Song,
                $assembled->songId,
                $this->snapshot($assembled),
            );

            return new Ok(new UpdateOutputData($assembled));
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function snapshot(AssembledSong $assembled): array
    {
        return [
            'songId' => $assembled->songId,
            'title' => $assembled->title,
            'description' => $assembled->description,
            'lyricsLink' => $assembled->lyricsLink,
            'typeName' => $assembled->typeName,
            'typeValue' => $assembled->typeValue,
            'isDisplay' => $assembled->isDisplay,
            'orderNo' => $assembled->orderNo,
            'tagIds' => array_map(fn ($t) => $t->songTagId, $assembled->tags),
            'persons' => array_map(
                fn ($p) => [
                    'personId' => $p->personId,
                    'role' => $p->role->value,
                    'orderNo' => $p->orderNo,
                ],
                $assembled->persons,
            ),
        ];
    }

    private function handleError(DomainError $error): UseCaseError
    {
        return match (true) {
            $error instanceof DomainValidationError => new InvalidInputError($error->errors),
            $error instanceof EntityRuleViolationError => new InvalidInputError([$error->field => [$error->message]]),
            $error instanceof BusinessRuleViolationError => new BusinessLogicError($error->message),
            default => throw new LogicException('予期しないドメインエラーが発生しました: ' . $error::class),
        };
    }
}
