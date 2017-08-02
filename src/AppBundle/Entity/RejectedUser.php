<?php

namespace AppBundle\Entity;

use Doctrine\Instantiator\Exception\InvalidArgumentException;
use Doctrine\ORM\Mapping as ORM;


/**
 * RejectedUser
 *
 * @ORM\Entity
 * @ORM\Table(name="rejected_user", options={"engine"="MyISAM"})
 */
class RejectedUser
{
    const NUM_ITEMS = 10;
    const ACTIVATION_SOURCE_WEB = 'W';
    const ACTIVATION_SOURCE_MOBILE = 'M';
    const ACTIVATION_SOURCE_CALL_CENTER = 'C';
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="bigint", nullable=false)
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="ikt_card_no", type="integer", nullable=false, options={"unsigned"=true})
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
     * @var string
     *
     * @ORM\Column(name="activation_source", type="string", length=1, nullable=true)
     */
    private $activationSource;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

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
     * @return RejectedUser
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
     * @return RejectedUser
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
     * @return RejectedUser
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
     * @param $activationSource
     * @return RejectedUser
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
