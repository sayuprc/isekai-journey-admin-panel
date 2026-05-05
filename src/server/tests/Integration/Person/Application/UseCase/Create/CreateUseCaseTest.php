<?php

declare(strict_types=1);

namespace Tests\Integration\Person\Application\UseCase\Create;

use App\Models\Person\Person as ModelsPerson;
use Mockery;
use Person\Application\UseCase\Create\CreateInputData;
use Person\Application\UseCase\Create\CreateUseCase;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Support\Contracts\AuditLog\AuditAction;
use Support\Contracts\AuditLog\AuditLogRecorderInterface;
use Support\Contracts\AuditLog\AuditTargetType;
use Tests\Support\Concerns\AssertsAuditLog;
use Tests\Support\DatabaseTestCase;

class CreateUseCaseTest extends DatabaseTestCase
{
    use AssertsAuditLog;

    #[Test]
    public function create(): void
    {
        $result = $this->getInstance()->handle(new CreateInputData('ヰ世界情緒'));

        $this->assertTrue($result->isOk());

        $persons = ModelsPerson::query()->get();
        $this->assertCount(1, $persons);
        $this->assertSame('ヰ世界情緒', $persons->first()->name);

        $personId = $this->toUuid($persons->first()->person_id);

        $this->assertAuditLogCount(1);
        $log = $this->findAuditLog(AuditAction::Create, AuditTargetType::Person, $personId);
        $this->assertSame('ヰ世界情緒', $log['snapshot']['name']);
        $this->assertSame($personId, $log['target_id']);
    }

    #[Test]
    public function rollsBackBusinessDataWhenAuditLogRecorderThrows(): void
    {
        $this->privilegedContext();

        $recorder = Mockery::mock(AuditLogRecorderInterface::class);
        $recorder->shouldReceive('record')->andThrow(new RuntimeException('audit log failure'));
        $this->app->instance(AuditLogRecorderInterface::class, $recorder);

        try {
            $this->app->make(CreateUseCase::class)->handle(new CreateInputData('ロールバック対象'));
            $this->fail('RuntimeException が送出されるはず');
        } catch (RuntimeException $e) {
            $this->assertSame('audit log failure', $e->getMessage());
        }

        $this->assertCount(0, ModelsPerson::query()->where('name', 'ロールバック対象')->get());
        $this->assertAuditLogCount(0);
    }

    private function getInstance(): CreateUseCase
    {
        $this->privilegedContext();

        return $this->app->make(CreateUseCase::class);
    }
}
