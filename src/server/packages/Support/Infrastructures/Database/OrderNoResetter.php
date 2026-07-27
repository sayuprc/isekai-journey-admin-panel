<?php

declare(strict_types=1);

namespace Support\Infrastructures\Database;

use Support\Contracts\Uuid\UuidConverterInterface;

/**
 * テーブルの order_no を現行順のまま 10 刻みで振り直す
 */
final readonly class OrderNoResetter
{
    public const int STEP = 10;

    public function __construct(
        private QueryFactory $queryFactory,
        private UuidConverterInterface $converter,
    ) {
    }

    /**
     * @return list<array{id: string, order_no: int}> 更新した行の UUID と新しい order_no
     */
    public function reset(string $table, string $idColumn): array
    {
        $rows = $this->queryFactory->fetchAll(
            $this->queryFactory->select()
                ->withSelect([$idColumn, 'order_no'])
                ->from($table)
                ->orderBy('order_no')
                ->orderBy($idColumn),
        );

        $updated = [];
        $next = self::STEP;
        $now = now()->toDateTimeString();

        foreach ($rows as $row) {
            $current = Row::int($row, 'order_no');
            $binId = Row::string($row, $idColumn);

            if ($current !== $next) {
                $this->queryFactory->update()
                    ->table($table)
                    ->withSet([
                        'order_no' => $next,
                        'updated_at' => $now,
                    ])
                    ->where($idColumn, '=', $binId)
                    ->execute($this->queryFactory->pdo());

                $updated[] = [
                    'id' => $this->converter->toUuid($binId),
                    'order_no' => $next,
                ];
            }

            $next += self::STEP;
        }

        return $updated;
    }
}
