<?php
/**
 * Created by PhpStorm.
 * User: abdulali
 * Date: 2/22/17
 * Time: 8:39 AM
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Subscription
 *
 * @ORM\Table(name="subscription")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks()
 */
class Subscription
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
     * @var string
     *
     * @ORM\Column(name="subs_val", type="text", nullable=true)
     */
    private $subsVal;

    /**
     * @var string
     *
     * @ORM\Column(name="subs_type", type="text", nullable=false)
     */
    private $subsType;
    /**
     * @var integer
     *
     * @ORM\Column(name="created", type="integer", length=15, nullable=true)
     */
    private $created;

    /**
     * Triggered on insert
     * @ORM\PrePersist
     */
    public function onPrePersist()
    {
        $this->created = date_format(new \DateTime('now'), 'U');
    }


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
     * Set subsVal
     *
     * @param string $subsVal
     *
     * @return Subscription
     */
    public function setSubsVal($subsVal)
    {
        $this->subsVal = $subsVal;

        return $this;
    }

    /**
     * Get subsVal
     *
     * @return string
     */
    public function getSubsVal()
    {
        return $this->subsVal;
    }

    /**
     * Set subsType
     *
     * @param string $subsType
     *
     * @return Subscription
     */
    public function setSubsType($subsType)
    {
        $this->subsType = $subsType;

        return $this;
    }

    /**
     * Get subsType
     *
     * @return string
     */
    public function getSubsType()
    {
        return $this->subsType;
    }

    /**
     * Set created
     *
     * @param integer $created
     *
     * @return Subscription
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return integer
     */
    public function getCreated()
    {
        return $this->created;
    }
}
