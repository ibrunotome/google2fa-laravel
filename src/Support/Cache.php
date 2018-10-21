<?php

namespace PragmaRX\Google2FALaravel\Support;

use Illuminate\Http\Request as IlluminateRequest;
use Illuminate\Support\Facades\Cache as IlluminateCache;
use PragmaRX\Google2FALaravel\Interfaces\StoreInterface;

class Cache implements StoreInterface
{
    use Auth, Config, Request;

    public function __construct(IlluminateRequest $request)
    {
        $this->setRequest($request);
    }

    /**
     * Get a cache var value.
     *
     * @param null $var
     * @param null $default
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function get($var = null, $default = null)
    {
        return IlluminateCache::get($this->makeCacheVarName($var), $default);
    }

    /**
     * Make a cache var name for.
     *
     * @param null $name
     *
     * @return mixed
     *
     * @throws \Exception
     */
    protected function makeCacheVarName($name = null)
    {
        return $this->config('session_var') . (is_null($name) || empty($name) ? '' : '.' . $name) . ":" .
            $this->getUser()->id;
    }

    /**
     * Put a var value to the cache.
     *
     * @param $var
     * @param $value
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function put($var, $value)
    {
        ($this->config('cache_lifetime') === 0) ?
            IlluminateCache::forever($this->makeCacheVarName($var), $value) :
            IlluminateCache::put($this->makeCacheVarName($var), $value, $this->config('cache_lifetime'));

        return $value;
    }

    /**
     * Forget a session var.
     *
     * @param null $var
     *
     * @throws \Exception
     */
    public function forget($var = null)
    {
        IlluminateCache::forget($this->makeCacheVarName($var));
    }
}