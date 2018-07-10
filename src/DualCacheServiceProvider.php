<?php
declare(strict_types=1);

namespace Istyle\LaravelDualCache;

use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\CacheManager;
use Illuminate\Cache\Repository;
use Illuminate\Session\CacheBasedSessionHandler;

/**
 * Class DualCacheServiceProvider
 */
final class DualCacheServiceProvider extends ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        $this->registerCacheDriver();
    }

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->app['session']->extend('dual-session', function ($app) {
            $minutes = $app['config']['session.lifetime'];
            return new CacheBasedSessionHandler(
                clone $this->app['cache']->driver('dual-session'),
                $minutes
            );
        });
    }

    /**
     * register fusion cache
     */
    public function registerCacheDriver()
    {
        $this->app['cache']->extend('dual-cache', function ($app, $config) {
            /** @var CacheManager $cacheManager */
            $cacheManager = $app['cache'];
            return new Repository(
                new DualCacheStore(
                    $cacheManager->store($config['primary'])->getStore(),
                    $cacheManager->store($config['secondary'])->getStore(),
                    new DualCacheHandler()
                )
            );
        });
    }
}
