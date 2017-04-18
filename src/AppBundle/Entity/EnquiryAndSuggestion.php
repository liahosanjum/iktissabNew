<?php

namespace AppBundle\Entity;

use Captcha\Bundle\CaptchaBundle\Validator\Constraints\ValidCaptcha;
use Doctrine\DBAL\Exception\InvalidArgumentException;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * EnquiryAndSuggestion
 *
 * @ORM\Table(name="enquiry_and_suggestion")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Repository\EnquiryAndSuggestionRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class EnquiryAndSuggestion
{

    const TECHNICAL_SUPPORT = "T";
    const SUGGESTION        = "S";
    const COMPLAINT         = "C";
    const ENQUIRY           = "E";

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
     * @ORM\Column(name="name", type="string", length=100)
     * @Assert\NotBlank(message="This Field is required")
     *
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="job", type="string", length=200)
     * @Assert\NotBlank(message="This Field is required")
     *
     */
    private $job;

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
     *
     * @ORM\Column(name="email", type="string", length=255)
     * @Assert\NotBlank(message="This Field is required")
     * @Assert\Email(message="Invalid email address")
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="reason", type="string", length=1)
     * @Assert\NotBlank(message="This Field is required")
     * @Assert\Choice(choices={EnquiryAndSuggestion::TECHNICAL_SUPPORT, EnquiryAndSuggestion::COMPLAINT, EnquiryAndSuggestion::ENQUIRY, EnquiryAndSuggestion::SUGGESTION }, message="Invalid reason")
     */
    private $reason;

    /**
     * @var string
     *
     * @ORM\Column(name="comments", type="string", length=1000)
     * @Assert\NotBlank(message="This Field is required")
     */
    private $comments;

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
     * @var \DateTime
     *
     * @ORM\Column(name="modified", type="datetime", nullable=true)
     */
    private $modified;
    /**
     * @var string
     *
     * @ORM\Column(name="user_ip", type="string", length=50)
     */
    private $user_ip;

    /**
     * @var string
     *
     * @ORM\Column(name="source", type="string", length=100)
     */
    private $source;


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
     * Set name
     *
     * @param string $name
     *
     * @return EnquiryAndSuggestion
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set job
     *
     * @param string $job
     *
     * @return EnquiryAndSuggestion
     */
    public function setJob($job)
    {
        $this->job = $job;

        return $this;
    }

    /**
     * Get job
     *
     * @return string
     */
    public function getJob()
    {
        return $this->job;
    }

    /**
     * Set mobile
     *
     * @param string $mobile
     *
     * @return EnquiryAndSuggestion
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
     * @return EnquiryAndSuggestion
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
     * Set reason
     *
     * @param string $reason
     *
     * @return EnquiryAndSuggestion
     * @throws InvalidArgumentException
     */
    public function setReason($reason)
    {
        if(!in_array($reason,  array(self::COMPLAINT, self::ENQUIRY, self::SUGGESTION, self::TECHNICAL_SUPPORT)))
            throw  new InvalidArgumentException();
        $this->reason = $reason;

        return $this;
    }

    /**
     * Get reason
     *
     * @return string
     */
    public function getReason()
    {
        return $this->reason;
    }

    /**
     * Set comments
     *
     * @param string $comments
     *
     * @return EnquiryAndSuggestion
     */
    public function setComments($comments)
    {
        $this->comments = $comments;

        return $this;
    }

    /**
     * Get comments
     *
     * @return string
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * Set country
     *
     * @param string $country
     *
     * @return EnquiryAndSuggestion
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
     * Set user_ip
     *
     * @param string $user_ip
     *
     * @return EnquiryAndSuggestion
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
     * Set created
     *
     * @param \DateTime $created
     *
     * @return EnquiryAndSuggestion
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
     * Set modified
     *
     * @param \DateTime $modified
     *
     * @return EnquiryAndSuggestion
     */
    public function setModified($modified)
    {
        $this->modified = $modified;

        return $this;
    }

    /**
     * Get modified
     *
     * @return \DateTime
     */
    public function getModified()
    {
        return $this->modified;
    }

    /**
     * triggers on insert
     * @ORM\PrePersist
     */
    public function OnPrePersist(){
        $this->created = new \DateTime('now');
    }

    /**
     * @ORM\PreUpdate
     */
    public function OnPreUpdate(){
        $this->modified = new \DateTime('now');
    }


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

    /**
     * Set source
     *
     * @param string $source
     * @return EnquiryAndSuggestion
     */
    public function setSource($source)
    {
        $this->source = $source;
        return $this;
    }

    /**
     * Get source
     *
     * @return string
     */
    public function getSource()
    {
        return $this->source;
    }

}

