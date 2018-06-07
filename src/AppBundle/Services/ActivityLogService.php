<?php
/**
 * Created by PhpStorm.
 * User: abdulali
 * Date: 12/4/16
 * Time: 11:52 AM
 */
namespace AppBundle\Services;

use AppBundle\AppConstant;
use AppBundle\Entity\ActivityLog;
use AppBundle\Entity\LoginLog;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ActivityLogService
{
    private $em;
    private $containerInterface;

    public function __construct(EntityManager $em, ContainerInterface $containerInterface)
    {
        $this->em = $em;
        $this->containerInterface = $containerInterface;
    }

    public function logEvent($actionType, $iktCardNo ,$data, $old_value = "" , $new_value = "")
    {
        $activityLog = new ActivityLog();
        $activityLog->setActionType($actionType);
        $activityLog->setOldValue($old_value);
        $activityLog->setNewValue($new_value);
        $activityLog->setSource('W');
        $browserAgent = $this->getBrowserInfo();
        $activityLog->setBrowser($browserAgent);
        $activityLog->setVersion('');
        $activityLog->setActionData(serialize($data));
        if($iktCardNo == "" || $iktCardNo == null){
            $iktCardNo = 0;
        }
        $activityLog->setIktCardNo($iktCardNo);
        $date = date('Y-m-d H:i:s');
        $activityLog->setActionDate($date);
        $this->em->persist($activityLog);
        // update the lastlogin in user table
        $this->em->flush();
    }

    public function logLoginEvent($ikt_card_no)
    {
        $activityLog = new ActivityLog();
        $activityLog->setIktCardNo($ikt_card_no);
        $actionData = array('login_ip' => $this->containerInterface->get('request_stack')->getCurrentRequest()->getClientIp());
        $activityLog->setActionData(serialize($actionData));
        $activityLog->setSource('W');
        $browserAgent = $this->getBrowserInfo();
        $activityLog->setBrowser($browserAgent);
        $activityLog->setVersion('');
        $date = date('Y-m-d H:i:s');
        $activityLog->setActionDate($date);
        $activityLog->setActionType(AppConstant::ACTIVITY_LOGIN);
        $this->em->persist($activityLog);
        // update the lastlogin in user table
        $this->em->flush();
    }

    public function getBrowserInfo()
    {
        return $_SERVER['HTTP_USER_AGENT'];
    }

    public function logLogoutEvent($ikt_card_no, $type = null)
    {
        $activityLog = new ActivityLog();
        $activityLog->setIktCardNo($ikt_card_no);
        $activityLog->setActionData('');
        $activityLog->setSource('W');
        $browserAgent = $this->getBrowserInfo();
        $activityLog->setBrowser($browserAgent);
        $activityLog->setVersion('');
        $date = date('Y-m-d H:i:s');
        $activityLog->setActionDate($date);
        $activityLog->setActionType(AppConstant::ACTIVITY_LOGOUT.$type);
        $this->em->persist($activityLog);
        $this->em->flush();
    }
}