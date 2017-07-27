<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * NotificationSubscription
 *
 * @ORM\Table(name="notification_subscription")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Repository\NotificationSubscriptionRepository")
 */
class NotificationSubscription
{
    const SUBSCRIBE_YES = 'y';
    const SUBSCRIBE_NO = 'n';

    /**
     * @var string
     * @ORM\Id
     * @ORM\Column(name="ikt_card", type="string", length=8)
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $iktCard;

    /**
     * @var string
     *
     * @ORM\Column(name="email_subscription", type="string", length=1)
     */
    private $emailSubscription;

    /**
     * @var string
     *
     * @ORM\Column(name="sms_subscription", type="string", length=1)
     */
    private $smsSubscription;

    /**
     * @var string
     *
     * @ORM\Column(name="push_subscription", type="string", length=1)
     */
    private $pushSubscription;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime")
     */
    private $created;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="modified", type="datetime")
     */
    private $modified;


    /**
     * Set iktCard
     *
     * @param integer $iktCard
     *
     * @return NotificationSubscription
     */
    public function setIktCard($iktCard)
    {
        $this->iktCard = $iktCard;

        return $this;
    }

    /**
     * Get iktCard
     *
     * @return int
     */
    public function getIktCard()
    {
        return $this->iktCard;
    }

    /**
     * Set emailSubscription
     *
     * @param string $emailSubscription
     *
     * @return NotificationSubscription
     */
    public function setEmailSubscription($emailSubscription)
    {
        $this->emailSubscription = $this->checkSubscriptionValue($emailSubscription);

        return $this;
    }

    /**
     * Get emailSubscription
     *
     * @return string
     */
    public function getEmailSubscription()
    {
        return $this->emailSubscription;
    }

    /**
     * Set smsSubscription
     *
     * @param string $smsSubscription
     *
     * @return NotificationSubscription
     */
    public function setSmsSubscription($smsSubscription)
    {
        $this->smsSubscription = $this->checkSubscriptionValue($smsSubscription);

        return $this;
    }

    /**
     * Get smsSubscription
     *
     * @return string
     */
    public function getSmsSubscription()
    {
        return $this->smsSubscription;
    }

    /**
     * Set pushSubscription
     *
     * @param string $pushSubscription
     *
     * @return NotificationSubscription
     */
    public function setPushSubscription($pushSubscription)
    {
        $this->pushSubscription = $this->checkSubscriptionValue($pushSubscription);

        return $this;
    }

    /**
     * Get pushSubscription
     *
     * @return string
     */
    public function getPushSubscription()
    {
        return $this->pushSubscription;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     *
     * @return NotificationSubscription
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
     * @return NotificationSubscription
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
     * @param $value
     * @return string
     */
    private function checkSubscriptionValue($value){
        if(!in_array($value, [NotificationSubscription::SUBSCRIBE_YES, NotificationSubscription::SUBSCRIBE_NO]))
            return NotificationSubscription::SUBSCRIBE_NO;

        return $value;
    }
}

