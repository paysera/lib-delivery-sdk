<?php

declare(strict_types=1);

namespace Paysera\DeliverySdk\Tests\Fixtures;

class ClassWithNotTypedArgument
{
    public array $array;

    public function __construct($input)
    {
        $this->array = $input;
    }
}
