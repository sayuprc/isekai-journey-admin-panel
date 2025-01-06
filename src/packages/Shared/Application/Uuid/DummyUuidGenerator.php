<?php

declare(strict_types=1);

namespace Shared\Application\Uuid;

use Shared\Uuid\UuidGeneratorInterface;

class DummyUuidGenerator implements UuidGeneratorInterface
{
    /**
     * このシステム上で UUID を発番する必要はないが、ドメインとして UUID を持つので、ダミーの UUID を返す
     */
    public function generate(): string
    {
        return 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';
    }
}
