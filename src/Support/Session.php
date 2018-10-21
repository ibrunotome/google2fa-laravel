<?php

namespace PragmaRX\Google2FALaravel\Support;

use PragmaRX\Google2FALaravel\Interfaces\StoreInterface;
use Illuminate\Http\Request as IlluminateRequest;

class Session implements StoreInterface
{
    use Config, Request;

    public function __construct(IlluminateRequest $request)
    {
        $this->setRequest($request);
    }

    /**
     * Make a session var name for.
     *
     * @param null $name
     *
     * @return mixed
     */
    protected function makeSessionVarName($name = null)
    {
        return $this->config('session_var').(is_null($name) || empty($name) ? '' : '.'.$name);
    }

    /**
     * Get a session var value.
     *
     * @param null $var
     *
     * @return mixed
     */
    public function get($var = null, $default = null)
    {
        return $this->getRequest()->session()->get(
            $this->makeSessionVarName($var),
            $default
        );
    }

    /**
     * Put a var value to the current session.
     *
     * @param $var
     * @param $value
     *
     * @return mixed
     */
    public function put($var, $value)
    {
        $this->getRequest()->session()->put(
            $this->makeSessionVarName($var),
            $value
        );

        return $value;
    }

    /**
     * Forget a session var.
     *
     * @param null $var
     */
    public function forget($var = null)
    {
        $this->getRequest()->session()->forget(
            $this->makeSessionVarName($var)
        );
    }

}
