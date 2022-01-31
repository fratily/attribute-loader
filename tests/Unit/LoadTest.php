<?php

declare(strict_types=1);

namespace Fratily\Tests\AttributeLoader\Unit;

// phpcs:disable -- PSR1.Files.SideEffects.FoundWithSymbols
include __DIR__ . '/load_test_helper.php';
// phpcs:enable

use Error;
use Fratily\AttributeLoader\AttributeLoader;
use Fratily\Tests\AttributeLoader\Helper\BarNotAttribute;
use Fratily\Tests\AttributeLoader\Helper\BazAttribute;
use Fratily\Tests\AttributeLoader\Helper\FooAttribute;
use LogicException;
use PHPUnit\Framework\TestCase;
use ReflectionFunction;

class LoadTest extends TestCase
{
    public function testSubclassWillNotBeDetectedIfAllowSubClassIsNone(): void
    {
        $attributes = (new AttributeLoader(FooAttribute::class))->load(
            new ReflectionFunction('func_baz_and_foo_attribute')
        );

        $this->assertCount(1, $attributes);
        $this->assertSame(FooAttribute::class, get_class($attributes[0]));
    }

    public function testSubclassWillNotBeDetectedIfAllowSubClassIsFalse(): void
    {
        $attributes = (new AttributeLoader(FooAttribute::class, null, false))->load(
            new ReflectionFunction('func_baz_and_foo_attribute')
        );

        $this->assertCount(1, $attributes);
        $this->assertSame(FooAttribute::class, get_class($attributes[0]));
    }

    public function testSubclassWillBeDetectedIfAllowSubClassIsTrue(): void
    {
        $attributes = (new AttributeLoader(FooAttribute::class, null, true))->load(
            new ReflectionFunction('func_baz_and_foo_attribute')
        );

        $this->assertCount(2, $attributes);
        $this->assertSame(BazAttribute::class, get_class($attributes[0]));
        $this->assertSame(FooAttribute::class, get_class($attributes[1]));
    }

    /**
     * @dataProvider dataProviderInvalidBuilder
     * @phpstan-param callable(\ReflectionAttribute<FooAttribute>):FooAttribute $builder
     * @phpstan-param class-string<\Throwable> $exception
     */
    public function testInvalidBuilder(
        callable $builder,
        bool $allow_sub_class,
        string $exception,
        string $exception_message
    ): void {
        $this->expectException($exception);
        $this->expectExceptionMessage($exception_message);

        (new AttributeLoader(FooAttribute::class, $builder, $allow_sub_class))->load(
            new ReflectionFunction('func_foo_attribute')
        );
    }
    // phpcs:disable Generic.Files.LineLength.TooLong
    /**
     * @phpstan-return array<string,array{callable(\ReflectionAttribute<\Attribute>):mixed,bool,class-string<\Throwable>,string}>
     */
    // phpcs:enable Generic.Files.LineLength.TooLong
    public function dataProviderInvalidBuilder(): array
    {
        return [
            'returned not object' => [
                fn() => 'not object',
                false,
                LogicException::class,
                'The builder must return an instance of the specified attribute class.'
                . ' Expected instance of ' . FooAttribute::class . ', but string was returned.'
            ],
            'returned sub class' => [
                fn() => new BazAttribute(),
                true,
                LogicException::class,
                // phpcs:disable Generic.Files.LineLength.TooLong
                'The builder must return an instance of the specified attribute class.'
                . ' Expected instance of ' . FooAttribute::class . ', but instance of ' . BazAttribute::class . ' was returned.'
                // phpcs:enable Generic.Files.LineLength.TooLong
            ],
        ];
    }

    public function testNotAttributeClassCannotMakeInstanceIfWithoutBuilder(): void
    {
        $this->expectException(Error::class);
        $this->expectExceptionMessage(
            'Attempting to use non-attribute class "Fratily\Tests\AttributeLoader\Helper\BarNotAttribute" as attribute'
        );

        (new AttributeLoader(FooAttribute::class, null, true))->load(
            new ReflectionFunction('func_bar_not_attribute')
        );
    }

    public function testNotAttributeClassCanMakeInstanceIfWithBuilder(): void
    {
        $attributes = (new AttributeLoader(FooAttribute::class, fn() => new BarNotAttribute(), true))->load(
            new ReflectionFunction('func_bar_not_attribute')
        );

        $this->assertCount(1, $attributes);
        $this->assertSame(BarNotAttribute::class, get_class($attributes[0]));
    }
}
