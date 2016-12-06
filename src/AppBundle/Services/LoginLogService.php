<?php
/**
 * Created by PhpStorm.
 * User: abdulali
 * Date: 12/4/16
 * Time: 11:52 AM
 */
namespace AppBundle\Services;

use AppBundle\Entity\LoginLog;
use Doctrine\ORM\EntityManager;

class LoginLogService
{
    private  $em;
    public function __construct(EntityManager $em){
        $this->em = $em;
    }

    public function logEvent($ikt_card_no){
        $loginLog = new LoginLog();
        $loginLog->setIktCardNo($ikt_card_no);
        $loginLog->setLoginIp($this->container->get('request_stack')->getCurrentRequest()->getClientIp());
        $loginLog->setLoginDate(time());
        $this->em->persist($loginLog);
        $this->em->flush();
        var_dump($loginLog);
    }
}