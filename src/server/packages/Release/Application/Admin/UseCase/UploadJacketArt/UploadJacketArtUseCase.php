<?php

declare(strict_types=1);

namespace Release\Application\Admin\UseCase\UploadJacketArt;

use AdminUser\Domain\Models\Permission;
use Release\Application\Admin\Storage\JacketArtStorageInterface;
use Support\Contracts\Uuid\UuidGeneratorInterface;
use Support\Domain\Exceptions\DomainValidationException;
use Support\UseCase\Authorizer\UseCaseAuthorizer;

readonly class UploadJacketArtUseCase
{
    private const int MAX_BYTES = 20 * 1024 * 1024;

    /** @var array<string, string> */
    private const array EXTENSIONS = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    public function __construct(
        private UseCaseAuthorizer $authorizer,
        private UuidGeneratorInterface $generator,
        private JacketArtStorageInterface $storage,
    ) {
    }

    public function handle(UploadJacketArtInputData $inputData): UploadJacketArtOutputData
    {
        $this->authorizer->ensure(Permission::WriteRelease);

        $contentType = $this->normalizeContentType($inputData->contentType);
        $errors = $this->validate($inputData->content, $contentType);

        if ($errors !== []) {
            throw new DomainValidationException($errors);
        }

        $key = sprintf(
            'release-jacket-art/%s.%s',
            $this->generator->generate(),
            self::EXTENSIONS[$contentType],
        );

        return new UploadJacketArtOutputData(
            $this->storage->put($key, $inputData->content, $contentType),
        );
    }

    /**
     * @return array<string, list<string>>
     */
    private function validate(string $content, string $contentType): array
    {
        $messages = [];

        if ($content === '') {
            $messages[] = '画像ファイルは必須です';
        }

        if (strlen($content) > self::MAX_BYTES) {
            $messages[] = '画像ファイルは20MB以下にしてください';
        }

        if (! array_key_exists($contentType, self::EXTENSIONS)) {
            $messages[] = '画像ファイルはJPEG、PNG、WebPのいずれかを指定してください';
        }

        if ($content !== '' && $messages === []) {
            /** @var array{0: int, 1: int, 2: int, 3: string, mime: string, channels?: int, bits?: int}|false $imageInfo */
            $imageInfo = @getimagesizefromstring($content);

            if ($imageInfo === false || $imageInfo['mime'] !== $contentType) {
                $messages[] = '画像ファイルの内容が不正です';
            }
        }

        return $messages === [] ? [] : ['jacketArt' => $messages];
    }

    private function normalizeContentType(string $contentType): string
    {
        return strtolower(trim(explode(';', $contentType, 2)[0]));
    }
}
