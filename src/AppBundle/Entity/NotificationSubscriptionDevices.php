<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * NotificationSubscriptionDevices
 *
 * @ORM\Table(name="notification_subscription_devices")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Repository\NotificationSubscriptionDevicesRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class NotificationSubscriptionDevices
{
    /**
     * @var int
     *
     * @ORM\Column(name="serial", type="integer")
     * @ORM\Id
     */
    private $serial;

    /**
     * @var string
     *
     * @ORM\Column(name="ikt_card", type="string", length=8)
     * @ORM\Id
     */
    private $iktCard;

    /**
     * @var string
     *
     * @ORM\Column(name="device", type="string", length=20)
     */
    private $device;


    /**
     * @var string
     *
     * @ORM\Column(name="device_token", type="string", length=500)
     */
    private $deviceToken;

    /**
     * @var string
     *
     * @ORM\Column(name="device_uid", type="string", length=200)
     */
    private $deviceUID;

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
     * Set serial
     *
     * @param int $serial
     * @return NotificationSubscriptionDevices
     */
    public function setSerial($serial)
    {
        $this->serial = $serial;
        return $this;
    }

    /**
     * Get serial
     *
     * @return int
     */
    public function getSerial()
    {
        return $this->serial;
    }

    /**
     * Set iktCard
     *
     * @param string $iktCard
     *
     * @return NotificationSubscriptionDevices
     */
    public function setIktCard($iktCard)
    {
        $this->iktCard = $iktCard;

        return $this;
    }

    /**
     * Get iktCard
     *
     * @return string
     */
    public function getIktCard()
    {
        return $this->iktCard;
    }

    /**
     * Set device
     *
     * @param string $device
     *
     * @return NotificationSubscriptionDevices
     */
    public function setDevice($device)
    {
        $this->device = $device;

        return $this;
    }

    /**
     * Get device
     *
     * @return string
     */
    public function getDevice()
    {
        return $this->device;
    }

    /**
     * Set deviceToken
     *
     * @param string $deviceToken
     *
     * @return NotificationSubscriptionDevices
     */
    public function setDeviceToken($deviceToken)
    {
        $this->deviceToken = $deviceToken;

        return $this;
    }

    /**
     * Get deviceToken
     *
     * @return string
     */
    public function getDeviceToken()
    {
        return $this->deviceToken;
    }

    /**
     * Set deviceUID
     *
     * @param string $deviceUID
     *
     * @return NotificationSubscriptionDevices
     */
    public function setDeviceUID($deviceUID)
    {
        $this->deviceUID = $deviceUID;

        return $this;
    }

    /**
     * Get deviceUID
     *
     * @return string
     */
    public function getDeviceUID()
    {
        return $this->deviceUID;
    }
    /**
     * Set created
     *
     * @param \DateTime $created
     *
     * @return NotificationSubscriptionDevices
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
     * @return NotificationSubscriptionDevices
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
     * @ORM\PrePersist
     */
    public function OnPrePersist(){
        $this->created = new \DateTime('now');
    }

    /**
     * @ORM\PreUpdate
     */
    public function  OnPreUpdate(){
        $this->modified = new \DateTime('now');
    }
}

