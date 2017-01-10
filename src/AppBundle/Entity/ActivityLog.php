<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ActivityLog
 *
 * @ORM\Table(name="activity_log")
 * @ORM\Entity
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
}
