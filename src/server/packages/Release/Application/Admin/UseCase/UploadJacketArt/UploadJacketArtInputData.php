<?php

declare(strict_types=1);

namespace Release\Application\Admin\UseCase\UploadJacketArt;

readonly class UploadJacketArtInputData
{
    public function __construct(
        public string $content,
        public string $contentType,
    ) {
    }
}
