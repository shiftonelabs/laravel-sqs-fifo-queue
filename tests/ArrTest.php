<?php

namespace ShiftOneLabs\LaravelSqsFifoQueue\Tests;

use ArrayObject;
use ShiftOneLabs\LaravelSqsFifoQueue\Support\Arr;

class ArrTest extends TestCase
{
    public function test_array_only()
    {
        $array = ['name' => 'Desk', 'price' => 100, 'orders' => 10];
        $array = Arr::only($array, ['name', 'price']);
        $this->assertEquals(['name' => 'Desk', 'price' => 100], $array);
        $this->assertEmpty(Arr::only($array, ['nonExistingKey']));
    }

    public function test_array_pull()
    {
        $array = ['name' => 'Desk', 'price' => 100];
        $name = Arr::pull($array, 'name');
        $this->assertSame('Desk', $name);
        $this->assertEquals(['price' => 100], $array);

        // Only works on first level keys
        $array = ['joe@example.com' => 'Joe', 'jane@localhost' => 'Jane'];
        $name = Arr::pull($array, 'joe@example.com');
        $this->assertSame('Joe', $name);
        $this->assertEquals(['jane@localhost' => 'Jane'], $array);

        // Does not work for nested keys
        $array = ['emails' => ['joe@example.com' => 'Joe', 'jane@localhost' => 'Jane']];
        $name = Arr::pull($array, 'emails.joe@example.com');
        $this->assertNull($name);
        $this->assertEquals(['emails' => ['joe@example.com' => 'Joe', 'jane@localhost' => 'Jane']], $array);
    }

    public function test_array_get()
    {
        $array = ['products.desk' => ['price' => 100]];
        $this->assertEquals(['price' => 100], Arr::get($array, 'products.desk'));

        $array = ['products' => ['desk' => ['price' => 100]]];
        $value = Arr::get($array, 'products.desk');
        $this->assertEquals(['price' => 100], $value);

        // Test null array values
        $array = ['foo' => null, 'bar' => ['baz' => null]];
        $this->assertNull(Arr::get($array, 'foo', 'default'));
        $this->assertNull(Arr::get($array, 'bar.baz', 'default'));

        // Test direct ArrayAccess object
        $array = ['products' => ['desk' => ['price' => 100]]];
        $arrayAccessObject = new ArrayObject($array);
        $value = Arr::get($arrayAccessObject, 'products.desk');
        $this->assertEquals(['price' => 100], $value);

        // Test array containing ArrayAccess object
        $arrayAccessChild = new ArrayObject(['products' => ['desk' => ['price' => 100]]]);
        $array = ['child' => $arrayAccessChild];
        $value = Arr::get($array, 'child.products.desk');
        $this->assertEquals(['price' => 100], $value);

        // Test array containing multiple nested ArrayAccess objects
        $arrayAccessChild = new ArrayObject(['products' => ['desk' => ['price' => 100]]]);
        $arrayAccessParent = new ArrayObject(['child' => $arrayAccessChild]);
        $array = ['parent' => $arrayAccessParent];
        $value = Arr::get($array, 'parent.child.products.desk');
        $this->assertEquals(['price' => 100], $value);

        // Test missing ArrayAccess object field
        $arrayAccessChild = new ArrayObject(['products' => ['desk' => ['price' => 100]]]);
        $arrayAccessParent = new ArrayObject(['child' => $arrayAccessChild]);
        $array = ['parent' => $arrayAccessParent];
        $value = Arr::get($array, 'parent.child.desk');
        $this->assertNull($value);

        // Test missing ArrayAccess object field
        $arrayAccessObject = new ArrayObject(['products' => ['desk' => null]]);
        $array = ['parent' => $arrayAccessObject];
        $value = Arr::get($array, 'parent.products.desk.price');
        $this->assertNull($value);

        // Test null ArrayAccess object fields
        $array = new ArrayObject(['foo' => null, 'bar' => new ArrayObject(['baz' => null])]);
        $this->assertNull(Arr::get($array, 'foo', 'default'));
        $this->assertNull(Arr::get($array, 'bar.baz', 'default'));

        // Test null key returns the whole array
        $array = ['foo', 'bar'];
        $this->assertEquals($array, Arr::get($array, null));

        // Test $array not an array
        $this->assertSame('default', Arr::get(null, 'foo', 'default'));
        $this->assertSame('default', Arr::get(false, 'foo', 'default'));

        // Test $array not an array and key is null
        $this->assertSame('default', Arr::get(null, null, 'default'));

        // Test $array is empty and key is null
        $this->assertEmpty(Arr::get([], null));
        $this->assertEmpty(Arr::get([], null, 'default'));

        // Test numeric keys
        $array = [
            'products' => [
                ['name' => 'desk'],
                ['name' => 'chair'],
            ],
        ];
        $this->assertSame('desk', Arr::get($array, 'products.0.name'));
        $this->assertSame('chair', Arr::get($array, 'products.1.name'));

        // Test return default value for non-existing key.
        $array = ['names' => ['developer' => 'taylor']];
        $this->assertSame('dayle', Arr::get($array, 'names.otherDeveloper', 'dayle'));
        $this->assertSame('dayle', Arr::get($array, 'names.otherDeveloper', function () {
            return 'dayle';
        }));
    }
}
