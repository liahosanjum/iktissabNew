<?php

namespace AppBundle\Entity\Repository;
use AppBundle\Entity\NotificationSubscriptionDevices;

/**
 * NotificationSubscriptionDevicesRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class NotificationSubscriptionDevicesRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * @param $iktCard
     * @return int
     */
    public function GetNextSerial($iktCard)
    {
        $max = $this->_em->createQueryBuilder()->select("MAX(nsd.serial)")
            ->from("AppBundle:NotificationSubscriptionDevices", "nsd")
            ->where("nsd.IktCard = :iktCard")
            ->setParameter("iktCard", $iktCard)
            ->getQuery()
            ->getSingleScalarResult();

        if($max == null) $max = 0;
        $max++;
        return $max;

    }

    /**
     * @param $device
     * @param $uid
     * @return mixed|NotificationSubscriptionDevices
     */
    public function  GetDeviceByDeviceAndUid($device, $uid){
        return $this->_em
            ->createQueryBuilder()
            ->from("AppBundle:NotificationSubscriptionDevices", 'd')
            ->where('d.device = :device')
            ->andWhere('d.deviceUid = :deviceUid')
            ->setParameter('device', $device)
            ->setParameter('deviceUid', $uid)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
