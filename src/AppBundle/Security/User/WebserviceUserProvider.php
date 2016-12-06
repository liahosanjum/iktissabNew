<?php
/**
 * Created by PhpStorm.
 * User: abdulali
 * Date: 12/1/16
 * Time: 1:01 PM
 */

namespace AppBundle\Security\User;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;


class WebserviceUserProvider implements UserProviderInterface
{
    private $em;

    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
    }

    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof WebserviceUser) {
            throw new UnsupportedUserException(
                sprintf('instance of "%s" are not supported', get_class($user))
            );

        }
        return $this->loadUserByUsername($user->getUsername());
        // TODO: Implement refreshUser() method.
    }

    public function loadUserByUsername($username)
    {

        echo "username = ".$username;
        $user = $this->em->createQueryBuilder()
            ->select('u')
            ->from('AppBundle:User', 'u')
            ->where('u.ikt_card_no = :ikt_card_no')
            ->setParameter('ikt_card_no', $username)
            ->getQuery()
            ->getOneOrNullResult();
//        echo $user->getQuery()->getSQL();
        if ($user) {


//        return new WebserviceUser($username, $password, $salt, $roles);
            return new WebserviceUser($user->getIktCardNo(), $user->getPassword(), '', array('ROLE_ADMIN'));
            // TODO: Implement loadUserByUsername() method.
        }
        throw new UsernameNotFoundException(
            sprintf('Username "%s" are not supported. ', $username)

        );
    }

    public function supportsClass($class)
    {
        return $class === 'AppBundle\Security\User\WebserviceUser';
        // TODO: Implement supportsClass() method.
    }
}