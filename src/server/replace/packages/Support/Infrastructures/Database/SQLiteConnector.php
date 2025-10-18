<?php

declare(strict_types=1);

namespace Support\Infrastructures\Database;

use Emonkak\Database\PDO as EmonkakPDO;
use Emonkak\Database\PDOConnector;
use Emonkak\Database\PDOInterface;
use PDO;
use Support\Infrastructures\Path;

class SQLiteConnector
{
    /** @var array<int, int|bool> */
    private array $options = [
        PDO::ATTR_CASE => PDO::CASE_NATURAL,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_ORACLE_NULLS => PDO::NULL_NATURAL,
        PDO::ATTR_STRINGIFY_FETCHES => false,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    private ?PDOInterface $pdo = null;

    public function __construct(
        private readonly Path $path,
        private readonly SQLiteConfig $config,
    ) {
    }

    public function connect(): PDOInterface
    {
        if (is_null($this->pdo)) {
            $this->pdo = $this->createConnection();
        }

        return $this->pdo;
    }

    private function createConnection(): PDOInterface
    {
        $database = $this->config->path;

        $path = realpath($database) ?: realpath($this->path->base($database));

        $pdo = new PDOConnector(dsn: "sqlite:{$path}", options: $this->options)->getPdo();

        $this->configure($pdo);

        return $pdo;
    }

    private function configure(EmonkakPDO $pdo): void
    {
        $this->configureForeignKeyConstraints($pdo, $this->config->foreignKeyConstraints);
        $this->configureBusyTimeout($pdo, $this->config->busyTimeout);
        $this->configureJournalMode($pdo, $this->config->journalMode);
        $this->configureSynchronous($pdo, $this->config->synchronous);
    }

    private function configureForeignKeyConstraints(EmonkakPDO $pdo, ?bool $foreignKeyConstraints): void
    {
        if (is_null($foreignKeyConstraints)) {
            return;
        }

        $foreignKeys = $foreignKeyConstraints ? 1 : 0;

        $pdo->prepare("pragma foreign_keys = {$foreignKeys}")->execute();
    }

    private function configureBusyTimeout(EmonkakPDO $pdo, ?int $busyTimeout): void
    {
        if (is_null($busyTimeout)) {
            return;
        }

        $pdo->prepare("pragma busy_timeout = {$busyTimeout}")->execute();
    }

    private function configureJournalMode(EmonkakPDO $pdo, ?string $journalMode): void
    {
        if (is_null($journalMode)) {
            return;
        }

        $pdo->prepare("pragma journal_mode = {$journalMode}")->execute();
    }

    private function configureSynchronous(EmonkakPDO $pdo, ?string $synchronous): void
    {
        if (is_null($synchronous)) {
            return;
        }

        $pdo->prepare("pragma synchronous = {$synchronous}")->execute();
    }
}
