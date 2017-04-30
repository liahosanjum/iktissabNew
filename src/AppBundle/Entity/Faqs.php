<?php

namespace AppBundle\Entity;

use Captcha\Bundle\CaptchaBundle\Validator\Constraints\ValidCaptcha;
// use Doctrine\DBAL\Exception\InvalidArgumentException;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Faqs
 *
 * @ORM\Table(name="faq")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Repository\FaqsRepository")
 */
class Faqs
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
     * @Assert\NotBlank(message="This Field is required")
     * @Assert\Email(message="Invalid email address")
     */
    private $email;


    /**
     * @var string
     *
     * @ORM\Column(name="mobile", type="string", length=16)
     * @Assert\NotBlank(message="This Field is required")
     * @Assert\Regex(pattern="/^\d{10,16}$/", message="Invalid mobile number")
     */
    private $mobile;



    /**
     * @var string
     *@ORM\Column(name="question", type="string", length=255)
     * @Assert\NotBlank(message="This Field is required")
     */
    private $question;



    /**
     * @var string
     *
     * @ORM\Column(name="country", type="string", length=50)
     */
    private $country;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime")
     */
    private $created;

    /**
     * @var string
     *
     * @ORM\Column(name="user_ip", type="string", length=100)
     */
    private $user_ip;




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
     * Set mobile
     *
     * @param string $mobile
     *
     * @return Faqs
     */
    public function setMobile($mobile)
    {
        $this->mobile = $mobile;

        return $this;
    }

    /**
     * Get mobile
     *
     * @return string
     */
    public function getMobile()
    {
        return $this->mobile;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return Faqs
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
     * Set question
     *
     * @param string $question
     *
     * @return Faqs
     */
    public function setQuestion($question)
    {
        $this->question = $question;

        return $this;
    }

    /**
     * Get question
     *
     * @return string
     */
    public function getQuestion()
    {
        return $this->question;
    }





    /**
     * Set country
     *
     * @param string $country
     *
     * @return Faqs
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
     * Set created
     *
     * @param \DateTime $created
     *
     * @return Faqs
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }



    /**
     * triggers on insert
     * @ORM\PrePersist
     */
    public function OnPrePersist(){
        $this->created = new \DateTime('now');
    }


    /**
     * Set user_ip
     *
     * @param string $user_ip
     *
     * @return Faqs
     */
    public function setUser_ip($user_ip)
    {
        $this->user_ip = $user_ip;

        return $this;
    }

    /**
     * Get user_ip
     *
     * @return string
     */
    public function getUser_ip()
    {
        return $this->user_ip;
    }



    /**
     * @Assert\NotBlank(message="This field is required")
     * @ValidCaptcha(message="Invalid captcha code")
     */
    private $captchaCode;

    /**
     * @return mixed
     */
    public function getCaptchaCode()
    {
        return $this->captchaCode;
    }

    /**
     * @param mixed $captchaCode
     */
    public function setCaptchaCode($captchaCode)
    {
        $this->captchaCode = $captchaCode;
        return $this;
    }

}

