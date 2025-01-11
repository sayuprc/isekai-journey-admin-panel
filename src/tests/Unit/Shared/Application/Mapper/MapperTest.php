<?php

declare(strict_types=1);

namespace Shared\Application\Mapper;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Shared\Mapper\MapperInterface;
use stdClass;
use Tests\TestCase;

class MapperTest extends TestCase
{
    #[Test]
    #[DataProvider('arrayData')]
    public function mapWithArray(array $source): void
    {
        $mapper = $this->getMapper();

        $instance = $mapper->map(Sample::class, $source);

        $this->assertInstanceOf(Sample::class, $instance);
        $this->assertSame($instance->id, $source['id']);
        $this->assertSame($instance->name, $source['name']);
        $this->assertCount(count($source['items']), $instance->items);
        $this->assertSame($instance->items[0]->id, $source['items'][0]['id']);
        $this->assertSame($instance->items[0]->name, $source['items'][0]['name']);
        $this->assertSame($instance->items[1]->id, $source['items'][1]['id']);
        $this->assertSame($instance->items[1]->name, $source['items'][1]['name']);
    }

    public static function arrayData(): array
    {
        return [
            [
                [
                    'id' => 1,
                    'name' => 'sample',
                    'items' => [
                        [
                            'id' => 1,
                            'name' => 'item name 1',
                        ],
                        [
                            'id' => 2,
                            'name' => 'item name 2',
                        ],
                    ],
                ],
            ],
        ];
    }

    #[Test]
    #[DataProvider('withStdClassData')]
    public function mapWithStdClass(stdClass $source): void
    {
        $mapper = $this->getMapper();

        $instance = $mapper->map(Sample::class, $source);

        $this->assertInstanceOf(Sample::class, $instance);
        $this->assertSame($instance->id, $source->id);
        $this->assertSame($instance->name, $source->name);
        $this->assertCount(count($source->items), $instance->items);
        $this->assertSame($instance->items[0]->id, $source->items[0]->id);
        $this->assertSame($instance->items[0]->name, $source->items[0]->name);
        $this->assertSame($instance->items[1]->id, $source->items[1]->id);
        $this->assertSame($instance->items[1]->name, $source->items[1]->name);
    }

    public static function withStdClassData(): array
    {
        $item1 = new stdClass();
        $item1->id = 1;
        $item1->name = 'item name 1';

        $item2 = new stdClass();
        $item2->id = 2;
        $item2->name = 'item name 2';

        $sample1 = new stdClass();
        $sample1->id = 1;
        $sample1->name = 'sample';
        $sample1->items = [$item1, $item2];

        return [
            [$sample1],
        ];
    }

    #[Test]
    #[DataProvider('withJsonData')]
    public function mapWithJson(string $source): void
    {
        $mapper = $this->getMapper();

        $instance = $mapper->map(Sample::class, $source);

        $expected = json_decode($source, true);

        $this->assertInstanceOf(Sample::class, $instance);
        $this->assertSame($instance->id, $expected['id']);
        $this->assertSame($instance->name, $expected['name']);
        $this->assertCount(count($expected['items']), $instance->items);
        $this->assertSame($instance->items[0]->id, $expected['items'][0]['id']);
        $this->assertSame($instance->items[0]->name, $expected['items'][0]['name']);
        $this->assertSame($instance->items[1]->id, $expected['items'][1]['id']);
        $this->assertSame($instance->items[1]->name, $expected['items'][1]['name']);
    }

    public static function withJsonData(): array
    {
        return [
            [
                json_encode([
                    'id' => 1,
                    'name' => 'sample',
                    'items' => [
                        [
                            'id' => 1,
                            'name' => 'item name 1',
                        ],
                        [
                            'id' => 2,
                            'name' => 'item name 2',
                        ],
                    ],
                ]),
            ],
        ];
    }

    private function getMapper(): Mapper
    {
        $mapper = $this->app->get(Mapper::class);
        assert($mapper instanceof MapperInterface);

        return $mapper;
    }
}

class Sample
{
    /**
     * @param array<Item> $items
     */
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly array $items
    ) {
    }
}

class Item
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
    ) {
    }
}
