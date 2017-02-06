<?php

namespace AppBundle\Security\User;
use AppBundle\Entity\LoginLog;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;



class IktissabUser implements UserInterface, EquatableInterface
{

    private $username;
    private $password;
    private $salt;
    private $roles;
    private $ikt_card_no;

    public function __construct($username, $password, $ikt_card_no, $salt="", $roles = array('ROLE_IKTUSER'))
    {
//        die('webservice use const');
        $this->username = $username;
        $this->password = $password;
        $this->salt = $salt;
        $this->roles = $roles;
        $this->ikt_card_no = $ikt_card_no;
    }

    public function getRoles()
    {
        return $this->roles;
    }

    public function getPassword()
    {
        return $this->password;
    }
    public function setPassword($value)
    {
        $this->password = $value;
        return $this;
    }
    public function getSalt()
    {
        return $this->salt;
    }

    public function getUsername()
    {
        return $this->username;
    }
    public function setUsername($value)
    {
        $this->username = $value;
        return $this;
    }


    public function getIktCardNo(){
        return $this->ikt_card_no;
    }

    public function eraseCredentials()
    {
    }

    public function isEqualTo(UserInterface $user)
    {
        //die('inside is equal to the user is');
        if (!$user instanceof IktissabUser) {
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
}



?>