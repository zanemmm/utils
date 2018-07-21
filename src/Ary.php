<?php

namespace Zane\Utils;

use TypeError;
use IteratorAggregate;
use ArrayAccess;
use ArrayIterator;

class Ary implements IteratorAggregate, ArrayAccess
{
    protected static $config = [
        'keysSearchValue'    => null,
        'keysStrict'         => true,
        'limitPreserveKeys'  => false,
        'slicePreserveKeys'  => false,
        'appendPreserveKeys' => false,
        'joinGlue'           => '',
        'filterFlag'         => 0,
        'toJsonOptions'      => JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT,
        'toJsonDepth'        => 512,
        'fromJsonAssoc'      => false,
        'fromJsonOptions'    => 0,
        'fromJsonDepth'      => 512,
        'popGetElement'      => true
    ];

    protected $val;

    public function __construct(array $array = [])
    {
        $this->val = $array;
    }

    public function val(array $array = null)
    {
        if (is_null($array)) {
            return $this->val;
        }

        $this->val = $array;

        return $this;
    }

    public function values(): self
    {
        return static::new(array_values($this->val));
    }

    public function keys($searchValue = null, $strict = null): self
    {
        // 实参个数为零时直接调用 array_keys 函数
        // 若此时调用 array_keys 函数时带入 $searchValue 和 $strict 参数会导致 array_keys 返回 数组值为 null 的键名, 与预期结果不符
        $args = func_num_args();
        if ($args === 0) {
            return static::new(array_keys($this->val));
        }

        return static::new(
            array_keys(
                $this->val,
                static::config('keysSearchValue', $searchValue),
                static::config('keysStrict', $strict)
            )
        );
    }

    public function first()
    {
        return reset($this->val);
    }

    public function end()
    {
        return end($this->val);
    }

    public function limit(int $len, bool $preserveKeys = null): self
    {
        if ($len < 0) {
            return static::new([]);
        }

        $val = array_slice($this->val, 0, $len, static::config('limitPreserveKeys', $preserveKeys));

        return static::new($val);
    }

    public function slice(int $offset, int $len, bool $preserveKeys = null): self
    {
        $val = array_slice($this->val, $offset, $len, static::config('slicePreserveKeys', $preserveKeys));

        return static::new($val);
    }

    public function reverse(): self
    {
        $val = array_reverse($this->val);

        return static::new($val);
    }

    public function push(...$element): self
    {
        array_push($this->val, ...$element);

        return $this;
    }

    public function pop(bool $getElement = null)
    {
        $element = array_pop($this->val);
        if (static::config('popGetElement', $getElement)) {
            return $element;
        }

        return $this;
    }

    public function append($array, bool $preserveKeys = null): self
    {
        if ($array instanceof static) {
            $array = $array->val();
        } elseif (!is_array($array)) {
            throw new TypeError(
                "Argument 1 passed to Zane\Utils\Ary::merge() must be an instance of Zane\Utils\Ary or array =>"
                . ' file: ' . __FILE__
                . ' line: ' . __LINE__
            );
        }

        $preserveKeys = static::config('appendPreserveKeys', $preserveKeys);
        if ($preserveKeys) {
            $val = $this->val + $array;
        } else {
            $val = array_merge($this->val, $array);
        }

        return static::new($val);
    }

    public function clean(): self
    {
        $this->val = array_filter($this->val);

        return $this;
    }

    public function join(string $glue = null): string
    {
        // 当 val 数组中存在 Ary 实例时，其魔术方法 __toString 会同样调用 join 方法，并以 $config 中 joinGlue 的值为 $glue
        // 所以调用 join 方法时需要将 $config 中 joinGlue 的值设置为当前实参 $glue， 并在调用结束后恢复原值
        $oldGlue = static::config('joinGlue');
        static::setConfig(['joinGlue' => $glue]);
        $str = implode($glue, $this->val);
        static::setConfig(['joinGlue' => $oldGlue]);

        return $str;
    }

    public function each(callable $fn, $userData = null): self
    {
        array_walk($this->val, $fn, $userData);

        return $this;
    }

    public function map(callable $fn): self
    {
        $val = array_map($fn, $this->val);

        return static::new($val);
    }

    public function filter(callable $fn, int $flag = null): self
    {
        $val = array_filter($this->val, $fn, static::config('filterFlag', $flag));

        return static::new($val);
    }

    public function reduce(callable $fn, $initial = null)
    {
        return array_reduce($this->val, $fn, $initial);
    }

    public function toJson(int $options = null, int $depth = 512): string
    {
        return json_encode(
            $this->val,
            static::config('toJsonOptions', $options),
            static::config('toJsonDepth', $depth)
        );
    }

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->val);
    }

    public function offsetExists($offset)
    {
        return isset($this->val[$offset]);
    }

    public function offsetGet($offset)
    {
        return isset($this->val[$offset]) ? $this->val[$offset] : null;
    }

    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->val[] = $value;
        } else {
            $this->val[$offset] = $value;
        }
    }

    public function offsetUnset($offset)
    {
        unset($this->val[$offset]);
    }

    public function __toString(): string
    {
        return $this->join(static::config('joinGlue'));
    }

    public static function new(array $array = []): self
    {
        return new static($array);
    }

    public static function setConfig(array $config): void
    {
        foreach ($config as $key => $val) {
            static::$config[$key] = $val;
        }
    }

    public static function fromJson(string $json, bool $assoc = null, int $depth = null, int $options = null): self
    {
        return static::new(
            json_decode(
                $json,
                static::config('fromJsonAssoc', $assoc),
                static::config('fromJsonDepth', $depth),
                static::config('fromJsonDepth', $options)
            )
        );
    }

    public function combine(self $key, self $val): self
    {
        return static::new(
            array_combine($key->val(), $val->val())
        );
    }

    protected static function config(string $key, $val = null)
    {
        if (is_null($val)) {
            return static::$config[$key] ?? null;
        }

        return $val;
    }
}
