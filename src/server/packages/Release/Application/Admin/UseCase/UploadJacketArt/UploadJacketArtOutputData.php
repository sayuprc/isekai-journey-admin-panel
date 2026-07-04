<?php

declare(strict_types=1);

namespace Release\Application\Admin\UseCase\UploadJacketArt;

readonly class UploadJacketArtOutputData
{
    public function __construct(public string $jacketArtUrl)
    {
    }
}
