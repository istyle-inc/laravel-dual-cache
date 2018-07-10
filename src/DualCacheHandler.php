<?php
declare(strict_types=1);

namespace Istyle\LaravelDualCache;

use Throwable;
use Closure;
use Istyle\LaravelDualCache\Exception\DualCacheException;

/**
 * Class DualCacheHandler
 */
class DualCacheHandler implements DualCacheHandlerInterface
{
    /**
     * @param Closure      $function
     * @param Closure|null $secondary
     *
     * @return mixed
     */
    public function handle(Closure $function, Closure $secondary = null)
    {
        try {
            return call_user_func($function);
        } catch (Throwable $e) {
            if (is_callable($secondary)) {
                return call_user_func($secondary);
            }
            throw new DualCacheException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
