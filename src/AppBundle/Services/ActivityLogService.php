<?php
/**
 * Created by PhpStorm.
 * User: abdulali
 * Date: 12/4/16
 * Time: 11:52 AM
 */
namespace AppBundle\Services;

use AppBundle\Entity\ActivityLog;
use AppBundle\Entity\LoginLog;
use AppBundle\AppConstant;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ActivityLogService
{
    private  $em;
    private $containerInterface;
    public function __construct(EntityManager $em, ContainerInterface $containerInterface){
        $this->em = $em;
        $this->containerInterface = $containerInterface;
    }

    public function logLoginEvent($ikt_card_no){
        $activityLog = new ActivityLog();
        $activityLog->setIktCardNo($ikt_card_no);
        $actionData = array('login_ip'=>$this->containerInterface->get('request_stack')->getCurrentRequest()->getClientIp());
        $activityLog->setActionData(serialize($actionData));
        $activityLog->setActionDate(time());
        $activityLog->setActionType(AppConstant::ACTIVITY_LOGIN);
        $this->em->persist($activityLog);
        // update the lastlogin in user table
        $this->em->flush();
    }
    public function logLogoutEvent($ikt_card_no){
        $activityLog = new ActivityLog();
        $activityLog->setIktCardNo($ikt_card_no);
        $activityLog->setActionData('');
        $activityLog->setActionDate(time());
        $activityLog->setActionType(AppConstant::ACTIVITY_LOGOUT);
        $this->em->persist($activityLog);
        $this->em->flush();
    }
}