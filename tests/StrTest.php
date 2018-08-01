<?php
namespace Zane\Tests;

use PHPUnit\Framework\TestCase;
use Zane\Utils\Ary;
use Zane\Utils\Exceptions\StrEncodingException;
use Zane\Utils\Str;

class StrTest extends TestCase
{
    /**
     * @expectedException \Zane\Utils\Exceptions\StrEncodingException
     */
    public function testNew()
    {
        $gbk = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'gbk.txt');
        $str = Str::convert($gbk, 'GBK');
        $this->assertEquals('PHP 是世界上最好的语言', $str);

        $str = Str::new($gbk, 'GBK');
        $this->assertEquals('PHP 是世界上最好的语言', $str->str());

        // throw StrEncodingException
        Str::new($gbk);
    }

    /**
     * @throws \Zane\Utils\Exceptions\StrEncodingException
     */
    public function testStr()
    {
        $str = Str::new('PHP 是世界上最好的语言');
        $this->assertEquals('PHP 是世界上最好的语言', $str->str());
    }

    /**
     * @expectedException \Zane\Utils\Exceptions\StrEncodingException
     * @throws \Zane\Utils\Exceptions\StrEncodingException
     */
    public function testSet()
    {
        $str = Str::new('PHP 是世界上最好的语言');
        $str->set("你好世界");
        $this->assertEquals('你好世界', $str->str());

        $str2 = Str::new("世界你好");
        $this->assertEquals('世界你好', $str->set($str2)->str());

        $gbk = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'gbk.txt');
        $str2->set($gbk);
    }

    /**
     * @throws \Zane\Utils\Exceptions\StrEncodingException
     */
    public function testToUpperCase()
    {
        $str = Str::new('hello world');
        $this->assertEquals('HELLO WORLD', $str->toUpperCase()->str());
    }

    /**
     * @throws \Zane\Utils\Exceptions\StrEncodingException
     */
    public function testToLowerCase()
    {
        $str = Str::new('HELLO WORLD');
        $this->assertEquals('hello world', $str->toLowerCase()->str());
    }

    /**
     * @throws \Zane\Utils\Exceptions\StrEncodingException
     */
    public function testToTitleCase()
    {
        $str = Str::new('hello world');
        $this->assertEquals('Hello World', $str->toTitleCase()->str());
    }

    /**
     * @throws \Zane\Utils\Exceptions\StrEncodingException
     */
    public function testExplode()
    {
        $str = Str::new('第一|第二|第三');
        $ary = $str->explode('|');
        $this->assertEquals(Ary::new(['第一', '第二', '第三']), $ary);

        $ary = $str->explode('|', 2);
        $this->assertEquals(Ary::new(['第一', '第二|第三']), $ary);

        $ary = $str->explode(Str::new('第二'));
        $this->assertEquals(Ary::new(['第一|', '|第三']), $ary);
    }

    /**
     * @throws \Zane\Utils\Exceptions\StrEncodingException
     */
    public function testSplit()
    {
        $str = Str::new('你好66世界99好你');
        $ary = $str->split('\d{2,}');
        $this->assertEquals(['你好', '世界', '好你'], $ary->val());

        $ary = $str->split('\d{2,}', 2);
        $this->assertEquals(['你好', '世界99好你'], $ary->val());
    }

    /**
     * @throws \Zane\Utils\Exceptions\StrEncodingException
     */
    public function testSubstring()
    {
        $str = Str::new('PHP 是世界上最好的语言');
        $this->assertEquals('世界上最好的语言', $str->substring(5));
    }

    /**
     * @throws \Zane\Utils\Exceptions\StrEncodingException
     */
    public function testSubstringCount()
    {
        $str = Str::new('四十是四十，十四是十四');
        $this->assertEquals(2, $str->substringCount('十四'));

        $needle = Str::new('四十');
        $this->assertEquals(2, $str->substringCount($needle));

        $this->assertEquals(0, $str->substringCount('不存在'));
    }

    /**
     * @throws \Zane\Utils\Exceptions\StrEncodingException
     */
    public function testTruncate()
    {
        $str = Str::new('四十是十四，十四是四十');
        $this->assertEquals('四十是十四', $str->truncate(5));
        $this->assertEquals('四十是十四......', $str->truncate(5, '......'));
        $this->assertEquals($str, $str->truncate(11));
        $this->assertEquals($str, $str->truncate(12, '......'));
    }

    /**
     * @throws \Zane\Utils\Exceptions\StrEncodingException
     */
    public function testPosition()
    {
        $str = Str::new('最好的语言PHP言语的好最PHP');
        $this->assertEquals(5, $str->position('PHP', 0, true, false));
        $this->assertEquals(13, $str->position('PHP', 0, true, true));
        $this->assertEquals(5, $str->position('php', 0, false, false));
        $this->assertEquals(13, $str->position('php', 0, false, true));

        $this->assertFalse($str->position('php', 0, true, false));
        $this->assertEquals(5, $str->position('PHP', 5, true, false));
    }

    /**
     * @throws StrEncodingException
     */
    public function testSearch()
    {
        $str = Str::new('最好的语言PHP言语的好最PHP');
        $this->assertEquals('最好的语言', $str->search('PHP', true, true, false));
        $this->assertEquals('最好的语言', $str->search('php', true, false, false));
        $this->assertEquals('最好的语言PHP言语的好最', $str->search('PHP', true, true, true));
        $this->assertEquals('最好的语言PHP言语的好最', $str->search('php', true, false, true));

        $this->assertEquals('PHP言语的好最PHP', $str->search('PHP', false, true, false));
        $this->assertEquals('PHP言语的好最PHP', $str->search('php', false, false, false));
        $this->assertEquals('PHP', $str->search('PHP', false, true, true));
        $this->assertEquals('PHP', $str->search('php', false, false, true));

        $this->assertFalse($str->search('php', true, true, false));
    }

    /**
     * @throws StrEncodingException
     */
    public function testBefore()
    {
        $str = Str::new('pi@0php.net');
        $this->assertEquals('pi', $str->before('@', false));
        $this->assertEquals('pi@', $str->before('@', true));
        $this->assertEquals(Str::new('pi'), $str->before('@', false));
        $this->assertEquals(Str::new('pi@'), $str->before('@', true));

        $this->assertFalse($str->before('null', false));
        $this->assertFalse($str->before('null', true));
    }

    /**
     * @throws StrEncodingException
     */
    public function testAfter()
    {
        $str = Str::new('pi@0php.net');

        $this->assertEquals('0php.net', $str->after('@', false));
        $this->assertEquals('@0php.net', $str->after('@', true));
        $this->assertEquals(Str::new('0php.net'), $str->after('@', false));
        $this->assertEquals(Str::new('@0php.net'), $str->after('@', true));

        $this->assertFalse($str->after('null', false));
        $this->assertFalse($str->after('null', true));
    }

    /**
     * @throws StrEncodingException
     */
    public function testRepeat()
    {
        $str = Str::new('再来一瓶');

        $this->assertEquals('再来一瓶再来一瓶', $str->repeat(2));
        $this->assertEquals('再来一瓶|再来一瓶', $str->repeat(2, '|'));
        $this->assertEquals('再来一瓶', $str->repeat(1, '|'));
        $this->assertEquals('', $str->repeat(0));
        $this->assertEquals('', $str->repeat(0, '|'));
    }

    /**
     * @throws StrEncodingException
     */
    public function testReplace()
    {
        $str = Str::new('java是世界上最好的语言');

        $this->assertEquals('php是世界上最好的语言', $str->replace('java', 'php'));
        $this->assertEquals('php是世界上最好的语言', $str->replace('JAVA', 'php', false));
    }

    /**
     * @throws StrEncodingException
     */
    public function testToBase64()
    {
        $str = Str::new('你好世界');
        $base64 = base64_encode($str->str());

        $this->assertEquals($base64, $str->toBase64()->str());
    }

    /**
     * @throws StrEncodingException
     */
    public function testToMd5()
    {
        $str = Str::new('你好世界');
        $md5 = md5($str->str());

        $this->assertEquals($md5, $str->toMd5());
    }

    /**
     * @throws StrEncodingException
     */
    public function testSha1()
    {
        $str = Str::new('你好世界');
        $md5 = sha1($str->str());

        $this->assertEquals($md5, $str->toSha1());
    }

    /**
     * @throws StrEncodingException
     */
    public function testPasswordHash()
    {
        $str  = Str::new('你好世界');

        $this->assertTrue(password_verify('你好世界', $str->passwordHash()));
    }

    /**
     * @throws StrEncodingException
     */
    public function testTrim()
    {
        $str  = Str::new(' 你好世界 ');
        $this->assertEquals('你好世界', $str->trim());

        $str  = Str::new('#你好世界#');
        $this->assertEquals('你好世界', $str->trim('#'));
    }

    /**
     * @throws StrEncodingException
     */
    public function testLtrim()
    {
        $str  = Str::new(' 你好世界 ');
        $this->assertEquals('你好世界 ', $str->ltrim());

        $str  = Str::new('#你好世界#');
        $this->assertEquals('你好世界#', $str->ltrim('#'));
    }

    /**
     * @throws StrEncodingException
     */
    public function testRtrim()
    {
        $str  = Str::new(' 你好世界 ');
        $this->assertEquals(' 你好世界', $str->rtrim());

        $str  = Str::new('#你好世界#');
        $this->assertEquals('#你好世界', $str->rtrim('#'));
    }

    /**
     * @throws StrEncodingException
     */
    public function testComp()
    {
        $str1 = Str::new('star utils');
        $str2 = Str::new('star php');

        $this->assertTrue($str1->comp($str2) > 0);
        $this->assertTrue($str1->comp($str2, true, 5) === 0);

        $str3 = Str::new('STAR php');
        $this->assertTrue($str1->comp($str3, false, 5) === 0);
        $this->assertTrue($str2->comp($str3, false) === 0);
    }

    /**
     * @throws StrEncodingException
     */
    public function testNatComp()
    {
        $str1 = Str::new('img2');
        $str2 = Str::new('img12');
        $this->assertTrue($str1->natComp($str2) < 0);

        $str3 = Str::new('IMG13');
        $this->assertTrue($str3->natComp($str2, false) > 0);
    }

    /**
     * @throws StrEncodingException
     */
    public function testEquals()
    {
        $str = Str::new('hello world');
        $this->assertTrue($str->equals('hello world'));
        $this->assertFalse($str->equals('HELLO WORLD'));
    }

    /**
     * @throws StrEncodingException
     */
    public function testReverse()
    {
        $str = Str::new('十四是十四');
        $this->assertEquals('四十是四十', $str->reverse());
    }

    /**
     * @throws StrEncodingException
     */
    public function testToArray()
    {
        $str = Str::new('十四是十四');
        $this->assertEquals(['十', '四', '是', '十', '四'], $str->toArray());
    }

    /**
     * @throws StrEncodingException
     */
    public function testToAry()
    {
        $str = Str::new('十四是十四');
        $ary = Ary::new(['十', '四', '是', '十', '四']);
        $this->assertEquals($ary, $str->toAry());
    }

    /**
     * @throws StrEncodingException
     */
    public function testLen()
    {
        $str = Str::new('十四是十四');
        $this->assertEquals(5, $str->len());
    }

    /**
     * @throws StrEncodingException
     */
    public function testCount()
    {
        $str = Str::new('十四是十四');
        $this->assertEquals(5, $str->count());
        $this->assertEquals(5, count($str));
    }

    /**
     * @throws StrEncodingException
     */
    public function testSetDefault()
    {
        $str = Str::new('十四是十四');
        Str::setDefault(['toMd5RawOutput' => true]);
        $md5 = md5($str->str(), true);
        $this->assertEquals($md5, $str->toMd5());
    }
}
