# Istyle\LaravelDualCache

[![Build Status](https://travis-ci.org/istyle-inc/laravel-dual-cache.svg?branch=master)](https://travis-ci.org/istyle-inc/laravel-dual-cache)


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
