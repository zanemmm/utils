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

    public function testValues()
    {
        $array = ['x' => 'a', 'y' => 'b', 'z' => 'c', 'd', 'e'];
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

    public function testFirst()
    {
        $array = [1, 2, 3];
        $ary = Ary::new($array);

        $this->assertEquals($array[0], $ary->first());

        $array = ['a' => 1, 2, 3];
        $ary = Ary::new($array);

        $this->assertEquals($array['a'], $ary->first());
    }

    public function testEnd()
    {
        $array = [1, 2, 3];
        $ary = Ary::new($array);

        $this->assertEquals($array[2], $ary->end());

        $array = [1, 2, 'b' => 3];
        $ary = Ary::new($array);

        $this->assertEquals($array['b'], $ary->end());
    }

    public function testFirstKey()
    {
        $array = [1, 2, 3];
        $ary = Ary::new($array);

        $this->assertEquals(0, $ary->firstKey());

        $array = ['a' => 1, 2, 3];
        $ary = Ary::new($array);

        $this->assertEquals('a', $ary->firstKey());
    }

    public function testEndKey()
    {
        $array = [1, 2, 3];
        $ary = Ary::new($array);

        $this->assertEquals(2, $ary->endKey());

        $array = [1, 2, 'b' => 3];
        $ary = Ary::new($array);

        $this->assertEquals('b', $ary->endKey());
    }

    public function testLimit()
    {
        $array = ['a' => 1, 'b' => 3, 99 => 4, 5, 6];
        $ary = Ary::new($array);

        $this->assertEquals(['a' => 1, 'b' => 3, 4], $ary->limit(3, false)->val());
        $this->assertEquals($array, $ary->limit(100, true)->val());
        $this->assertEquals(['a' => 1, 'b' => 3, 4, 5, 6], $ary->limit(100, false)->val());
        $this->assertEmpty($ary->limit(0)->val());
        $this->assertEmpty($ary->limit(-1)->val());
    }

    public function testTail()
    {
        $array = [99 => 1, 2, 3, 'zane' => 'utils', 'ary' => 'array'];
        $ary = Ary::new($array);

        $this->assertEquals([3, 'zane' => 'utils', 'ary' => 'array'], $ary->tail(3, false)->val());
        $this->assertEquals($array, $ary->tail(100, true)->val());
        $this->assertEquals([1, 2, 3, 'zane' => 'utils', 'ary' => 'array'], $ary->tail(100, false)->val());
        $this->assertEmpty($ary->tail(0)->val());
        $this->assertEmpty($ary->tail(-1)->val());
    }

    public function testSlice()
    {
        $array = ['a' => 1, 'b' => 3, 99 => 4, 5, 6];
        $ary = Ary::new($array);

        $this->assertEquals(array_slice($array, 0, 3), $ary->slice(0, 3)->val());
        $this->assertEquals(array_slice($array, -1, 3), $ary->slice(-1, 3)->val());
        $this->assertEquals(array_slice($array, -1, 100), $ary->slice(-1, 100)->val());
        $this->assertEquals(array_slice($array, -1, 0), $ary->slice(-1, 0)->val());
    }

    public function testChunk()
    {
        $array = [1, 2, 3, 4, 5, 6];
        $ary = Ary::new($array);

        $ary = $ary->chunk(1, false);
        $chunks = array_chunk($array, 1, false);
        foreach ($chunks as $key => $chunk) {
            $this->assertEquals($chunk, $ary[$key]->val());
        }
    }

    public function testColumn()
    {
        $array = [
            ['id' => 1, 'name' => 'a', 'val' => 'x'],
            ['id' => 2, 'name' => 'a', 'val' => 'y'],
            ['id' => 3, 'name' => 'a', 'val' => 'z']
        ];
        $ary = Ary::new($array);

        $col = array_column($array, 'name', 'id');
        $val = $ary->column('name', 'id')->val();
        $this->assertEquals($col, $val);

        $AryArray = [
            Ary::new(['id' => 1, 'name' => 'a', 'val' => 'x']),
            Ary::new(['id' => 2, 'name' => 'b', 'val' => 'y']),
            Ary::new(['id' => 3, 'name' => 'c', 'val' => 'z'])
        ];
        $ary = Ary::new($AryArray);

        $col = array_column($AryArray, 'val');
        $val = $ary->column('val')->val();
        $this->assertEquals($col, $val);

        $col = array_column($AryArray, 'val', 'name');
        $val = $ary->column('val', 'name')->val();
        $this->assertEquals($col, $val);
    }

    public function testCountValues()
    {
        $array = [1, 'hello', 1, 'world', 'hello'];
        $ary = Ary::new($array);

        $this->assertEquals(array_count_values($array), $ary->countValues()->val());
    }

    public function testFlip()
    {
        $array = ['oranges', 'apples', 'pears'];
        $ary = Ary::new($array);

        $this->assertEquals(array_flip($array), $ary->flip()->val());
    }

    public function testExist()
    {
        $array = ['1.10', 12.4, 1.13];
        $ary = Ary::new($array);

        $this->assertEquals(in_array('12.4', $array), $ary->exist('12.4', false));
        $this->assertEquals(in_array('12.4', $array, true), $ary->exist('12.4', true));
    }

    public function testKeyExist()
    {
        $array = ['first' => null, 'second' => 2, 3];
        $ary = Ary::new($array);

        $this->assertEquals(array_key_exists('first', $array), $ary->keyExist('first'));
        // 与 isset 的区别
        $this->assertEquals(isset($array['first']), !$ary->keyExist('first'));
        // 数字字符串的键名默认转为数字索引
        $this->assertEquals(array_key_exists('0', $array), $ary->keyExist('0'));
    }

    public function testIsSet()
    {
        $array = ['first' => null, 'second' => 2, 3];
        $ary = Ary::new($array);

        // 数组成员的值为 null 会返回 false
        $this->assertEquals(false, $ary->isSet('first'));
        $this->assertEquals(isset($array['second']), $ary->isSet('second'));
        // 数字字符串的键名默认转为数字索引
        $this->assertEquals(isset($array['0']), $ary->isSet('0'));
    }

    public function testSort()
    {
        $array = ['l' => 'lemon', 'o' => 'orange', 'b' => 'banana', 'a' => 'apple'];
        $ary = Ary::new($array);

        asort($array);
        $ary->sort(true, true);
        $this->assertEquals($array, $ary->val());

        arsort($array);
        $ary->sort(false, true);
        $this->assertEquals($array, $ary->val());

        sort($array);
        $ary->sort(true, false);
        $this->assertEquals($array, $ary->val());

        rsort($array);
        $ary->sort(false, false);
        $this->assertEquals($array, $ary->val());
    }

    public function testUserSort()
    {
        $fn = function ($x, $y) {
            return strlen($x) <=> strlen($y);
        };
        $array = ['longLong', 'long', 'float', 'int'];
        $ary   = Ary::new($array);

        uasort($array, $fn);
        $ary->userSort($fn, true);
        $this->assertEquals($array, $ary->val());

        usort($array, $fn);
        $ary->userSort($fn, false);
        $this->assertEquals($array, $ary->val());
    }

    public function testNatSort()
    {
        $array = ['IMG0.png', 'img12.png', 'img10.png', 'img2.png', 'img1.png', 'IMG3.png'];
        $ary = Ary::new($array);

        natsort($array);
        $ary->natSort(true);
        $this->assertEquals($array, $ary->val());
        
        natcasesort($array);
        $ary->natSort(false);
        $this->assertEquals($array, $ary->val());
    }

    public function testKeySort()
    {
        $array = ['l' => 'lemon', 'o' => 'orange', 'b' => 'banana', 'a' => 'apple'];
        $ary = Ary::new($array);

        ksort($array);
        $ary->keySort(true);
        $this->assertEquals($array, $ary->val());

        krsort($array);
        $ary->keySort(false);
        $this->assertEquals($array, $ary->val());
    }

    public function testUserKeySort()
    {
        $fn = function ($x, $y) {
            return strlen($x) <=> strlen($y);
        };
        $array = ['longLong' => 0, 'long' => 1, 'float' => 2, 'int' => 3];
        $ary = Ary::new($array);

        uksort($array, $fn);
        $ary->userKeySort($fn);
        $this->assertEquals($array, $ary->val());
    }

    public function testShuffle()
    {
        $ary = Ary::new(range(1, 20));

        $this->assertNotEquals(range(1, 20), $ary->shuffle()->val());
    }

    public function testUnique()
    {
        $array = ['a' => 'green', 'red', 'b' => 'green', 'blue', 'red'];
        $ary = Ary::new($array);

        $this->assertEquals(array_unique($array), $ary->unique()->val());
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

        $a = array_pop($array);
        $b = $ary->pop();

        $this->assertEquals($a, $b);
        $this->assertEquals($array, $ary->val());
    }

    public function testUnShift()
    {
        $array = ['orange', 'banana'];
        $ary = Ary::new($array);

        array_unshift($array, 'apple', 'raspberry');
        $ary->unShift('apple', 'raspberry');
        $this->assertEquals($array, $ary->val());

        return [$array, $ary];
    }

    /**
     * @param array $unShiftResult
     * @depends testUnShift
     */
    public function testShift($unShiftResult)
    {
        [$array, $ary] = $unShiftResult;

        $a = array_shift($array);
        $b = $ary->shift();
        $this->assertEquals($a, $b);
        $this->assertEquals($array, $ary->val());
    }

    public function testAppend()
    {
        $a = [1, 2, 3, 4];
        $b = ['a', 'b', 'c', 'd'];
        $ab = array_merge($a, $b);

        $aryA = Ary::new($a);
        $aryB = Ary::new($b);

        // append Ary
        $aryAB = $aryA->append($aryB);
        $this->assertEquals($ab, $aryAB->val());

        // preserve values
        $a = [1, 2, 3];
        $b = [4, 5, 6, 7, 99 => 100];
        $aryA->val($a);
        $aryB->val($b);

        $this->assertEquals([1, 2, 3, 7, 99 => 100], $aryA->append($aryB, true)->val());
    }

    public function testSearch()
    {
        $array = ['blue', 'red', 'green', 'red', '1', 1];
        $ary = Ary::new($array);

        $a = array_search('red', $array, true);
        $b = $ary->search('red', true);
        $this->assertEquals($a, $b);

        // strict 为 false 时 '1' 与 1 相等
        $a = array_search(1, $array, false);
        $b = $ary->search(1, false);
        $this->assertEquals($a, $b);
    }

    /**
     * @depends testKeys
     * @depends testSearch
     * @depends testLimit
     */
    public function testBefore()
    {
        $ary = Ary::new(['pi', '@', '0php.net', '233', 233, 233, 'hello' => 'world']);

        $this->assertEquals(['pi'], $ary->before('@', false, false)->val());
        $this->assertEquals(['pi', '@'], $ary->before('@', true, false)->val());
        // 确认采用了严格比较
        $this->assertEquals($ary->limit(3), $ary->before('233', false, false));
        // 确认只包含第一个指定值之前的元素
        $this->assertEquals($ary->limit(4), $ary->before(233, false));
        // 确认键名保护
        $this->assertEquals($ary, $ary->before('world', true, true));
        // 确认字符串键名始终不变
        $a = ['pi', '@', '0php.net', '233', 233, 233, 'world'];
        $this->assertNotEquals($a, $ary->before('world', true, false)->val());
        // 确认值不存在返回空
        $this->assertEmpty($ary->before('hello')->val());
    }

    /**
     * @depends testKeys
     * @depends testSearch
     * @depends testTail
     */
    public function testAfter()
    {
        $ary = Ary::new(['hello' => 233, 233, '233', '0php.net', '@', 'pi']);

        $this->assertEquals(['pi'], $ary->after('@', false, false)->val());
        $this->assertEquals(['@', 'pi'], $ary->after('@', true, false)->val());
        // 确认采用了严格比较
        $this->assertEquals($ary->tail(3, false), $ary->after('233', false, false));
        // 确认只包含第一个指定值之前的元素
        $this->assertEquals($ary->tail(5, false), $ary->after(233, false, false));
        // 确认键名保护
        $this->assertEquals($ary->tail(5, true), $ary->after(233, false, true));
        // 确认字符串键名始终不变
        $a = [233, 233, '233', '0php.net', '@', 'pi'];
        $this->assertNotEquals($a, $ary->after(233, true, false));
        // 确认值不存在返回空
        $this->assertEmpty($ary->after('zane')->val());
    }

    public function testReplace()
    {
        $base = ['orange', 'banana', 'apple', 'raspberry'];
        $replacements = [0 => 'pineapple', 4 => 'cherry'];
        $replacements2 = [0 => 'grape'];
        $aryBase = Ary::new($base);
        $aryReplacements = Ary::new($replacements);
        $aryReplacements2 = Ary::new($replacements2);

        $basket = array_replace($base, $replacements, $replacements2);
        $aryBasket = $aryBase->replace($aryReplacements, $aryReplacements2);
        $this->assertEquals($basket, $aryBasket->val());
    }

    public function testClean()
    {
        $a = [0 => 'foo', 1 => false, 2 => -1, 3 => null, 4 => ''];
        $ary = Ary::new($a);

        $this->assertEquals(['foo', 2 => -1], $ary->clean()->val());
    }

    public function testJoin()
    {
        $a = ['hello', 'world', '!'];
        $aryA = Ary::new($a);

        $this->assertEquals(implode(' ', $a), $aryA->join(' '));

        $b = ['I', 'am', 'utils', $aryA];
        $aryB = Ary::new($b);
        $this->assertEquals('I am utils hello world !', $aryB->join(' '));
    }

    public function testIteratorAggregate()
    {
        $a = ['a' => 0, 'b' => 1, 'c' => 2, 3, 4, 5];

        $ary = Ary::new($a);

        foreach ($ary as $key => $val) {
            $this->assertEquals($a[$key], $val);
        }
    }
}
