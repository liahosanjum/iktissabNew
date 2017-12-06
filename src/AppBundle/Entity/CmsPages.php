<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
/**
 * CmsPages
 *
 * @ORM\Table(name="cms_pages")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Repository\CmsPagesRepository")
 */
class CmsPages
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
     * @ORM\Column(name="page_content", type="text",  nullable=true)
     */
    private $page_content;

    /**
     * @var string
     *
     * @ORM\Column(name="page_title", type="string", length=255, nullable=true)
     */
    private $page_title;

    /**
     * @var int
     *
     * @ORM\Column(name="status", type="integer", nullable=true)
     */
    private $status;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", nullable=false)
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="country", type="string", nullable=false)
     */
    private $country;

    /**
     * @var string
     *
     * @ORM\Column(name="language", type="string", nullable=false)
     */


    private $language;


    /**
     * @var string
     *
     * @ORM\Column(name="url_path", type="string", length=255, nullable=false)
     */
    private $url_path;


    /**
     * @var string
     *
     * @ORM\Column(name="brochure", type="string", nullable=true)
     * @Assert\Image(mimeTypes={ "image/png","image/jpg","image/jpeg" })
     */
    private $brochure;


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
     * Set pageContent
     *
     * @param string $page_content
     *
     * @return CmsPages
     */
    public function setpageContent($page_content)
    {
        $this->page_content = $page_content;

        return $this;
    }

    /**
     * Get page_content
     *
     * @return string
     */
    public function getpageContent()
    {
        return $this->page_content;
    }



    /**
     * Set pageTitle
     *
     * @param string $page_title
     *
     * @return CmsPages
     */
    public function setpageTitle($page_title)
    {
        $this->page_title = $page_title;

        return $this;
    }

    /**
     * Get pageTitle
     *
     * @return string
     */
    public function getpageTitle()
    {
        return $this->page_title;
    }




    /**
     * Set status
     *
     * @param integer $status
     *
     * @return CmsPages
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
     * Set language
     *
     * @param string $language
     *
     * @return CmsPages
     */
    public function setLanguage($language)
    {
        $this->language = $language;

        return $this;
    }

    /**
     * Get language
     *
     * @return string
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * Set type
     *
     * @param string $type
     *
     * @return CmsPages
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get adesc
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set country
     *
     * @param string $country
     *
     * @return CmsPages
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
     * Set urlPath
     *
     * @param string $url_path
     *
     * @return CmsPages
     */
    public function seturlPath($url_path)
    {
        $this->url_path = $url_path;
        return $this;
    }

    /**
     * Get urlPath
     *
     * @return string
     */
    public function geturlPath()
    {
        return $this->url_path;
    }



    /**
     * Get brochure
     *
     * @return string
     */


    public function getBrochure()
    {
        return $this->brochure;
    }

    /**
     * Set brochure
     *
     * @param string $brochure
     *
     * @return CmsPages
     */
    public function setBrochure($brochure)
    {
        $this->brochure = $brochure;

        return $this;
    }




}

