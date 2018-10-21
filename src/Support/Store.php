<?php

namespace PragmaRX\Google2FALaravel\Support;

use PragmaRX\Google2FALaravel\Interfaces\StoreInterface;

trait Store
{
    /**
     * The StoreInterface instance
     *
     * @var
     */
    protected $store;

    /**
     * @return StoreInterface
     */
    public function getStore()
    {
        return $this->store;
    }

    public function setStore($request)
    {
        $this->store = new Cache($request);
    }

}