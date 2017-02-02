<?php
/**
 * Created by PhpStorm.
 * User: abdulali
 * Date: 12/9/16
 * Time: 9:21 AM
 */
namespace AppBundle\Security\Authentication\Token;

use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

class WsseUserToken extends AbstractToken
{
    public $created;
    public $digest;
    public $nonce;
    public $area;
    public function __construct(array $roles = array())
    {
//        var_dump($roles);
//        die('----');

        parent::__construct($roles);

        // If the user has roles, consider it authenticated
        $this->setAuthenticated(count($roles) > 0);
    }

    public function getCredentials()
    {
        return '';
    }
}