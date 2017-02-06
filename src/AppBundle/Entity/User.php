<?php

namespace AppBundle\Entity;

use Doctrine\Instantiator\Exception\InvalidArgumentException;
use Doctrine\ORM\Mapping as ORM;

/**
 * User
 *
 * @ORM\Table(name="user")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Repository\UserRepository")
 */
class User
{
    const ACTIVATION_SOURCE_WEB = 'W';
    const ACTIVATION_SOURCE_MOBILE = 'M';
    const ACTIVATION_SOURCE_CALL_CENTER = 'C';
    /**
     * @var integer
     *
     * @ORM\Column(name="ikt_card_no", type="integer", nullable=false)
     * @ORM\Id
     */
    private $iktCardNo;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255, nullable=false)
     */
    private $email;

    /**
     * @var integer
     *
     * @ORM\Column(name="reg_date", type="integer", nullable=false)
     */
    private $regDate;

    /**
     * @var integer
     *
     * @ORM\Column(name="last_login", type="integer", nullable=true)
     */
    private $lastLogin;

    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string", length=255, nullable=false)
     */
    private $password;

    /**
     * @var string
     *
     * @ORM\Column(name="activation_source", type="string", length=1, nullable=false)
     */
    private $activationSource;
    

    /**
     * Get iktCardNo
     *
     * @return integer
     */
    public function getIktCardNo()
    {
        return $this->iktCardNo;
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
     * Set regDate
     *
     * @param integer $regDate
     *
     * @return User
     */
    public function setRegDate($regDate)
    {
        $this->regDate = $regDate;

        return $this;
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
        $this->iktCardNo = $iktCardNo;

        return $this;
    }

    /**
     * Get regDate
     *
     * @return integer
     */
    public function getRegDate()
    {
        return $this->regDate;
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
        $this->lastLogin = $lastLogin;

        return $this;
    }

    /**
     * Get lastLogin
     *
     * @return integer
     */
    public function getLastLogin()
    {
        return $this->lastLogin;
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

    public function getRoles()
    {
        return array('ROLE_API', 'ROLE_API_CUSTOMER');
    }

    /**
     * @param $activationSource
     * @return User
     */
    public function setActivationSource($activationSource)
    {
        if(!in_array($activationSource, [self::ACTIVATION_SOURCE_WEB, self::ACTIVATION_SOURCE_MOBILE, self::ACTIVATION_SOURCE_CALL_CENTER])){
            throw new InvalidArgumentException('Invalid Activation Source');
        }
        $this->activationSource = $activationSource;
        return $this;
    }
    /**
     * @return string
     */
    public function getActivationSource()
    {
        return $this->activationSource;
    }
}
