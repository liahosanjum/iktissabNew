<?php
/**
 * Created by PhpStorm.
 * User: abdulali
 * Date: 12/1/16
 * Time: 9:29 AM
 */
namespace AppBundle\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class LoginLog
 * @ORM\Entity()
 * @ORM\Table(name="login_log")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Repository\LoginLogRepository")
 */
class LoginLog{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $ikt_card_no;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $login_ip;
    /**
     * @ORM\Column(type="integer")
     */
    private $login_date;



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
     * @return LoginLogs
     */
    public function setIktCardNo($iktCardNo)
    {
        $this->ikt_card_no = $iktCardNo;

        return $this;
    }

    /**
     * Get iktCardNo
     *
     * @return integer
     */
    public function getIktCardNo()
    {
        return $this->ikt_card_no;
    }

    /**
     * Set loginIp
     *
     * @param string $loginIp
     *
     * @return LoginLogs
     */
    public function setLoginIp($loginIp)
    {
        $this->login_ip = $loginIp;

        return $this;
    }

    /**
     * Get loginIp
     *
     * @return string
     */
    public function getLoginIp()
    {
        return $this->login_ip;
    }

    /**
     * Set loginDate
     *
     * @param integer $loginDate
     *
     * @return LoginLogs
     */
    public function setLoginDate($loginDate)
    {
        $this->login_date = $loginDate;

        return $this;
    }

    /**
     * Get loginDate
     *
     * @return integer
     */
    public function getLoginDate()
    {
        return $this->login_date;
    }
}
