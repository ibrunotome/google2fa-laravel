<?php
/**
 * Created by PhpStorm.
 * User: veleg
 * Date: 25/08/2018
 * Time: 19:13
 */

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

    /**
     * @param Request $request
     */
    public function setStore()
    {
        $this->store = $this->getRequest()->bearerToken() ? new Cache($this->getRequest()) : new Session($this->getRequest());
    }

}