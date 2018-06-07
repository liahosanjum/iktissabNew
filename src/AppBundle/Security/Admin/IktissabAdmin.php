<?php

namespace AppBundle\Security\Admin;
use AppBundle\Entity\LoginLog;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;



class IktissabAdmin implements UserInterface, EquatableInterface
{

    private $username;
    private $password;
    private $salt;
    private $roles;
    private $id;
    private $rolename;


    public function __construct($username, $password, $id, $salt="", $roles = array('ROLE_IKTADMIN'), $rolename )
    {
        // die('webservice use const');
        $this->username = $username;
        $this->password = $password;
        $this->salt = $salt;
        $this->roles = $roles;
        $this->id = $id;
        $this->rolename = $rolename;

    }

    public function getRoles()
    {
        return $this->roles;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function getSalt()
    {
        return $this->salt;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function getId(){
        return $this->id;
    }

    public function eraseCredentials()
    {
    }

    public function isEqualTo(UserInterface $user)
    {
        //die('inside is equal to the user is');
        if (!$user instanceof IktissabAdmin) {
            return false;
        }



        if ($this->password !== $user->getPassword()) {
            return false;
        }

        if ($this->salt !== $user->getSalt()) {
            return false;
        }

        if ($this->username !== $user->getUsername()) {
            return false;
        }


        return true;
    }





    public function getRoleName()
    {
        return $this->rolename;
    }
}



?>