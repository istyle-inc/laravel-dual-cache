<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Istyle\LaravelDualCache\DualCacheStore;
use Istyle\LaravelDualCache\DualCacheHandler;

final class DualCacheStoreTest extends TestCase
{
    /** @var DualCacheStore */
    private $primary;

    /** @var DualCacheStore */
    private $secondary;

    protected function setUp()
    {
        $this->primary = $this->primary();
        $this->secondary = $this->secondary();
    }

    /**
     * @return DualCacheStore
     */
    protected function primary()
    {
        return new DualCacheStore(
            new \Illuminate\Cache\ArrayStore(),
            new \Illuminate\Cache\NullStore(),
            new DualCacheHandler()
        );
    }

    /**
     * @return DualCacheStore(
     */
    protected function secondary()
    {
        return new  DualCacheStore(
            new \Illuminate\Cache\NullStore(),
            new \Illuminate\Cache\ArrayStore(),
            new DualCacheHandler()
        );
    }

    public function testShouldReturnFusionCacheInstance()
    {
        $this->assertInstanceOf(DualCacheStore::class, $this->primary);
        $this->assertInstanceOf(DualCacheStore::class, $this->secondary);
    }

    public function testShouldReturnNullForFusionCache()
    {
        $this->assertNull($this->primary->get('123456'));
        $this->assertNull($this->secondary->get('123456'));
    }

    /**
     * @runInSeparateProcess
     */
    public function testShouldReturnCacheValuesForPrimary()
    {
        $this->primary->put('testing:primary', 123, 60);
        $this->assertSame(123, $this->primary->get('testing:primary'));
    }

    /**
     * @runInSeparateProcess
     */
    public function testShouldReturnCacheValuesForSecondary()
    {
        $this->secondary->put('testing:secondary', 123, 60);
        $this->assertSame(null, $this->secondary->get('testing:secondary'));
    }

    /**
     * @runInSeparateProcess
     */
    public function testShouldReturnIntegerForPrimary()
    {
        $this->primary->forever('testing:primary:incr', 0);
        $this->assertSame(1, $this->primary->increment('testing:primary:incr', 1));
        $this->assertSame(0, $this->primary->decrement('testing:primary:incr', 1));
    }

    /**
     * @runInSeparateProcess
     */
    public function testShouldReturnIntegerForSecondary()
    {
        $this->secondary->forever('testing:secondary:incr', 0);
        $this->assertSame(1, $this->secondary->increment('testing:secondary:incr', 1));
        $this->assertSame(0, $this->secondary->decrement('testing:secondary:incr', 1));
    }

    public function testShouldReturnNullWhenEmpty()
    {
        $this->primary->put('testing', 123, 60);
        $this->primary->forget('testing');
        $this->assertNull($this->primary->get('testing'));
        $this->secondary->put('testing', 123, 60);
        $this->secondary->forget('testing');
        $this->assertNull($this->secondary->get('testing'));
    }

    public function testShouldReturnNullWhenFlushStorage()
    {
        $this->primary->put('testing', 123, 60);
        $this->primary->flush();
        $this->assertNull($this->primary->get('testing'));
        $this->secondary->put('testing', 123, 60);
        $this->secondary->flush();
        $this->assertNull($this->secondary->get('testing'));
    }

    /**
     * @runInSeparateProcess
     */
    public function testShouldReturnPreviousValueWhenThrowException()
    {
        $arrayCache = new \Illuminate\Cache\ArrayStore;
        $arrayCache->put('testing:throwable', 1234, 60);
        $partialMock = Mockery::mock(\Illuminate\Cache\ArrayStore::class)->makePartial();
        $partialMock->shouldReceive('put')->andThrow(\Exception::class, 'throw exception from mock');
        $cache = new DualCacheStore(
            $partialMock,
            $arrayCache,
            new DualCacheHandler()
        );
        $this->assertSame(1234, $arrayCache->get('testing:throwable'));
        try {
            $cache->put('testing:throwable', 'exception', 120);
        } catch (\Exception $e) {
            $this->assertNull($arrayCache->get('testing:throwable'));
        }
    }

    /**
     * @runInSeparateProcess
     */
    public function testShouldReturnNullForIncrementBothDriverReturningNull()
    {
        $primaryMock = Mockery::mock(\Illuminate\Cache\ArrayStore::class)->makePartial();
        $primaryMock->shouldReceive('increment')->andReturnNull();
        $secondaryMock = clone $primaryMock;
        $cache = new DualCacheStore(
            $primaryMock,
            $secondaryMock,
            new DualCacheHandler()
        );
        $this->assertNull($cache->increment('testing:increment', 200));
    }

    /**
     * @runInSeparateProcess
     */
    public function testShouldReturnNullForDecrementBothDriverReturningNull()
    {
        $primaryMock = Mockery::mock(\Illuminate\Cache\ArrayStore::class)->makePartial();
        $primaryMock->shouldReceive('decrement')->andReturnNull();
        $secondaryMock = clone $primaryMock;
        $cache = new DualCacheStore(
            $primaryMock,
            $secondaryMock,
            new DualCacheHandler()
        );
        $this->assertNull($cache->decrement('testing:decrement', 200));
    }

    /**
     * @runInSeparateProcess
     * @expectedException Istyle\LaravelDualCache\Exception\DualCacheException
     */
    public function testShouldThrowDualCacheException()
    {
        $partialMock = Mockery::mock(\Illuminate\Cache\ArrayStore::class)->makePartial();
        $partialMock->shouldReceive('forget')->andThrow(\Exception::class, 'throw exception from mock');
        $partialSecondaryMock = Mockery::mock(\Illuminate\Cache\ArrayStore::class)->makePartial();
        $partialSecondaryMock->shouldReceive('forget')->andThrow(\Exception::class, 'throw exception from mock');
        $cache = new DualCacheStore(
            $partialMock,
            $partialSecondaryMock,
            new DualCacheHandler()
        );
       $cache->forget('testing:primary:incr');
    }

    /**
     * @runInSeparateProcess
     */
    public function testShouldThrowDualCacheExceptions()
    {
        $partialMock = Mockery::mock(\Illuminate\Cache\ArrayStore::class)->makePartial();
        $partialMock->shouldReceive('get')->andThrow(\Exception::class, 'throw exception from mock');
        $cache = new DualCacheStore(
            $partialMock,
            new \Illuminate\Cache\ArrayStore(),
            new DualCacheHandler()
        );
        $this->assertNull($cache->get('testing:primary'));
    }

    protected function tearDown()
    {
        Mockery::close();
    }
}
