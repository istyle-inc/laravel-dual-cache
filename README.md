# Istyle\LaravelDualCache

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
