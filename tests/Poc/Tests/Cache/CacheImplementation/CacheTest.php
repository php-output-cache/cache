<?php
/*
 * Copyright 2013 Imre Toth <tothimre at gmail> Licensed under the Apache
 * License, Version 2.0 (the "License"); you may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.apache.org/licenses/LICENSE-2.0 Unless required by applicable law
 * or agreed to in writing, software distributed under the License is
 * distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
 * KIND, either express or implied. See the License for the specific language
 * governing permissions and limitations under the License.
 */

namespace Poc\Tests\Cache\CacheImplementation;

use Poc\Exception\CacheNotReachableException;
use Poc\Exception\DriverNotFoundException;

abstract class CacheTest extends \PHPUnit_Framework_TestCase
{

    const TESTDATA = 'testdata';

    const TTL = 1;

    public $cache = null;

    public $TESTKEY = 'testkey';

    abstract public function setUp_ ();

    protected function setUp()
    {
       $this->TESTKEY .= rand() . rand();

        try {
            $this->setUp_();
        }
        catch (DriverNotFoundException $e) {
            $this->markTestSkipped($e->getMessage());
        }
        catch (CacheNotReachableException $e) {
            $this->markTestSkipped($e->getMessage());
        }
        catch (\Exception $e) {
            $this->assertTrue(false);
        }

        $this->cache->clearAll();
    }

    public function testCacheSpecificFetch ()
    {
        $this->cache->cacheSpecificStore($this->TESTKEY, self::TESTDATA);
        //this is for mongodb
        usleep(500);
        $fetchedFromCache = $this->cache->fetch($this->TESTKEY);
        $this->assertEquals($fetchedFromCache, self::TESTDATA);

        $this->cache->cacheSpecificStore($this->TESTKEY, self::TESTDATA."+1");
        //this is for mongodb
        usleep(500);
        $fetchedFromCache = $this->cache->fetch($this->TESTKEY);
        $this->assertEquals($fetchedFromCache, self::TESTDATA."+1");

        sleep(self::TTL + 1);
        $fetchedFromCache = $this->cache->fetch($this->TESTKEY);
        $this->assertEquals($fetchedFromCache, '');

        $this->assertTrue($this->cache->fetch($this->TESTKEY) == '');
    }

    public function testCacheSpecificClearAll ()
    {
        $this->cache->cacheSpecificStore($this->TESTKEY, self::TESTDATA);
        $this->cache->clearAll();
        $this->assertTrue(
                $this->cache->fetch($this->TESTKEY) != self::TESTDATA);
        $this->assertTrue($this->cache->fetch($this->TESTKEY) == '');
    }

    public function testCacheSpecificClearItem ()
    {
        $this->cache->cacheSpecificStore($this->TESTKEY, self::TESTDATA);
        $this->cache->clearItem($this->TESTKEY);
        $this->assertTrue(
                $this->cache->fetch($this->TESTKEY) != self::TESTDATA);
        $this->assertTrue($this->cache->fetch($this->TESTKEY) == '');

    }

    public function testCacheSpecificStore ()
    {
        $this->cache->cacheSpecificStore($this->TESTKEY, self::TESTDATA);
        $this->assertTrue(
                $this->cache->fetch($this->TESTKEY) == self::TESTDATA);
    }
}
