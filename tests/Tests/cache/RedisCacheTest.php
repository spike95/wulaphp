<?php
/*
 * This file is part of wulacms.
 *
 * (c) Leo Ning <windywany@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace tests\Tests\cache;

use PHPUnit\Framework\TestCase;
use wulaphp\cache\Cache;
use wulaphp\cache\RedisCache;
use wulaphp\conf\CacheConfiguration;
use wulaphp\conf\ConfigurationLoader;

class RedisCacheTest extends TestCase {
    public static function setUpBeforeClass() {
        bind('on_load_cache_config', function ($conf) {
            $conf = new CacheConfiguration();
            $conf->enabled();
            $conf->addRedisServer('127.0.0.1', 6379, 1);
            $conf->addMemcachedServer('127.0.0.1');
            $conf->setDefaultCache(CACHE_TYPE_REDIS);

            return $conf;
        });
    }

    /**
     * @return \wulaphp\cache\Cache
     */
    public function testGetCacheIns() {
        self::assertTrue(has_hook('on_load_cache_config'));
        self::assertTrue(has_hook('get_redis_cache'));

        $cfg = ConfigurationLoader::loadFromFile('cache');
        self::assertTrue($cfg->getb('enabled'));
        self::assertEquals(CACHE_TYPE_REDIS, $cfg->get('default'));

        $redisCfg = $cfg->get('redis');
        self::assertEquals('127.0.0.1', $redisCfg[0]);

        $cache = RedisCache::getInstance($cfg);
        self::assertTrue($cache instanceof RedisCache);

        $cache = Cache::getCache();
        self::assertTrue($cache instanceof RedisCache, get_class($cache));

        return $cache;
    }

    /**
     * @param Cache $cache
     *
     * @depends testGetCacheIns
     * @return Cache
     */
    public function testAdd($cache) {
        self::assertTrue($cache->add('test', 'this text will be cached for 60s', 60));
        self::assertTrue($cache->add('test1', 'this text1 will be cached for 60s', 60));
        self::assertTrue($cache->add('test2', 'this text2 will be cached for 60s', 60));

        self::assertTrue($cache->has_key('test'));
        self::assertTrue($cache->has_key('test1'));
        self::assertTrue($cache->has_key('test2'));
        self::assertTrue(!$cache->has_key('test3'));

        return $cache;
    }

    /**
     * @param Cache $cache
     *
     * @depends testAdd
     * @return Cache
     */
    public function testGet($cache) {
        $text = $cache->get('test');

        self::assertEquals('this text will be cached for 60s', $text);

        return $cache;
    }

    /**
     * @param Cache $cache
     *
     * @depends testAdd
     * @return Cache
     */
    public function testDel($cache) {
        self::assertTrue($cache->delete('test1'));
        self::assertTrue(!$cache->has_key('test1'));
        $text = $cache->get('test1');
        self::assertNull($text);

        return $cache;
    }

    /**
     * @param Cache $cache
     *
     * @depends testDel
     * @return Cache
     */
    public function testClear($cache) {
        self::assertTrue($cache->clear());
        self::assertTrue(!$cache->has_key('test'));
        $text = $cache->get('test');
        self::assertNull($text);

        self::assertTrue(!$cache->has_key('test2'));
        $text = $cache->get('test2');
        self::assertNull($text);

        return $cache;
    }
}