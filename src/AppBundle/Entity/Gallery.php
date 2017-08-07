<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Gallery
 *
 * @ORM\Table(name="gallery")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Repository\GalleryRepository")
 */
class Gallery
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
     * @ORM\Column(name="adesc", type="string",  length=255,  nullable=true)
     */
    private $adesc;

    /**
     * @var string
     *
     * @ORM\Column(name="edesc", type="string", length=255,  nullable=true)
     */
    private $edesc;

    /**
     * @var string
     *
     * @ORM\Column(name="atitle", type="string", length=255, nullable=true)
     */
    private $atitle;

    /**
     * @var string
     *
     * @ORM\Column(name="etitle", type="string", length=255, nullable=true)
     */
    private $etitle;

    /**
     * @var int
     *
     * @ORM\Column(name="status", type="integer", nullable=true)
     */
    private $status;



    /**
     * @var string
     *
     * @ORM\Column(name="image", type="string", nullable=false)
     * @Assert\Image(mimeTypes={ "image/png","image/jpg","image/jpeg" })
     */
    private $image;

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
     * Set adesc
     *
     * @param string $adesc
     *
     * @return string
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
     * @return string
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

    /**
     * Set atitle
     *
     * @param string $atitle
     *
     * @return string
     */
    public function setAtitle($atitle)
    {
        $this->atitle = $atitle;

        return $this;
    }

    /**
     * Get atitle
     *
     * @return string
     */
    public function getAtitle()
    {
        return $this->atitle;
    }

    /**
     * Set etitle
     *
     * @param string $etitle
     *
     * @return string
     */
    public function setEtitle($etitle)
    {
        $this->etitle = $etitle;

        return $this;
    }

    /**
     * Get etitle
     *
     * @return string
     */
    public function getEtitle()
    {
        return $this->etitle;
    }

    /**
     * Set status
     *
     * @param integer $status
     *
     * @return Gallery
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }



    /**
     * Get status
     *
     * @return integer
     */
    public function getStatus()
    {
        return $this->status;
    }


    /**
     * Get image
     *
     * @return string
     */

    public function getImage()
    {
        return $this->image;
    }

    /**
     * Set image
     *
     * @param string $image
     *
     * @return Gallery
     */
    public function setImage($image)
    {
        $this->image = $image;
        return $this;
    }




}

