<?php
namespace AppBundle\Security\User;

use AppBundle\Entity\ApiUser;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;


class ApiUserProvider implements UserProviderInterface
{

    private $em;

    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
    }

    public function loadUserByUsername($email)
    {

        $user =  $this->em->getRepository('AppBundle:User')->findOneByEmail($email);

        if($user != null){

            return new \AppBundle\Security\User\ApiUser($user->getEmail(), $user->getPassword(), null, $user->getRoles() );
            
        }

        throw new UsernameNotFoundException(
            sprintf('Username %s does not exist', $email)
        );
    }

    public function refreshUser(UserInterface $user)
    {
        if(!$user instanceof User){
            throw new UnsupportedUserException(
                sprintf('Instances of "%s" are not supported.', get_class($user))
            );
        }

        return $user;
    }

    public function supportsClass($class)
    {
        return $class === 'AppBundle\Security\User\User';
    }
}

?>