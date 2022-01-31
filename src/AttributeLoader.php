<?php
declare(strict_types=1);

namespace Fratily\AttributeLoader;

use Attribute;
use InvalidArgumentException;
use LogicException;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionClassConstant;
use ReflectionFunctionAbstract;
use ReflectionParameter;
use ReflectionProperty;

/**
 * @phpstan-template T of object
 */
class AttributeLoader
{
    /**
     * @var string
     * @phpstan-var class-string<T>
     */
    private string $attributeClassName;

    /**
     * @var callable|null
     * @phpstan-var (callable(ReflectionAttribute<T>):T)|null
     */
    private $attributeInstanceBuilder;

    /** @var bool */
    private bool $allowSubClasses;

    /**
     * @param string $attributeClassName The load target attribute class name.
     * @param callable|null $attributeInstanceBuilder The attribute instance build callback.
     * @param bool $allowSubClasses If TRUE, subclasses of the specified attribute class will be load.
     *
     * @phpstan-param class-string<T> $attributeClassName
     * @phpstan-param (callable(ReflectionAttribute<T>):T)|null $attributeInstanceBuilder
     */
    public function __construct(
        string $attributeClassName,
        callable|null $attributeInstanceBuilder = null,
        bool $allowSubClasses = false
    ) {
        if (!class_exists($attributeClassName)) {
            throw new InvalidArgumentException(
                "Class {$attributeClassName} is not exists."
            );
        }

        $attr = (new ReflectionClass($attributeClassName))
            ->getAttributes(Attribute::class)[0] ?? null;

        if ($attr === null) {
            throw new InvalidArgumentException(
                "Class {$attributeClassName} cannot be used as attribute."
            );
        }

        $this->attributeClassName = $attributeClassName;
        $this->attributeInstanceBuilder = $attributeInstanceBuilder;
        $this->allowSubClasses = $allowSubClasses;
    }

    /**
     * Returns an instance of the attribute attached to reflection.
     *
     * @param ReflectionClass|ReflectionClassConstant|ReflectionFunctionAbstract|ReflectionParameter|ReflectionProperty $reflection
     * @return object[]
     *
     * @phpstan-return list<T>
     */
    public function load(
        ReflectionClass
        | ReflectionClassConstant
        | ReflectionFunctionAbstract
        | ReflectionParameter
        | ReflectionProperty $reflection
    ): array {
        $flags = 0 | ($this->allowSubClasses ? ReflectionAttribute::IS_INSTANCEOF : 0);
        $attributes = [];

        foreach ($reflection->getAttributes($this->attributeClassName, $flags) as $attribute) {
            $attributeInstance = $this->attributeInstanceBuilder === null
                ? $attribute->newInstance()
                : ($this->attributeInstanceBuilder)($attribute);

            // @phpstan-ignore-next-line T of object will always object.
            if (!is_object($attributeInstance)) {
                $returnedType = gettype($attributeInstance);
                throw new LogicException(
                    'The builder must return an instance of the specified attribute class.'
                    . " Expected instance of {$attribute->getName()}, but {$returnedType} was returned."
                );
            }

            // MEMO: $this->allowSubClasses has no effect here.
            // The builder MUST return an instance of the class specified as attribute.
            if (get_class($attributeInstance) !== $attribute->getName()) {
                $returnedClass = get_class($attributeInstance);
                throw new LogicException(
                    'The builder must return an instance of the specified attribute class.'
                    . " Expected instance of {$attribute->getName()}, but instance of {$returnedClass} was returned."
                );
            }

            $attributes[] = $attributeInstance;
        }

        return $attributes;
    }
}
