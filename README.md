# Istyle\LaravelDualCache

[![Build Status](http://img.shields.io/travis/istyle-inc/laravel-dual-cache/master.svg?style=flat-square)](https://travis-ci.org/istyle-inc/laravel-dual-cache)
[![Coverage Status](http://img.shields.io/coveralls/istyle-inc/laravel-dual-cache/master.svg?style=flat-square)](https://coveralls.io/github/istyle-inc/laravel-dual-cache?branch=master)
[![Scrutinizer Code Quality](http://img.shields.io/scrutinizer/g/istyle-inc/laravel-dual-cache.svg?style=flat-square)](https://scrutinizer-ci.com/g/istyle-inc/laravel-dual-cache/?branch=master)

[![License](http://img.shields.io/packagist/l/istyle-inc/laravel-dual-cache.svg?style=flat-square)](https://packagist.org/packages/istyle-inc/laravel-dual-cache)
[![Latest Version](http://img.shields.io/packagist/v/istyle-inc/laravel-dual-cache.svg?style=flat-square)](https://packagist.org/packages/istyle-inc/laravel-dual-cache)
[![Total Downloads](http://img.shields.io/packagist/dt/istyle-inc/laravel-dual-cache.svg?style=flat-square)](https://packagist.org/packages/istyle-inc/laravel-dual-cache)


## Install

required >= PHP 7.0

$ composer require istyle-inc/laravel-dual-cache

**Supported Auto-Discovery(^Laravel5.5)**

## Usage

added config/cache.php

### example

```php
'your-cache-driver-name' => [
    'driver' => 'dual-cache',
    'primary' => 'memcached',
    'secondary' => 'file',
],
```
