<?php

namespace Zane\Tests;

use PHPUnit\Framework\TestCase;
use Zane\Utils\Ary;

class AryTest extends TestCase
{
    public function testNew()
    {
        $empty = Ary::new([]);
        $this->assertEmpty($empty->val());

        $array = [1, 2, 3];
        $ary = Ary::new($array);
        $this->assertEquals($array, $ary->val());
    }

    public function testVal()
    {
        $arrayA = [1, 2, 3, '4'];
        $ary = Ary::new($arrayA);
        $this->assertEquals($arrayA, $ary->val());

        $arrayB = [1, 2, 3, 4];
        $ary->val($arrayB);
        $this->assertEquals($arrayB, $ary->val());
    }
}
