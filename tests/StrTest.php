<?php
namespace Zane\Tests;

use PHPUnit\Framework\TestCase;
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
        $str = Str::new($gbk);
    }
}
