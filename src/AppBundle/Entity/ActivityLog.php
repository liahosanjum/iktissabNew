<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ActivityLog
 *
 * @ORM\Table(name="activity_log")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Repository\ActivityLogRepository")
 */
class ActivityLog
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
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
     * @ORM\Column(name="action_data", type="text", nullable=false)
     */
    private $actionData;

    /**
     * @var integer
     *
     * @ORM\Column(name="action_date", type="integer", nullable=false)
     */
    private $actionDate;

    /**
     * @var string
     *
     * @ORM\Column(name="action_type", type="string", length=255, nullable=false)
     */
    private $actionType;


    /**
     * @var string
     *
     * @ORM\Column(name="old_value", type="string", length=255, nullable=true)
     */
    private $oldValue;

    /**
     * @var string
     *
     * @ORM\Column(name="new_value", type="string", length=255, nullable=true)
     */
    private $newValue;


    /**
     * @var string
     *
     * @ORM\Column(name="source", type="string", length=255, nullable=true)
     */
    private $source;

    /**
     * @var string
     *
     * @ORM\Column(name="browser", type="string", length=255, nullable=true)
     */
    private $browser;

    /**
     * @var string
     *
     * @ORM\Column(name="version", type="string", length=255, nullable=true)
     */
    private $version;








    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set iktCardNo
     *
     * @param integer $iktCardNo
     *
     * @return ActivityLog
     */
    public function setIktCardNo($iktCardNo)
    {
        $this->iktCardNo = $iktCardNo;

        return $this;
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
     * Set actionData
     *
     * @param string $actionData
     *
     * @return ActivityLog
     */
    public function setActionData($actionData)
    {
        $this->actionData = $actionData;

        return $this;
    }

    /**
     * Get actionData
     *
     * @return string
     */
    public function getActionData()
    {
        return $this->actionData;
    }

    /**
     * Set actionDate
     *
     * @param integer $actionDate
     *
     * @return ActivityLog
     */
    public function setActionDate($actionDate)
    {
        $this->actionDate = $actionDate;

        return $this;
    }

    /**
     * Get actionDate
     *
     * @return integer
     */
    public function getActionDate()
    {
        return $this->actionDate;
    }

    /**
     * Set actionType
     *
     * @param string $actionType
     *
     * @return ActivityLog
     */
    public function setActionType($actionType)
    {
        $this->actionType = $actionType;

        return $this;
    }

    /**
     * Get actionType
     *
     * @return string
     */
    public function getActionType()
    {
        return $this->actionType;
    }


    /**
     * Set newValue
     *
     * @param string $newValue
     *
     * @return ActivityLog
     */
    public function setNewValue($newValue)
    {
        $this->newValue = $newValue;

        return $this;
    }

    /**
     * Get newValue
     *
     * @return string
     */
    public function getNewValue()
    {
        return $this->newValue;
    }



    /**
     * Set oldValue
     *
     * @param string $newValue
     *
     * @return ActivityLog
     */
    public function setOldValue($oldValue)
    {
        $this->oldValue = $oldValue;
        return $this;
    }

    /**
     * Get oldValue
     *
     * @return string
     */
    public function getOldValue()
    {
        return $this->oldValue;
    }




    /**
     * Set source
     *
     * @param string $source
     *
     * @return ActivityLog
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
    public function getsource()
    {
        return $this->source;
    }




    /**
     * Set browser
     *
     * @param string $browser
     *
     * @return ActivityLog
     */
    public function setBrowser($browser)
    {
        $this->browser = $browser;
        return $this;
    }

    /**
     * Get browser
     *
     * @return string
     */
    public function getbrowser()
    {
        return $this->browser;
    }



    /**
     * Set version
     *
     * @param string $version
     *
     * @return ActivityLog
     */
    public function setVersion($version)
    {
        $this->version = $version;
        return $this;
    }

    /**
     * Get browser
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->browser;
    }










}
