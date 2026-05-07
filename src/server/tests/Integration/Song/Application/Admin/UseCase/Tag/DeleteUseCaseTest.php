<?php

declare(strict_types=1);

namespace Tests\Integration\Song\Application\Admin\UseCase\Tag;

use App\Models\Song\SongTag as ModelsSongTag;
use PHPUnit\Framework\Attributes\Test;
use Song\Application\Admin\UseCase\Tag\Delete\DeleteInputData;
use Song\Application\Admin\UseCase\Tag\Delete\DeleteUseCase;
use Support\UseCase\AuditLog\AuditAction;
use Support\UseCase\AuditLog\AuditTargetType;
use Tests\Support\Concerns\AssertsAuditLog;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class DeleteUseCaseTest extends DatabaseTestCase
{
    use AssertsAuditLog;
    use EntityFactory;
    use EntityStore;

    #[Test]
    public function canDelete(): void
    {
        $uuid = $this->generateUuid();

        $this->storeSongTags($this->createSongTag($uuid, 'タグ', 1));

        $result = $this->getInstance()->handle(new DeleteInputData($uuid));

        $this->assertTrue($result->isOk());
        $this->assertCount(0, ModelsSongTag::query()->get()->all());

        $this->assertAuditLogCount(1);
        $log = $this->findAuditLog(AuditAction::Delete, AuditTargetType::SongTag, $uuid);
        $this->assertSame('タグ', $log['snapshot']['name']);
    }

    private function getInstance(): DeleteUseCase
    {
        $this->privilegedContext();

        return $this->app->make(DeleteUseCase::class);
    }
}
