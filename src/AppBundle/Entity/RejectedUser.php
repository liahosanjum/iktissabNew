<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * RejectedUser
 *
 * @ORM\Table(name="rejected_user")
 * @ORM\Entity
 */
class RejectedUser
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="bigint", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="ikt_card_no", type="integer", nullable=false)
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
     * @ORM\Column(name="reg_date", type="integer", nullable=false)
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
     * @param int $iktCardNo
     */
    public function setIktCardNo($iktCardNo)
    {
        $this->iktCardNo = $iktCardNo;
    }

    /**
     * @return int
     */
    public function getIktCardNo()
    {
        return $this->iktCardNo;
    }

    /**
     * @param string $activationSource
     */
    public function setActivationSource($activationSource)
    {
        $this->activationSource = $activationSource;
    }

    /**
     * @return string
     */
    public function getActivationSource()
    {
        return $this->activationSource;
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param int $regDate
     */
    public function setRegDate($regDate)
    {
        $this->regDate = $regDate;
    }

    /**
     * @return int
     */
    public function getRegDate()
    {
        return $this->regDate;
    }


}

