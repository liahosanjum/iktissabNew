<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Nationality
 *
 * @ORM\Table(name="nationality")
 * @ORM\Entity
 */
class Nationality
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
     * @ORM\Column(name="adesc", type="text", nullable=true)
     */
    private $adesc;

    /**
     * @var string
     *
     * @ORM\Column(name="edesc", type="string", length=255, nullable=true)
     */
    private $edesc;



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
     * Set adesc
     *
     * @param string $adesc
     *
     * @return Nationality
     */
    public function setAdesc($adesc)
    {
        $this->adesc = $adesc;

        return $this;
    }

    /**
     * Get adesc
     *
     * @return string
     */
    public function getAdesc()
    {
        return $this->adesc;
    }

    /**
     * Set edesc
     *
     * @param string $edesc
     *
     * @return Nationality
     */
    public function setEdesc($edesc)
    {
        $this->edesc = $edesc;

        return $this;
    }

    /**
     * Get edesc
     *
     * @return string
     */
    public function getEdesc()
    {
        return $this->edesc;
    }
}
