# Attribute Loader

Attribute Loader wraps [ReflectionAttribute](https://www.php.net/manual/class.reflectionattribute.php).

# How to use

## General

```php
#[Attribute]
class FooAttribute {}

#[FooAttribute]
function target_function(): void {}

$loader = new Fratily\AttributeLoader\AttributeLoader(FooAttribute::class);
$attributes = $loader->load(new ReflectionFunction('target_function'));
var_dump($attributes[0]); // object(FooAttribute)
```

## Custom instance builder

`AttributeLoader::load()` uses `ReflectionAttribute::newInstance()` by default to instantiate.
However, if you want to interrupt the instantiation process for some reason, you can do the following:

```php
#[Attribute]
class FooAttribute {}

#[FooAttribute(name: 'abc')]
function target_function(int $number): void {}

$loader = new Fratily\AttributeLoader\AttributeLoader(
    FooAttribute::class,
    function (ReflectionAttribute $attr) {
        var_dump($attr->getArguments()); // array('name' => 'abc')

        // do something ...
        // ex: trigger event / customize attribute arguments ...

        // MUST return an instance of $attr->getName().
        // MUST not return a subclass of $attr->getName().
        return new FooAttribute();
    }
);
$attributes = $loader->load(new ReflectionFunction('target_function'));
var_dump($attributes[0]); // object(FooAttribute)
```
