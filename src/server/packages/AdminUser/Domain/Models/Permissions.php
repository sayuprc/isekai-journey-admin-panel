<?php

declare(strict_types=1);

namespace AdminUser\Domain\Models;

use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Support\Collection\ImmutableCollection;
use Support\Domain\Error\EntityRuleViolationError;

/**
 * @extends ImmutableCollection<int, Permission>
 */
readonly class Permissions extends ImmutableCollection
{
    /**
     * @param list<string> $items
     *
     * @return Result<self, EntityRuleViolationError>
     */
    public static function fromArray(array $items): Result
    {
        $permissions = [];

        foreach ($items as $item) {
            $result = Permission::tryFrom($item);

            if (is_null($result)) {
                return new Err(new EntityRuleViolationError('権限', '不正な権限です'));
            }

            $permissions[] = $result;
        }

        return new Ok(new self($permissions));
    }

    /**
     * @param list<string> $items
     */
    public static function reconstruct(array $items): self
    {
        return new self(array_map(fn (string $item): Permission => Permission::from($item), $items));
    }

    /**
     * @return list<string>
     */
    public function toArray(): array
    {
        return $this->toGeneric()
            ->map(fn (Permission $item): string => $item->value)
            ->toList();
    }
}
