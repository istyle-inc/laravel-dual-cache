<?php
declare(strict_types=1);

namespace Istyle\LaravelDualCache;

use Illuminate\Cache\TaggableStore;
use Illuminate\Contracts\Cache\Store;
use Illuminate\Cache\RetrievesMultipleKeys;

/**
 * Class DualCacheStore
 */
class DualCacheStore extends TaggableStore implements Store
{
    use RetrievesMultipleKeys;

    /** @var Store */
    protected $primaryStore;

    /** @var Store */
    protected $secondaryStore;

    /** @var DualCacheHandlerInterface */
    protected $cacheHandler;

    /**
     * @param Store                     $primaryStore
     * @param Store                     $secondaryStore
     * @param DualCacheHandlerInterface $cacheHandler
     */
    public function __construct(
        Store $primaryStore,
        Store $secondaryStore,
        DualCacheHandlerInterface $cacheHandler
    ) {
        $this->primaryStore = $primaryStore;
        $this->secondaryStore = $secondaryStore;
        $this->cacheHandler = $cacheHandler;
    }

    /**
     * @param array|string $key
     *
     * @return mixed
     * @throws \Throwable
     */
    public function get($key)
    {
        return $this->cacheHandler->handle(function () use ($key) {
            $primaryResult = $this->primaryStore->get($key);
            if (!is_null($primaryResult)) {
                return $primaryResult;
            }

            return null;
        }, function () use ($key) {
            $secondaryResult = $this->secondaryStore->get($key);
            if (!is_null($secondaryResult)) {
                return $secondaryResult;
            }

            return null;
        });
    }

    /**
     * @param string    $key
     * @param mixed     $value
     * @param float|int $minutes
     *
     * @throws \Throwable
     */
    public function put($key, $value, $minutes)
    {
        $this->cacheHandler->handle(function () use ($key, $value, $minutes) {
            $this->primaryStore->put($key, $value, $minutes);
            $this->secondaryStore->put($key, $value, $minutes);
        }, function () use ($key) {
            $this->forget($key);
        });
    }

    /**
     * @param string $key
     * @param int    $value
     *
     * @return bool|int|mixed
     * @throws \Throwable
     */
    public function increment($key, $value = 1)
    {
        $previousValue = $this->get($key);

        return $this->cacheHandler->handle(function () use ($key, $value) {
            $primaryResult = $this->primaryStore->increment($key, $value);
            $secondaryResult = $this->secondaryStore->increment($key, $value);
            if (!is_null($primaryResult)) {
                return $primaryResult;
            }
            if (!is_null($secondaryResult)) {
                return $secondaryResult;
            }

            return null;
        }, function () use ($key, $previousValue) {
            if ($previousValue) {
                return $this->forever($key, $previousValue);
            }

            return $this->forget($key);
        });
    }

    /**
     * @param string $key
     * @param int    $value
     *
     * @return bool|int|mixed
     * @throws \Throwable
     */
    public function decrement($key, $value = 1)
    {
        $previousValue = $this->get($key);

        return $this->cacheHandler->handle(function () use ($key, $value) {
            $primaryResult = $this->primaryStore->decrement($key, $value);
            $secondaryResult = $this->secondaryStore->decrement($key, $value);
            if (!is_null($primaryResult)) {
                return $primaryResult;
            }
            if (!is_null($secondaryResult)) {
                return $secondaryResult;
            }

            return null;
        }, function () use ($key, $previousValue) {
            if ($previousValue) {
                return $this->forever($key, $previousValue);
            }

            return $this->forget($key);
        });
    }

    /**
     * @param string $key
     * @param mixed  $value
     *
     * @throws \Throwable
     */
    public function forever($key, $value)
    {
        $this->put($key, $value, 0);
    }

    /**
     * @param string $key
     *
     * @return bool|mixed
     * @throws \Throwable
     */
    public function forget($key)
    {
        return $this->cacheHandler->handle(function () use ($key) {
            $primaryResult = $this->primaryStore->forget($key);
            $secondaryResult = $this->secondaryStore->forget($key);
            if ($primaryResult) {
                return $primaryResult;
            }
            if ($secondaryResult) {
                return $secondaryResult;
            }

            return false;
        });
    }

    /**
     * @return void
     * @throws \Throwable
     */
    public function flush()
    {
        $this->cacheHandler->handle(function () {
            $this->primaryStore->flush();
            $this->secondaryStore->flush();
        });
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function getPrefix(): string
    {
        return '';
    }
}
