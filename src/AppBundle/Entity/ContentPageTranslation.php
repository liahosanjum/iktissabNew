<?php
/**
 * Created by PhpStorm.
 * User: s.aman
 * Date: 2/2/17
 * Time: 1:03 PM
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
/**
 * Class ContentPageTranslation
 * @ORM\Table(name="content_page_translation")
 * @ORM\Entity
 */
class ContentPageTranslation
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
     * @var ContentPage
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\ContentPage")
     * @ORM\JoinColumns({
     *     @ORM\JoinColumn(name="content_page_id", referencedColumnName="id")
     *     })
     */
    private $contentPage;
    
    /**
     * @var string
     * @ORM\Column(name="title", type="string", length=255, nullable=true)
     */
    private $title;

    /**
     * @var string
     * @ORM\Column(name="content", type="string", length=1000, nullable=true)
     */
    private $content;

    /**
     * @var string
     */
    private $language;
    
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
     * Set title
     *
     * @param string $title
     *
     * @return ContentPageTranslation
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set content
     *
     * @param string $content
     *
     * @return ContentPageTranslation
     */
    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }

    /**
     * Get content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set contentPage
     * @param $contentPage
     * @return $this
     */
    public function setContentPage($contentPage)
    {
        $this->contentPage = $contentPage;
        return $this;
    }

    /**
     * Get contentPage
     * @return ContentPage
     */
    public function getContentPage()
    {
        return $this->contentPage;
    }

    /**
     * @param $language
     * @return ContentPageTranslation
     */
    public function setLanguage($language)
    {
        $this->language = $language;
        return $this;
    }

    /**
     * @return string
     */
    public function getLanguage()
    {
        return $this->language;
    }

}