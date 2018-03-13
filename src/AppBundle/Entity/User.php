<?php

namespace AppBundle\Entity;

use Doctrine\Instantiator\Exception\InvalidArgumentException;
use Doctrine\ORM\Mapping as ORM;

/**
 * User
 *
 * @ORM\Table(name="user",options={"engine"="MyISAM"})
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Repository\UserRepository")
 */
class User
{
    const NUM_ITEMS = 10;
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
     * @ORM\Column(name="email", type="string", length=80, nullable=false)
     */
    private $email;

    /**
     * @var integer
     *
     * @ORM\Column(name="reg_date", type="integer", nullable=false, options={"unsigned"=true})
     */
    private $regDate;

    /**
     * @var integer
     *
     * @ORM\Column(name="last_login", type="integer", nullable=true, options={"unsigned"=true})
     */
    private $lastLogin;

    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string", length=32, nullable=false)
     */
    private $password;

    /**
     * @var string
     *
     * @ORM\Column(name="country", type="string", length=2, nullable=true)
     */
    private $country;

    /**
     * @var string
     *
     * @ORM\Column(name="data", type="string", length=800, nullable=true)
     */
    private $data;

    /**
     * @var string
     *
     * @ORM\Column(name="activation_source", type="string", length=1, nullable=true)
     */
    private $activationSource;

    /**
     * @var int
     *
     * @ORM\Column(name="status", type="smallint", nullable=false)
     */
    private $status;

    /**
     * @var int
     *
     * @ORM\Column(name="modified", type="integer", nullable=true, options={"unsigned"=true})
     */
    private $modified;

    


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


    /**
     * Set data
     *
     * @param string $data
     *
     * @return User
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Set country
     *
     * @param string $country
     *
     * @return string
     */
    public function setCountry($country)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * Get country
     *
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }


    /**
     * Get data
     *
     * @return string
     */
    public function getData()
    {
        return $this->data;
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

    /**
     * @param int $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param int $modified
     */
    public function setModified($modified)
    {
        $this->modified = $modified;
    }

    /**
     * @return int
     */
    public function getModified()
    {
        return $this->modified;
    }

    /**
     * @ORM\PreUpdate()
     */
    public function OnPreUpdate(){
        $d = new  \DateTime("now");
        $this->modified = $d->getTimestamp();
    }


   



}
