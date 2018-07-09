<?php
declare(strict_types=1);

namespace Istyle\LaravelDualCache;

use Illuminate\Cache\TaggableStore;
use Illuminate\Contracts\Cache\Store;
use Illuminate\Cache\RetrievesMultipleKeys;
use Istyle\LaravelDualCache\Exception\DualCacheException;

/**
 * Class DualCacheStore
 */
class DualCacheStore extends TaggableStore implements Store
{
    use RetrievesMultipleKeys;

    /** @var Store */
    protected $primaryStore;

    /** @var callable */
    protected $secondaryStore;

    /** @var Store */
    protected $secondaryCacheStorage = null;

    /**
     * FusionCacheStore constructor.
     *
     * @param Store    $primaryStore
     * @param callable $secondaryStore
     */
    public function __construct(Store $primaryStore, callable $secondaryStore)
    {
        $this->primaryStore = $primaryStore;
        $this->secondaryStore = $secondaryStore;
    }

    /**
     * @param array|string $key
     *
     * @return mixed
     * @throws \Throwable
     */
    public function get($key)
    {
        return $this->handleError(function () use ($key) {
            $primaryResult = $this->primaryStore->get($key);
            if (!is_null($primaryResult)) {
                return $primaryResult;
            }
            $secondaryResult = $this->secondaryStore()->get($key);
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
        $this->handleError(function () use ($key, $value, $minutes) {
            $this->primaryStore->put($key, $value, $minutes);
            $this->secondaryStore()->put($key, $value, $minutes);
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

        return $this->handleError(function () use ($key, $value) {
            $primaryResult = $this->primaryStore->increment($key, $value);
            $secondaryResult = $this->secondaryStore()->increment($key, $value);
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

        return $this->handleError(function () use ($key, $value) {
            $primaryResult = $this->primaryStore->decrement($key, $value);
            $secondaryResult = $this->secondaryStore()->decrement($key, $value);
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
        return $this->handleError(function () use ($key) {
            $primaryResult = $this->primaryStore->forget($key);
            $secondaryResult = $this->secondaryStore()->forget($key);
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
        $this->handleError(function () {
            $this->primaryStore->flush();
            $this->secondaryStore()->flush();
        });
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function getPrefix()
    {
        return '';
    }

    /**
     * @return Store
     */
    private function secondaryStore(): Store
    {
        if (\is_null($this->secondaryCacheStorage)) {
            $this->secondaryCacheStorage = call_user_func($this->secondaryStore);
        }

        return $this->secondaryCacheStorage;
    }

    /**
     * @param callable      $function
     * @param callable|null $secondary
     *
     * @return mixed
     * @throws \Throwable
     */
    protected function handleError(callable $function, callable $secondary = null)
    {
        try {
            return call_user_func($function);
        } catch (\Exception $e) {
            if (is_callable($secondary)) {
                return call_user_func($secondary);
            }
            throw new DualCacheException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
