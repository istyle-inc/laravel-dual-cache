# Istyle\LaravelDualCache

[![Build Status](https://travis-ci.org/istyle-inc/laravel-dual-cache.svg?branch=master)](https://travis-ci.org/istyle-inc/laravel-dual-cache)
[![Coverage Status](https://coveralls.io/repos/github/istyle-inc/laravel-dual-cache/badge.svg)](https://coveralls.io/github/istyle-inc/laravel-dual-cache)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/istyle-inc/laravel-dual-cache/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/istyle-inc/laravel-dual-cache/?branch=master)

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
