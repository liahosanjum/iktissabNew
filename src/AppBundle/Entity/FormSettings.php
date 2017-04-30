<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FormSettings
 *
 * @ORM\Table(name="form_setting")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Repository\FormSettingsRepository")
 */
class FormSettings
{
    const Inquiries_And_Suggestion = "Inquiries And Suggestion";
    const Faqs_Form = "Faqs Form";

    const SUBMISSION_EVERY_HOUR = 1;
    const SUBMISSION_EVERY_DAY = 24;
    const SUBMISSION_EVERY_WEEK = 168;
    const SUBMISSION_EVERY_MONTH = 720;

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
     * @ORM\Column(name="status", type="string", length=1)
     */
    private $status;

    /**
     * @var string
     *
     * @ORM\Column(name="formtype", type="string", length=255)
     */
    private $formtype;

    /**
     * @var string
     *
     * @ORM\Column(name="country", type="string", length=50)
     */
    private $country;


    /**
     * @var string
     *
     * @ORM\Column(name="submissions", type="string", length=2)
     */
    private $submissions;

    /**
     * @var integer
     *
     * @ORM\Column(name="limit_to", type="integer", length=3)
     */
    private $limitto;


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
     * Set status
     * @param string $status
     * @return FormSettings
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set formtype
     *
     * @param string $formtype
     *
     * @return FormSettings
     */
    public function setFormType($formtype)
    {
        $this->formtype = $formtype;

        return $this;
    }

    /**
     * Get formtype
     *
     * @return string
     */
    public function getFormType()
    {
        return $this->formtype;
    }

    /**
     * Set country
     *
     * @param integer $country
     *
     * @return Settings
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
     * Set submissions
     *
     * @param integer $submissions
     * @return FormSettings
     */
    public function setSubmissions($submissions)
    {
        $this->submissions = $submissions;
        return $this;
    }

    /**
     * Get submissions
     *
     * @return integer
     */
    public function getSubmissions()
    {
        return $this->submissions;
    }

    /**
     * Set limito
     *
     * @param integer $limitto
     * @return FormSettings
     */
    public function setLimitto($limitto)
    {
        $this->limitto = $limitto;
        return $this;
    }

    /**
     * Get limitto
     *
     * @return integer
     */
    public function getLimitto()
    {
        return $this->limitto;
    }




}

