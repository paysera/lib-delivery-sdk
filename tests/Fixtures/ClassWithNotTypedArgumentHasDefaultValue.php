<?php

declare(strict_types=1);

namespace Paysera\DeliverySdk\Tests\Fixtures;

class ClassWithNotTypedArgumentHasDefaultValue
{
    public array $array;

    public function __construct($input = ['test'])
    {
        $this->array = $input;
    }
}
