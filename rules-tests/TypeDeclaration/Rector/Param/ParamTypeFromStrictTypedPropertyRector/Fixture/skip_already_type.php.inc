<?php

namespace Rector\Tests\TypeDeclaration\Rector\Param\ParamTypeFromStrictTypedPropertyRector\Fixture;

final class SkipAlreadyType
{
    private int $age;

    public function setAge(string $age)
    {
        $this->age = $age;
    }
}
