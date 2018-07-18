<?php

namespace Zane\Tests;

use PHPUnit\Framework\TestCase;
use Zane\Utils\Ary;
use TypeError;

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

    public function testValues()
    {
        $array = ['a', 'b', 'c', 'd'];
        $ary = Ary::new($array);
        $this->assertEquals(array_values($array), $ary->values()->val());
    }

    public function testKeys()
    {
        $array = ['a' => 0, 'b' => 0, 'c' => '1', 'd' => 1, 0, 1, 2, 'e' => null, 'f' => []];
        $ary = Ary::new($array);

        $this->assertEquals(array_keys($array), $ary->keys()->val());

        // 筛选数组值
        $this->assertEquals(array_keys($array, 1, true), $ary->keys(1, true)->val());

        $this->assertEquals(array_keys($array, null), $ary->keys(null, false)->val());
    }

    public function testReverse()
    {
        $array = ['a' => 0, 'b' => 0, 'c' => '1', 'd' => 1, 0, 1, 2, 'e' => null, 'f' => []];
        $ary = Ary::new($array);

        $this->assertEquals(array_reverse($array), $ary->reverse()->val());
    }

    public function testPush()
    {
        $array = [];
        $ary = Ary::new($array);

        $ary->push(1, 2, 3, '4');
        array_push($array, 1, 2, 3, '4');

        $this->assertEquals($array, $ary->val());

        return [$array, $ary];
    }

    /**
     * @param array $pushResult
     * @depends testPush
     */
    public function testPop($pushResult)
    {
        [$array, $ary] = $pushResult;

        $a = $ary->pop();
        $b = array_pop($array);

        $this->assertEquals($a, $b);
        $this->assertEquals($array, $ary->val());
    }

    /**
     * @expectedException TypeError
     * @throws TypeError
     */
    public function testMerge()
    {
        $a = [1, 2, 3, 4];
        $b = ['a', 'b', 'c', 'd'];
        $ab = array_merge($a, $b);

        $aryA = Ary::new($a);
        $aryB = Ary::new($b);

        // merge Ary
        $aryAB = $aryA->merge($aryB);
        $this->assertEquals($ab, $aryAB->val());

        // merge array
        $aryAB = $aryA->merge($b);
        $this->assertEquals($ab, $aryAB->val());

        // merge error type
        $aryA->merge("test");
    }
}
