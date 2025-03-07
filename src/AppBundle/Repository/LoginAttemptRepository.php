<?php

namespace AppBundle\Repository;

use AppBundle\Entity\LoginAttempt;
use Symfony\Component\HttpFoundation\Request;

/**
 * LoginAttemptRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class LoginAttemptRepository extends \Doctrine\ORM\EntityRepository
{
    const WATCH_PERIOD = 300;

/**
* @param \Symfony\Component\HttpFoundation\Request $request
* @param \Symfony\Component\Security\Core\Exception\AuthenticationException $exception
*/
    public function incrementCountAttempts(Request $request)
    {
        if (!$this->hasIp($request)) {
            return;
        }
        $model = new LoginAttempt();
        $model->setIp($request->getClientIp());
        $data = [
            'clientIp'  => $request->getClientIp(),
            'sessionId' => $request->getSession()->getId()
        ];
        $username = $request->get('_username');
        if (!empty($username)) {
            $data['user'] = $username;
        }
        $model->setData($data);
        $this->_em->persist($model);
        $this->_em->flush($model);

    }

    /**
     * @param Request $request
     * @return int|mixed
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getCountAttempts(Request $request)
    {
        
        if (!$this->hasIp($request)) {
            return 0;
        }

        $watchDate = new \DateTime();
        $watchDate->modify('-' . self::WATCH_PERIOD . ' second');
        return $this->createQueryBuilder('attempt')
            ->select('COUNT(attempt.id)')
            ->where('attempt.ip = :ip')
            ->andWhere('attempt.createdAt > :createdAt')
            ->setParameters(array(
                'ip' => $request->getClientIp(),
                'createdAt' => $watchDate
            ))
            ->getQuery()
            ->getSingleScalarResult();
        

    }

    /**
     * @param Request $request
     * @return \DateTime|false
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getLastAttempt(Request $request)
    {
        if (!$this->hasIp($request)) {
            return false;
        }

        $lastAttempt = $this->createQueryBuilder('attempt')
            ->where('attempt.ip = :ip')
            ->orderBy('attempt.createdAt', 'DESC')
            ->setParameters(array(
                'ip' => $request->getClientIp()
            ))
            ->getQuery()
            ->setMaxResults(1)
            ->getOneOrNullResult();
        if (!empty($lastAttempt)) {
            return $lastAttempt->getCreatedAt();
        }
        return false;
    }

    /**
     * @param string $ip
     * @return integer
     */
    public function clearAttempts($ip)
    {
        return $this->getEntityManager()
            ->createQuery('DELETE FROM ' . $this->getClassMetadata()->name . ' attempt WHERE attempt.ip = :ip')
            ->execute(array('ip' => $ip));
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return boolean
     */
    protected function hasIp(Request $request)
    {
        return $request->getClientIp() != '';
    }
}
