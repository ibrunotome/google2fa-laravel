<?php
/**
 * Created by PhpStorm.
 * User: veleg
 * Date: 25/08/2018
 * Time: 19:06
 */

namespace PragmaRX\Google2FALaravel\Interfaces;


interface StoreInterface
{

    public function get($var = null, $default = null);
    public function put($var, $value);
    public function forget($var = null);

}