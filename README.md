# 关于本项目

[![GitHub license](https://img.shields.io/github/license/zanemmm/utils.svg)](https://github.com/zanemmm/utils/blob/master/LICENSE) [![Build Status](https://www.travis-ci.org/zanemmm/utils.svg?branch=master)](https://www.travis-ci.org/zanemmm/utils) [![StyleCI](https://github.styleci.io/repos/142729124/shield?branch=master)](https://github.styleci.io/repos/142729124)

开发这个项目的目的是为了将日常开发中经常能用到的方法封装在一起，节省开发时间。同时在开发本项目的过程中熟悉PHP的自带函数的应用，提高自己的水平。

另外在开发过程中还写了一篇关于 [PHP 数组函数的使用技巧](https://www.0php.net/posts/%E5%B7%A7%E7%94%A8-PHP-%E6%95%B0%E7%BB%84%E5%87%BD%E6%95%B0.html) 的博文。

# 项目特性

我觉得最大的特点就是： :sparkles:**链式调用**:sparkles: 。嗯:wink:，其他有待各位发掘。

举个栗子：

``` php
<?php

// 统计数组中出现次数最多的值
$data = ['red', 'green', 'blue', 'red', 'red']

// 原生 PHP
$cv = array_count_values($data);
arsort($cv);
$max = key($cv);
echo $max // red

// Utils 的 Ary 类
echo Ary::new($data)->countValues()->max(); // red

```

是不是方便很多:bangbang:其实项目里很多方法都只是PHP自带函数的简单封装，但是通过链式调用在可读性和可维护性上真的是完爆使用自带函数:laughing:。

# 安装

> composer require zane/utils

# 项目进度

- [x] Ary 数组类 2018.7.29
- [x] Str 字符串类 2018.8.1
- [x] Validator 验证器类 2018.8.3

# 贡献代码

代码风格采用 PSR2 标准

测试覆盖率 >= 90%

欢迎各位小伙伴提 issue 和 pr，立个 :triangular_flag_on_post: 一定要把这个项目坚持下去！

# 感谢

部分函数的实现参考以下项目

[Laravel/Framework](https://github.com/laravel/framework)

[JBZoo/Utils](https://github.com/JBZoo/Utils)

# 开源协议

MIT