# Istyle\LaravelDualCache

## Usage

cache and session.

```php
'your-cache-driver-name' => [
    'driver' => 'dual-cache',
    'primary' => 'memcached',
    'secondary' => 'file',
],
```

