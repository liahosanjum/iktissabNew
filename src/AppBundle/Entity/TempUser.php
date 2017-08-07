<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TempUser
 *
 * @ORM\Table(name="temp_user", indexes={@ORM\Index(name="Id", columns={"Id"})})
 * @ORM\Entity
 */
class TempUser
{
    /**
     * @var integer
     *
     * @ORM\Column(name="Id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="Ikt_card_no", type="integer", nullable=false)
     */
    private $iktCardNo;

    /**
     * @var string
     *
     * @ORM\Column(name="field", type="string", length=255, nullable=false)
     */
    private $field;

    /**
     * @var string
     *
     * @ORM\Column(name="data", type="string", length=1000, nullable=false)
     */
    private $data;

    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="integer", nullable=false)
     */
    private $status;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="regDate", type="datetime", nullable=false)
     */
    private $regdate = 'CURRENT_TIMESTAMP';

    /**
     * @var string
     *
     * @ORM\Column(name="country", type="string", length=2, nullable=false)
     */
    private $country;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $status
     *
     * @return TempUser
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param int $iktCardNo
     *
     * @return TempUser
     */
    public function setIktCardNo($iktCardNo)
    {
        $this->iktCardNo = $iktCardNo;
        return $this;
    }

    /**
     * @return int
     */
    public function getIktCardNo()
    {
        return $this->iktCardNo;
    }

    /**
     * @param string $field
     *
     * @return TempUser
     */
    public function setField($field)
    {
        $this->field = $field;
        return $this;
    }

    /**
     * @return string
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * @param string $data
     * @return TempUser
     */
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @return string
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param \DateTime $regdate
     * @return TempUser
     */
    public function setRegdate($regdate)
    {
        $this->regdate = $regdate;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getRegdate()
    {
        return $this->regdate;
    }

    /**
     * @param string $country
     * @return TempUser
     */
    public function setCountry($country)
    {
        $this->country = $country;
        return $this;
    }
    /**
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

}

