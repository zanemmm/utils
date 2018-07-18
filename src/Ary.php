<?php

namespace Zane\Utils;

use Closure;

class Ary
{
    protected static $keysSearchValue = null;

    protected static $keyStrict = true;

    protected static $eachUserData = null;

    protected static $toJsonOptions = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT;

    protected static $toJsonDepth = 512;

    protected static $fromJsonAssoc = false;

    protected static $fromJsonOptions = 0;

    protected static $fromJsonDepth = 512;

    protected static $popGetElement = true;

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
        return new static(array_values($this->val));
    }

    public function keys($searchValue, $strict): self
    {
        return static::new(
            array_keys(
                $this->val,
                static::config($searchValue, static::$keysSearchValue),
                static::config($strict, static::$keyStrict)
            )
        );
    }

    public function reverse()
    {
        array_reverse($this->val);

        return $this;
    }

    public function push($element): self
    {
        array_push($this->val, $element);

        return $this;
    }

    public function pop(bool $getElement = null)
    {
        $element = array_pop($this->val);
        if (static::config($getElement, static::$popGetElement)) {
            return $element;
        }

        return $this;
    }

    public function merge($array): self
    {
        if (is_array($array)) {
            array_merge($this->val, $array);
        } elseif ($array instanceof static) {
            array_merge($this->val, $array->val());
        }

        return $this;
    }

    public function map(Closure $closure): self
    {
        $this->val = array_map($closure, $this->val);

        return $this;
    }

    public function each(Closure $closure, $userData = null): self
    {
        array_walk($this->val, $closure, static::config($userData, static::$eachUserData));

        return $this;
    }

    public function reduce(Closure $closure, $initial)
    {
        return array_reduce($this->val, $closure, $initial);
    }

    public function toJson(int $options = null, int $depth = 512): string
    {
        return json_encode(
            $this->val,
            static::config($options, static::$toJsonOptions),
            static::config($depth, static::$toJsonDepth)
        );
    }

    public static function new(array $array = []): self
    {
        return new static($array);
    }

    public static function fromJson(string $json, bool $assoc = null, int $depth = null, int $options = null): self
    {
        return new static(
            json_decode(
                $json,
                static::config($assoc, static::$fromJsonAssoc),
                static::config($depth, static::$fromJsonDepth),
                static::config($options, static::$fromJsonDepth)
            )
        );
    }

    public function combine(self $aryA, self $aryB): self
    {
        return new static(
            array_combine($aryA->val(), $aryB->val())
        );
    }

    protected static function config($val, $default)
    {
        if (is_null($val)) {
            return $default;
        }

        return $val;
    }
}
