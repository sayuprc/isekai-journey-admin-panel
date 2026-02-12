<?php

declare(strict_types=1);

namespace Support\Infrastructures;

use BackedEnum;
use DateTimeInterface;
use JsonSerializable;
use ReflectionClass;
use ReflectionProperty;
use UnitEnum;

class Serializer
{
    /** @var array<class-string, array<ReflectionProperty>> */
    private static array $propertiesCache = [];

    public function serialize(mixed $data): mixed
    {
        if (is_array($data)) {
            $result = [];
            foreach ($data as $key => $value) {
                $result[$key] = $this->serialize($value);
            }

            return $result;
        }

        if (is_object($data)) {
            if ($data instanceof UnitEnum) {
                return $data instanceof BackedEnum ? $data->value : $data->name;
            }

            if ($data instanceof DateTimeInterface) {
                return $data->format(DateTimeInterface::ATOM);
            }

            if ($data instanceof JsonSerializable) {
                return $data->jsonSerialize();
            }

            $result = [];
            $reflection = new ReflectionClass($data);
            $properties = $this->getProperties($reflection);

            foreach ($properties as $property) {
                if ($property->isStatic()) {
                    continue;
                }

                $property->setAccessible(true);
                if ($property->isInitialized($data)) {
                    $value = $property->getValue($data);
                    $result[$property->getName()] = $this->serialize($value);
                }
            }

            return $result;
        }

        return $data;
    }

    /**
     * @return array<ReflectionProperty>
     */
    private function getProperties(ReflectionClass $reflection): array
    {
        $className = $reflection->getName();
        if (isset(self::$propertiesCache[$className])) {
            return self::$propertiesCache[$className];
        }

        $properties = $reflection->getProperties();
        $parent = $reflection->getParentClass();

        if ($parent) {
            $parentProperties = $this->getProperties($parent);
            foreach ($parentProperties as $prop) {
                $exists = false;
                foreach ($properties as $p) {
                    if ($p->getName() === $prop->getName()) {
                        $exists = true;

                        break;
                    }
                }

                if (! $exists) {
                    $properties[] = $prop;
                }
            }
        }

        return self::$propertiesCache[$className] = $properties;
    }
}
