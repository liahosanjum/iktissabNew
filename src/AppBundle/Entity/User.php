<?php
/**
 * Created by PhpStorm.
 * User: abdulali
 * Date: 11/30/16
 * Time: 3:40 PM
 */
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class User
 * @package AppBundle\Entity
 * @ORM\Entity()
 * @ORM\Table(name="user")
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Repository\UserRepository")
 */
class User
{

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id()
     */
    private $ikt_card_no;
    /**
     * @ORM\Column(type="string", length=255)
     */
    private $email;
    /**
     * @ORM\Column(type="string", length=255)
     *
     */
    private $password;
    /**
     * @ORM\Column(type="integer")
     */
    private $reg_date;
    /**
     * @ORM\Column(type="integer")
     */
    private $last_login;

    public function __construct()
    {
        $this->active = true;
    }




    /**
     * Set iktCardNo
     *
     * @param integer $iktCardNo
     *
     * @return User
     */
    public function setIktCardNo($iktCardNo)
    {
        $this->ikt_card_no = $iktCardNo;

        return $this;
    }

    /**
     * Get iktCardNo
     *
     * @return integer
     */
    public function getIktCardNo()
    {
        return $this->ikt_card_no;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set password
     *
     * @param string $password
     *
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set regDate
     *
     * @param integer $regDate
     *
     * @return User
     */
    public function setRegDate($regDate)
    {
        $this->reg_date = $regDate;

        return $this;
    }

    /**
     * Get regDate
     *
     * @return integer
     */
    public function getRegDate()
    {
        return $this->reg_date;
    }

    /**
     * Set lastLogin
     *
     * @param integer $lastLogin
     *
     * @return User
     */
    public function setLastLogin($lastLogin)
    {
        $this->last_login = $lastLogin;

        return $this;
    }

    /**
     * Get lastLogin
     *
     * @return integer
     */
    public function getLastLogin()
    {
        return $this->last_login;
    }
}
