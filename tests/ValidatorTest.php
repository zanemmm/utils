<?php

namespace Zane\Tests;

use PHPUnit\Framework\TestCase;
use Zane\Utils\Validator;

class ValidatorTest extends TestCase
{
    public function testAccepted()
    {
        $this->assertTrue(Validator::accepted('yes'));
        $this->assertTrue(Validator::accepted('Yes'));
        $this->assertTrue(Validator::accepted('yEs'));
        $this->assertTrue(Validator::accepted('true'));
        $this->assertTrue(Validator::accepted('on'));
        $this->assertTrue(Validator::accepted('1'));
        $this->assertTrue(Validator::accepted(' yes '));
        $this->assertFalse(Validator::accepted(' y e s '));
        $this->assertFalse(Validator::accepted('off'));
        $this->assertFalse(Validator::accepted('0'));
        $this->assertFalse(Validator::accepted('other'));
    }

    public function testBoolean()
    {
        $this->assertTrue(Validator::boolean('yes'));
        $this->assertTrue(Validator::boolean('Yes'));
        $this->assertTrue(Validator::boolean('yEs'));
        $this->assertTrue(Validator::boolean('true'));
        $this->assertTrue(Validator::boolean('on'));
        $this->assertTrue(Validator::boolean('1'));
        $this->assertTrue(Validator::boolean(' yes '));
        $this->assertTrue(Validator::boolean('off'));
        $this->assertTrue(Validator::boolean('no'));
        $this->assertTrue(Validator::boolean('false'));
        $this->assertTrue(Validator::boolean('0'));
        $this->assertFalse(Validator::boolean(' y e s '));
        $this->assertFalse(Validator::boolean('other'));
    }

    public function testAlpha()
    {
        $this->assertTrue(Validator::alpha('test'));
        $this->assertTrue(Validator::alpha('TEST'));
        $this->assertFalse(Validator::alpha(' test '));
        $this->assertFalse(Validator::alpha('test1'));
        $this->assertFalse(Validator::alpha('测试test'));
        $this->assertFalse(Validator::alpha('\test\\'));
    }

    public function testAlphaNum()
    {
        $this->assertTrue(Validator::alphaNum('test'));
        $this->assertTrue(Validator::alphaNum('TEST'));
        $this->assertTrue(Validator::alphaNum('test1'));
        $this->assertTrue(Validator::alphaNum('1test1'));
        $this->assertFalse(Validator::alphaNum(' test '));
        $this->assertFalse(Validator::alphaNum('测试test'));
        $this->assertFalse(Validator::alphaNum('\test\\'));
    }

    public function testNum()
    {
        $this->assertTrue(Validator::num('123'));
        $this->assertTrue(Validator::num('01239716526350713274912465110000'));
        $this->assertFalse(Validator::num('9527.0'));
        $this->assertFalse(Validator::num('-12'));
        $this->assertFalse(Validator::num(' 12 '));
        $this->assertFalse(Validator::num('1ABC'));

        $this->assertTrue(Validator::num('123', 3));
        $this->assertTrue(Validator::num('0123', 4));
        $this->assertFalse(Validator::num('1234', 3));
    }

    public function testNumeric()
    {
        $this->assertTrue(Validator::numeric('123'));
        $this->assertTrue(Validator::numeric('-123'));
        $this->assertTrue(Validator::numeric('00000'));
        $this->assertTrue(Validator::numeric('999999999999999999999999999999999999'));
        $this->assertTrue(Validator::numeric('09527.0'));
        $this->assertFalse(Validator::numeric('1.0.2.3.4'));
        $this->assertFalse(Validator::numeric('1ABC'));
        $this->assertFalse(Validator::numeric(' 123 '));
    }

    public function testInt()
    {
        $this->assertTrue(Validator::int('123'));
        $this->assertTrue(Validator::int('-123'));
        $this->assertTrue(Validator::int(' 123 '));
        $this->assertFalse(Validator::int('0123'));
        $this->assertFalse(Validator::int('123-'));
        $this->assertFalse(Validator::int('123.1'));
        // 整数溢出
        $this->assertFalse(Validator::int('9223372036854775808'));
        $this->assertFalse(Validator::int('other'));
    }

    /**
     * @depends testInt
     */
    public function testIntMax()
    {
        $this->assertTrue(Validator::intMax('123', 124));
        $this->assertTrue(Validator::intMax('123', 123));
        $this->assertTrue(Validator::intMax('-123', 123));
        $this->assertFalse(Validator::intMax('123', -123));
        $this->assertFalse(Validator::intMax('123', 122));
    }

    /**
     * @depends testInt
     */
    public function testIntMin()
    {
        $this->assertTrue(Validator::intMin('124', 123));
        $this->assertTrue(Validator::intMin('123', 123));
        $this->assertFalse(Validator::intMin('122', 123));
        $this->assertFalse(Validator::intMin('-122', 123));
    }

    /**
     * @depends testIntMax
     * @depends testIntMin
     */
    public function testIntBetween()
    {
        $this->assertTrue(Validator::intBetween('123', 123, 123));
        $this->assertTrue(Validator::intBetween('123', 122, 124));
        $this->assertFalse(Validator::intBetween('123', 124, 122));
        $this->assertFalse(Validator::intBetween('256', 124, 255));
        $this->assertFalse(Validator::intBetween('123', 124, 255));
    }

    public function testFloat()
    {
        $this->assertTrue(Validator::float('123'));
        $this->assertTrue(Validator::float('-123'));
        $this->assertTrue(Validator::float(' 123 '));
        $this->assertTrue(Validator::float('123.0'));
        $this->assertTrue(Validator::float('0123'));
        $this->assertFalse(Validator::float('123-'));
        $this->assertFalse(Validator::float('other'));
    }

    /**
     * @depends testFloat
     */
    public function testFloatMax()
    {
        $this->assertTrue(Validator::floatMax('123.5', 123.5));
        $this->assertTrue(Validator::floatMax('123', 123));
        $this->assertFalse(Validator::floatMax('124', 123.9));
    }

    /**
     * @depends testFloat
     */
    public function testFloatMin()
    {
        $this->assertTrue(Validator::floatMin('123.5', 123.5));
        $this->assertTrue(Validator::floatMin('123', 123));
        $this->assertFalse(Validator::floatMin('122', 123.9));
    }

    /**
     * @depends testFloatMax
     * @depends testFloatMin
     */
    public function testFloatBetween()
    {
        $this->assertTrue(Validator::floatBetween('123', 123.0, 123.0));
        $this->assertTrue(Validator::floatBetween('123.0', 122.9, 123.1));
        $this->assertFalse(Validator::floatBetween('123', 123, 122));
        $this->assertFalse(Validator::floatBetween('255.1', 124, 255));
        $this->assertFalse(Validator::intBetween('123.9', 124, 255));
    }

    public function testJson()
    {
        $json = <<<'EOT'
{
    "name": "zane",
    "email": "pi@0php.net"
}
EOT;
        $this->assertTrue(Validator::json($json));
        $this->assertFalse(Validator::json('other'));
    }

    public function testIp()
    {
        $this->assertTrue(Validator::ip('127.0.0.1'));
        $this->assertTrue(Validator::ip('192.168.1.1'));
        $this->assertTrue(Validator::ip('1030::C9B4:FF12:48AA:1A2B'));
        $this->assertTrue(Validator::ip('2001:DB8:2de::e13'));
        $this->assertFalse(Validator::ip('192.168.1.256'));
        $this->assertFalse(Validator::ip('G000:0:0:0:0:0:0:1'));
    }

    public function testIpv4()
    {
        $this->assertTrue(Validator::ipv4('127.0.0.1'));
        $this->assertTrue(Validator::ipv4('192.168.1.1'));
        $this->assertFalse(Validator::ipv4('1030::C9B4:FF12:48AA:1A2B'));
        $this->assertFalse(Validator::ipv4('2001:DB8:2de::e13'));
    }

    public function testIpv6()
    {
        $this->assertFalse(Validator::ipv6('127.0.0.1'));
        $this->assertFalse(Validator::ipv6('192.168.1.1'));
        $this->assertTrue(Validator::ipv6('1030::C9B4:FF12:48AA:1A2B'));
        $this->assertTrue(Validator::ipv6('2001:DB8:2de::e13'));
    }

    public function testDomain()
    {
        $this->assertTrue(Validator::domain('www.bai-du.com'));
        $this->assertTrue(Validator::domain('google.com'));
        $this->assertTrue(Validator::domain('google.com.cn'));
        $this->assertTrue(Validator::domain('test.google.com.cn'));
        $this->assertTrue(Validator::domain('test.test.google.com.cn'));
        $this->assertFalse(Validator::domain('我爱你.中国'));
        $this->assertFalse(Validator::domain('.google.com.cn'));
        $this->assertFalse(Validator::domain('google.com.cn.'));
        $this->assertFalse(Validator::domain('-google.com'));
        $this->assertFalse(Validator::domain('google-.com'));
        $this->assertFalse(Validator::domain('www.-google.com'));
        $this->assertFalse(Validator::domain('google.com.cn1'));
        $this->assertFalse(Validator::domain('google.com.cn.1'));
        $this->assertFalse(Validator::domain('127.0.0.1'));
    }

    /**
     * @depends testDomain
     */
    public function testActiveDomain()
    {
        $this->assertTrue(Validator::activeDomain('google.com'));
        $this->assertFalse(Validator::activeDomain('IGuessNoOneRegisterThisDomain.com'));
        $this->assertFalse(Validator::activeDomain('127.0.0.1'));
    }

    public function testUrl()
    {
        $this->assertTrue(Validator::url('http://www.google.com'));
        $this->assertTrue(Validator::url('ftp://www.google.com'));
        $this->assertTrue(Validator::url('http://www.google.com/'));
        $this->assertTrue(Validator::url('http://www.google.com/index.php'));
        $this->assertTrue(Validator::url('http://www.google.com/index.php?hello=world'));
        $this->assertTrue(Validator::url('http://www.google.com/index.php?hello=world#x'));
        $this->assertFalse(Validator::url('www.google.com'));
        $this->assertFalse(Validator::url('www.google.com/index.php'));

        $this->assertTrue(Validator::url('http://www.google.com', 'http'));
        $this->assertTrue(Validator::url('https://www.google.com', ['http', 'https']));
        $this->assertTrue(Validator::url('http://www.google.com/index.php?hello=world#x', ['http', 'https']));
        $this->assertTrue(Validator::url('ftp://www.google.com', 'ftp'));
        $this->assertFalse(Validator::url('https://www.google.com', 'http'));
    }

    public function testEmail()
    {
        $this->assertTrue(Validator::email('pi@0php.net'));
        $this->assertFalse(Validator::email('php.net'));
    }

    public function testPhone()
    {
        $this->assertTrue(Validator::phone('13012345678'));
        $this->assertFalse(Validator::phone('1301234567'));
        $this->assertFalse(Validator::phone('130123456789'));
        $this->assertFalse(Validator::phone('16012345678'));
        $this->assertFalse(Validator::phone('03012345678'));
        $this->assertFalse(Validator::phone('19012345678'));
    }

    public function testIDCard()
    {
        $this->assertTrue(Validator::IDCard('110101200001011953'));
        $this->assertTrue(Validator::IDCard('11010120000101103X'));
        $this->assertFalse(Validator::IDCard('110101200001011952'));
        $this->assertFalse(Validator::IDCard('11010120000101195'));
        $this->assertFalse(Validator::IDCard('X11010120000101195'));
    }

    /**
     * @depends testIDCard
     */
    public function testIDCardMaxAge()
    {
        $this->assertTrue(Validator::IDCardMaxAge('110101200001011953', 999));
        $this->assertFalse(Validator::IDCardMaxAge('110101200001011954', 999));
        $this->assertFalse(Validator::IDCardMaxAge('11010120000101195', 999));
        $this->assertFalse(Validator::IDCardMaxAge('110101200001011953', 17));
    }

    /**
     * @depends testIDCard
     */
    public function testIDCardMinAge()
    {
        $this->assertTrue(Validator::IDCardMinAge('110101200001011953', 17));
        $this->assertFalse(Validator::IDCardMinAge('110101200001011954', 17));
        $this->assertFalse(Validator::IDCardMinAge('11010120000101195', 17));
        $this->assertFalse(Validator::IDCardMinAge('110101200001011953', 999));
    }

    public function testIDCardBetween()
    {
        $this->assertTrue(Validator::IDCardBetween('110101200001011953', 17, 999));
        $this->assertFalse(Validator::IDCardBetween('110101200001011954', 17, 999));
        $this->assertFalse(Validator::IDCardBetween('11010120000101195', 17, 999));
        $this->assertFalse(Validator::IDCardBetween('11010120000101195', 9, 10));
        $this->assertFalse(Validator::IDCardBetween('11010120000101195', 99, 999));
        $this->assertFalse(Validator::IDCardBetween('11010120000101195', 999, 17));
    }

    /**
     * @expectedException \Zane\Utils\Exceptions\ValidatorNotFoundException
     */
    public function testSet()
    {
        $isA = function ($val) {
            if ($val === 'A') {
                return true;
            }

            return false;
        };

        Validator::set('isA', $isA);
        $this->assertTrue(Validator::isA('A'));
        $this->assertFalse(Validator::isA('B'));
        // throw ValidatorNotFoundException
        Validator::isB('B');
    }

    /**
     * @expectedException \Zane\Utils\Exceptions\ValidatorNotFoundException
     */
    public function testGet()
    {
        $isA = Validator::get('isA');
        $this->assertTrue($isA('A'));
        $this->assertFalse($isA('B'));

        $int = Validator::get('int');
        $this->assertTrue($int('123'));
        $this->assertFalse($int('ABC'));

        try {
            Validator::get('isB');
        } catch (\Exception $exception) {
            var_dump($exception->getMessage());
        }

        // throw ValidatorNotFoundException
        Validator::get('isB');
    }
}
