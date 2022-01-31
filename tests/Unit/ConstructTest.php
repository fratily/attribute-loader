<?php
declare(strict_types=1);

namespace Fratily\Tests\AttributeLoader\Unit;

use Fratily\AttributeLoader\AttributeLoader;
use Fratily\Tests\AttributeLoader\Helper\BarNotAttribute;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ConstructTest extends TestCase
{
    /**
     * @dataProvider dataProvider_invalidParameters
     *
     * @template T of object
     * @phpstan-param class-string<T> $attributeClass
     * @phpstan-param (callable(\ReflectionAttribute<T>):T)|null $attributeInstanceBuilder
     */
    public function test_invalidParameters(
        string $exceptionMessage,
        string $attributeClass,
        callable|null $attributeInstanceBuilder,
        bool $allowSubClass
    ): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($exceptionMessage);

        new AttributeLoader($attributeClass, $attributeInstanceBuilder, $allowSubClass);
    }

    /**
     * @phpstan-return array<string,array{string,class-string,callable|null,bool}>
     */
    public function dataProvider_invalidParameters(): array
    {
        /** @phpstan-var class-string */
        $not_exists_class = 'not_exists_class';
        $cannot_be_used_as_attribute = BarNotAttribute::class;
        return [
            'not exists class' => [
                "Class {$not_exists_class} is not exists.",
                $not_exists_class,
                null,
                false,
            ],
            'cannot be used as attribute' => [
                "Class {$cannot_be_used_as_attribute} cannot be used as attribute.",
                $cannot_be_used_as_attribute,
                null,
                false,
            ],
        ];
    }
}
