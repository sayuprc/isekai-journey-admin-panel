<?php

declare(strict_types=1);

namespace Release\Infrastructures\Storage;

use Aws\S3\S3Client;
use Release\Application\Admin\Storage\JacketArtStorageInterface;

readonly class R2JacketArtStorage implements JacketArtStorageInterface
{
    public function __construct(
        private S3Client $client,
        private R2JacketArtStorageConfig $config,
    ) {
    }

    public function put(string $key, string $content, string $contentType): string
    {
        $this->client->putObject([
            'Bucket' => $this->config->bucket,
            'Key' => $key,
            'Body' => $content,
            'ContentType' => $contentType,
            'CacheControl' => 'public, max-age=31536000, immutable',
        ]);

        return $this->publicUrl($key);
    }

    private function publicUrl(string $key): string
    {
        return rtrim($this->config->publicUrl, '/') . '/' . $key;
    }
}
