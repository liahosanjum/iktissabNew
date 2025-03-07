<?php
/**
 * Created by PhpStorm.
 * User: abdulali
 * Date: 12/1/16
 * Time: 1:01 PM
 */

namespace AppBundle\Security\Admin;

use Symfony\Component\HttpFoundation\RequestStack;
use AppBundle\Security\Admin\IktissabAdmin;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;






class IktissabAdminProvider implements UserProviderInterface
{
    const MAX_COUNT_ATTEMPTS = 5;
    const TIMEOUT = 300;
    private $em;

    /**
     * @var null|\Symfony\Component\HttpFoundation\Request
     */
    private $request;

    /**
     * IktissabAdminProvider constructor.
     * @param EntityManager $entityManager
     * @param RequestStack $request
     */
    public function __construct(EntityManager $entityManager ,RequestStack $request)
    {
        $this->em = $entityManager;
        $this->request = $request->getCurrentRequest();
    }

    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof IktissabAdmin) {
            throw new UnsupportedUserException(
                sprintf('Username or password is incorrect or account has been blocked due to many invalid login attempts')
            );

        }
        return $this->loadUserByUsername($user->getUsername());

        // TODO: Implement refreshUser() method.
    }

    public function canLogin()
    {
        try {
            $loginRepo = $this->em->getRepository("AppBundle:LoginAttempt");
            if($this->request != null || $this->request != "") {
                if ($loginRepo->getCountAttempts($this->request) >= self::MAX_COUNT_ATTEMPTS) {
                    $lastAttemptDate = $loginRepo->getLastAttempt($this->request);
                    $dateAllowLogin  = $lastAttemptDate->modify('+' . self::TIMEOUT . ' second');
                    if ($dateAllowLogin->diff(new \DateTime())->invert === 1) {
                        return false;
                    }
                }else{
                    return true;
                }
            }
            else
            {
                return false;
            }

        } catch (NoResultException $e) {
            return true;
        } catch (NonUniqueResultException $e) {
            return true;
        }

    }
    public function incrementLoginAttempts(){
        if($this->request != null || $this->request != "") {
            $this->em->getRepository('AppBundle:LoginAttempt')->incrementCountAttempts($this->request);
        }
    }

    public function clearLoginAttempts(){
        if($this->request != null || $this->request != "") {
            $this->em->getRepository("AppBundle:LoginAttempt")->clearAttempts($this->request->getClientIp());
        }
    }

    public function loadUserByUsername($username)
    {
        $user = $this->em->createQueryBuilder()
            ->select('u')
            ->from('AppBundle:Admin', 'u')
            ->where('u.email = :email')
            ->setParameter('email', $username)
            ->getQuery()
            ->getOneOrNullResult();
        //echo $user->getQuery()->getSQL();
        if ($user) {
            // return new WebserviceUser($username, $password, $salt, $roles);
            return new IktissabAdmin($user->getEmail(), $user->getPassword(),$user->getId(),
                '', array('ROLE_IKTADMIN'), $user->getRoleName() );
        }
        if($this->request != null || $this->request != "") {
            $this->em->getRepository('AppBundle:LoginAttempt')->incrementCountAttempts($this->request);
        }
        throw new UsernameNotFoundException(
            sprintf('Username or password is incorrect or account has been blocked due to many invalid login attempts')
        );
    }

    public function supportsClass($class)
    {
        return $class === 'AppBundle\Security\Admin\IktissabAdmin';
        // TODO: Implement supportsClass() method.
    }
}