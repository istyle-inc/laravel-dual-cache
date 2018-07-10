<?php
declare(strict_types=1);

namespace Istyle\LaravelDualCache;

use Closure;
use Istyle\LaravelDualCache\Exception\DualCacheException;

/**
 * Interface DualCacheHandlerInterface
 */
interface DualCacheHandlerInterface
{
    /**
     * @param Closure      $function
     * @param Closure|null $secondary
     *
     * @return mixed
     * @throws DualCacheException
     */
    public function handle(Closure $function, Closure $secondary = null);
}
