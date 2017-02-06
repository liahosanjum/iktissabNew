<?php
/**
 * Created by PhpStorm.
 * User: s.aman
 * Date: 2/2/17
 * Time: 1:03 PM
 */

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints\DateTime;

/**
 * Class ContentPage
 * @ORM\Table(name="content_page")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Repository\ContentPageRepository")
 */
class ContentPage
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
     * @ORM\Column(name="page_path", type="string", length=255, nullable=false)
     */
    private $page_path;

    /**
     * @var int
     *
     * @ORM\Column(name="status", type="integer", nullable=true)
     */
    private $status;

    /**
     * @var DateTime
     * @ORM\Column(name="created", type="datetime", nullable=false)
     */
    private $created;

    /**
     * @var DateTime
     * @ORM\Column(name="modified", type="datetime", nullable=true)
     */
    private $modified;

    /**
     * @var ArrayCollection
     */
    private $pages;
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
     * Set status
     *
     * @param integer $status
     *
     * @return ContentPage
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
     * Get status
     *
     * @param DateTime $created
     * @return ContentPage
     */
    public function setCreated($created)
    {
        $this->created = $created;
        return $this;
    }

    /**
     * Get created
     *
     * @return DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set modified
     * @param DateTime $modified
     * @return $this
     */
    public function setModified($modified)
    {
        $this->modified = $modified;
        return $this;
    }

    /**
     * Get modified
     * @return DateTime
     */
    public function getModified()
    {
        return $this->modified;
    }

    /**
     * Set page_path
     * @param string $page_path
     * @return ContentPage
     */
    public function setPagePath($page_path)
    {
        $this->page_path = $page_path;
        return $this;
    }

    /**
     * Get page_path
     * @return string
     */
    public function getPagePath()
    {
        return $this->page_path;
    }

    /**
     * @return ArrayCollection<ContentPageTranslation>
     */
    public function getPages()
    {
        return $this->pages;
    }

    public function __construct()
    {
        $this->pages = new ArrayCollection();
    }

}