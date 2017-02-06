<?php
/**
 * Created by PhpStorm.
 * User: s.aman
 * Date: 1/20/17
 * Time: 12:53 PM
 */

namespace AppBundle\Exceptions;


class RestServiceFailedException extends \Exception
{
    public function __construct($code, \Exception $previous)
    {
        parent::__construct($previous->getMessage(), $code, $previous);
    }

}