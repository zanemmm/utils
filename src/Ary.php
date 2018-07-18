<?php

namespace Zane\Utils;

use Closure;
use TypeError;
use IteratorAggregate;
use ArrayIterator;

class Ary implements IteratorAggregate
{
    protected static $config = [
        'keysSearchValue' => null,
        'keysStrict'      => true,
        'preserveKeys'    => false,
        'toJsonOptions'   => JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT,
        'toJsonDepth'     => 512,
        'fromJsonAssoc'   => false,
        'fromJsonOptions' => 0,
        'fromJsonDepth'   => 512,
        'popGetElement'   => true
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

    public function limit(int $num, bool $preserveKeys = null): self
    {
        if ($num < 0) {
            return static::new([]);
        }

        $val = array_slice($this->val, 0, $num, self::config('preserveKeys', $preserveKeys));

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

    public function merge($array): self
    {
        if (is_array($array)) {
            $val = array_merge($this->val, $array);
        } elseif ($array instanceof static) {
            $val = array_merge($this->val, $array->val());
        } else {
            throw new TypeError(
                "Argument 1 passed to Zane\Utils\Ary::merge() must be an instance of Zane\Utils\Ary or array =>"
                . ' file: ' . __FILE__
                . ' line: ' . __LINE__
            );
        }

        return static::new($val);
    }

    public function map(Closure $closure): self
    {
        $val = array_map($closure, $this->val);

        return static::new($val);
    }

    public function each(Closure $closure, $userData = null): self
    {
        array_walk($this->val, $closure, $userData);

        return $this;
    }

    public function reduce(Closure $closure, $initial = null)
    {
        return array_reduce($this->val, $closure, $initial);
    }

    public function toJson(int $options = null, int $depth = 512): string
    {
        return json_encode(
            $this->val,
            static::config('toJsonOptions', $options),
            static::config('toJsonDepth', $depth)
        );
    }

    public function getIterator()
    {
        return new ArrayIterator($this->val);
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
