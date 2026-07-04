<?php

declare(strict_types=1);

namespace Release\Infrastructures\Storage;

readonly class R2JacketArtStorageConfig
{
    public function __construct(
        public string $accessKeyId,
        public string $secretAccessKey,
        public string $endpoint,
        public string $bucket,
        public string $region,
        public string $publicUrl,
        public bool $usePathStyleEndpoint,
    ) {
    }
}
