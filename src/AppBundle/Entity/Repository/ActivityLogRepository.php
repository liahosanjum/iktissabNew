<?php
/**
 * Created by PhpStorm.
 * User: abdulali
 * Date: 3/7/17
 * Time: 9:34 AM
 */

namespace AppBundle\Entity\Repository;


use AppBundle\AppBundle;
use Doctrine\ORM\Query\Expr\Join;

class ActivityLogRepository extends \Doctrine\ORM\EntityRepository
{

    public function searchActivityLog($ikt,$action,$email)
    {
        $builder = $this->_em->createQueryBuilder()->select(array('a','u'))
            ->from('AppBundle:ActivityLog', 'a')
            ->leftJoin('AppBundle:User', 'u', Join::WITH, 'u.iktCardNo = a.iktCardNo ' )
            ->orderBy('a.actionDate', 'desc');
        if ($ikt != '') $builder->andWhere('a.iktCardNo = :ikt')->setParameter('ikt', $ikt);
        if ($action != '') $builder->andWhere('a.actionType like  :action')->setParameter('action', '%'.$action.'%');
        if ($email != '') $builder->andWhere('u.email like  :email')->setParameter('email', '%'.$email.'%');
        return $builder
            ->getQuery()
            ->setHydrationMode(3);
    }
}