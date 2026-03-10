<?php

declare(strict_types=1);

namespace Support\Infrastructures;

use AdminUser\Domain\Models\AdminUser;
use Auth\Domain\Models\AuthenticatableAdminUser;
use Auth\Domain\Models\Token\RefreshToken\RefreshToken;
use Creator\Domain\Models\Creator;
use CuyZ\Valinor\Mapper\Source\Source;
use CuyZ\Valinor\MapperBuilder;
use Performer\Domain\Models\Performer;
use Song\Domain\Models\Song;
use Support\Contracts\MapperInterface;
use Override;

readonly class Mapper implements MapperInterface
{
    public function __construct(private MapperBuilder $builder)
    {
    }

    #[Override]
    public function map(string $signature, mixed $source): mixed
    {
        $json = ! is_string($source) || ! json_validate($source)
            ? (string)json_encode($source)
            : $source;

        return $this->builder
            // TODO コンストラクタの設定を別のところでできるとよさそう
            ->registerConstructor(AdminUser::reconstruct(...))
            ->registerConstructor(AuthenticatableAdminUser::reconstruct(...))
            ->registerConstructor(RefreshToken::reconstruct(...))
            ->registerConstructor(Creator::reconstruct(...))
            ->registerConstructor(Performer::reconstruct(...))
            ->registerConstructor(Song::reconstruct(...))
            ->allowSuperfluousKeys()
            // 場合によって builder を DI できるようにして適宜変えるのがよさそう
            ->allowScalarValueCasting()
            ->supportDateFormats('Y-m-d', 'Y-m-d H:i:s')
            ->mapper()
            ->map($signature, Source::json($json)->camelCaseKeys());
    }
}
