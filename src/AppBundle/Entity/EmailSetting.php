<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Settings
 *
 * @ORM\Table(name="email_setting")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Repository\EmailSettingRepository")
 */
class EmailSetting
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=50)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=50)
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="country", type="string", length=10)
     */
    private $country;

    /**
     * @var string
     *
     * @ORM\Column(name="technical", type="integer", length=1)
     */
    private $technical;

    /**
     * @var int
     *
     * @ORM\Column(name="other", type="integer", length=1)
     */
    private $other;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return EmailSetting
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
     * Set type
     *
     * @param string $type
     *
     * @return EmailSetting
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function gettype()
    {
        return $this->type;
    }

    /**
     * Set country
     *
     * @param integer $country
     *
     * @return EmailSetting
     */
    public function setCountry($country)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * Get country
     *
     * @return integer
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Set technical
     *
     * @param integer $technical
     *
     * @return EmailSetting
     */
    public function setTechnical($technical)
    {
        $this->technical = $technical;

        return $this;
    }

    /**
     * Get technical
     *
     * @return integer
     */
    public function getTechnical()
    {
        return $this->technical;
    }

    /**
     * Set other
     *
     * @param integer $other
     *
     * @return EmailSetting
     */
    public function setOther($other)
    {
        $this->other = $other;
        return $this;
    }

    /**
     * Get other
     *
     * @return integer
     */
    public function getOther()
    {
        return $this->other;
    }

    /**
     *
     */
    
    /*private $brochure;

    public function getBrochure()
    {
        return $this->brochure;
    }

    public function setBrochure($brochure)
    {
        $this->brochure = $brochure;

        return $this;
    }*/
}

