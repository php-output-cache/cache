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

/**
 * This class utilizes the REDIS key-value database for caching.
 *
 * @author Imre Toth
 *
 */

namespace Poc\Cache\CacheImplementation;

use Poc\Exception\CacheNotReachableException;
use Poc\Exception\DriverNotFoundException;
use Predis\Network\ConnectionException;

class PredisCache extends Cache
{
    protected $redis;

    protected function setupDefaults ()
    {
        parent::setupDefaults();
        $this->optionable->setDefaultOption('servers', array('servers' => array(array('host' => 'localhost', 'port' => '6379'))));
    }

    public function __construct ($options = array())
    {
        parent::__construct($options);

        $className = 'Predis\Client';
        // @codeCoverageIgnoreStart
        if (! class_exists($className)) {
            throw new DriverNotFoundException('Predis driver not found');
        }
       // @codeCoverageIgnoreEnd

        $this->redis = new $className($this->optionable['servers']);

        // @codeCoverageIgnoreStart
        try {
            $this->redis->connect();
        } catch (ConnectionException $e) {
            throw new CacheNotReachableException('Redis not reachable');
        }
        // @codeCoverageIgnoreEnd
    }

    public function fetch ($key)
    {
        $value = $this->redis->get($key);

        return $value;
    }

    public function clearAll ()
    {
        $this->redis->flushdb();
    }

    public function clearItem ($key)
    {
        $this->redis->del($key);
    }

    public function cacheSpecificStore ($key, $output, $ttl=null)
    {
        $this->redis->set($key, $output);
        $this->redis->expire($key, $this->getRealTtl($ttl));
    }
}