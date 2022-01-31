<?php

declare(strict_types=1);

use Fratily\Tests\AttributeLoader\Helper\FooAttribute;
use Fratily\Tests\AttributeLoader\Helper\BarNotAttribute;
use Fratily\Tests\AttributeLoader\Helper\BazAttribute;

#[FooAttribute]
function func_foo_attribute(): void
{
}

// @phpstan-ignore-next-line not an Attribute class.
#[BarNotAttribute]
function func_bar_not_attribute(): void
{
}

#[BazAttribute]
function func_baz_attribute(): void
{
}

#[BazAttribute, FooAttribute]
function func_baz_and_foo_attribute(): void
{
}
