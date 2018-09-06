<?php
/**
 * Created by PhpStorm.
 * User: Salem Khan
 * Date: 12/1/16
 * Time: 1:01 PM
 */

namespace AppBundle\Security\User;

use AppBundle\AppConstant;
use Doctrine\ORM\EntityManager;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use AppBundle\Controller\Common\FunctionsController;


class IktissabUserProvider implements UserProviderInterface
{
    const MAX_COUNT_ATTEMPTS = 10;
    const TIMEOUT = 300;
    private $em;

    /**
     * @var null|\Symfony\Component\HttpFoundation\Request
     */
    private $request;

    /**
     * IktissabUserProvider constructor.
     * @param EntityManager $entityManager
     * @param RequestStack $request
     */
    public function __construct(EntityManager $entityManager, RequestStack $request)
    {
        $this->em = $entityManager;
        $this->request = $request->getCurrentRequest();
    }

    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof IktissabUser) {
            throw new UnsupportedUserException(
                sprintf('Username or password is incorrect or account has been blocked due to many invalid login attempts')
            );
        }
        return $this->loadUserByUsername($user->getUsername());
        // TODO: Implement refreshUser() method.
    }
    public function canLogin()
    {
        try
        {
            $loginRepo = $this->em->getRepository("AppBundle:LoginAttempt");
            // code added by sohail for null stactrequest
            if($this->request != null || $this->request != "") {
                if ($loginRepo->getCountAttempts($this->request) >= self::MAX_COUNT_ATTEMPTS) {
                    $lastAttemptDate = $loginRepo->getLastAttempt($this->request);
                    $dateAllowLogin = $lastAttemptDate->modify('+' . self::TIMEOUT . ' second');
                    if ($dateAllowLogin->diff(new \DateTime())->invert === 1) {
                        return false;
                    }
                }
                return true;
            } else {
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
        $status = 1;
        $verifyemail = 1;
        $commFunct = new FunctionsController();
        $commFunct->setContainer($this->container);
        $email_verificaton_for_registration = $commFunct->email_vrfy($this->em);
        //if(AppConstant::EMAIL_ADDRESS_VERIFICATION == 1)
        if($email_verificaton_for_registration == 1)
        {
            $user = $this->em->createQueryBuilder()
                ->select('u')
                ->from('AppBundle:User', 'u')
                ->where('u.email = :email')
                ->andWhere('u.status = :status')
                ->andWhere('u.verifyemail = :verifyemail')
                ->setParameter('email', $username)
                ->setParameter('status', $status)
                ->setParameter('verifyemail', $verifyemail)
                ->getQuery()
                ->getOneOrNullResult();
        }
        else
        {
            $user = $this->em->createQueryBuilder()
                ->select('u')
                ->from('AppBundle:User', 'u')
                ->where('u.email = :email')
                ->andWhere('u.status = :status')
                ->setParameter('email', $username)
                ->setParameter('status', $status)
                ->getQuery()
                ->getOneOrNullResult();
        }
        //print_r($user);exit;
            //  echo $user->getQuery()->getSQL();
        if ($user) {
            return new IktissabUser(
                $user->getEmail(),
                $user->getPassword(),
                $user->getIktCardNo(),
                $user->getCountry(),
                '',
                array('ROLE_IKTUSER'));
            // TODO: Implement loadUserByUsername() method.
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
        return $class === 'AppBundle\Security\User\IktissabUser';
        // TODO: Implement supportsClass() method.
    }
    public function email_vrfy()
    {
        $conn = $this->em->getConnection();
        $stm_res = $conn->prepare("select * from registration_settings where id = 1");
        $stm_res->execute();
        $result = $stm_res->fetchAll();
        return $result[0]['email_verification'];

    }


}