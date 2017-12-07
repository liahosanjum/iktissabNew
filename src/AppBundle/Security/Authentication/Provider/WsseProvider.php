<?php
/**
 * Created by PhpStorm.
 * User: abdulali
 * Date: 12/9/16
 * Time: 9:27 AM
 */
namespace AppBundle\Security\Authentication\Provider;


use AppBundle\Security\User\ApiUserProvider;
use Doctrine\ORM\EntityManager;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\NonceExpiredException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use AppBundle\Security\Authentication\Token\WsseUserToken;

class WsseProvider implements AuthenticationProviderInterface
{
    private $userProvider;
    private $cachePool;
    private $em;

    public function __construct(EntityManager $entityManager, CacheItemPoolInterface $cachePool)
    {

        $this->em = $entityManager;
        $this->cachePool = $cachePool;
    }

    /**
     * @param TokenInterface $token
     * @return WsseUserToken
     */
    public function authenticate(TokenInterface $token)
    {

        //For api call which does not required authentication
        if($token->area == 'anonymous'){

            $authenticatedToken = new WsseUserToken(['ROLE_API']);
            $authenticatedToken->area = $token->area;
            $authenticatedToken->setUser($token->getUser());

            return $authenticatedToken;

        }

        $this->userProvider = new ApiUserProvider($this->em);
        $user = $this->userProvider->loadUserByUsername($token->getUsername());

        if ($user && $this->validateDigest($token->digest, $token->nonce, $user->getPassword())) {
            $authenticatedToken = new WsseUserToken($user->getRoles());
            $authenticatedToken->setUser($user);

            return $authenticatedToken;
        }
        throw new AuthenticationException('The WSSE authentication failed.');
    }

    /**
     * This function is specific to Wsse authentication and is only used to help this example
     *
     * For more information specific to the logic here, see
     * https://github.com/symfony/symfony-docs/pull/3134#issuecomment-27699129
     */
    protected function validateDigest($digest, $nonce, $secret)
    {
        // Check created time is not in the future
//        if (strtotime($created) > time()) {
//            return false;
//        }

        // Expire timestamp after 5 minutes
//        if (time() - strtotime($created) > 3000000) {
//            return false;
//        }

        // Try to fetch the cache item from pool
        $cacheItem = $this->cachePool->getItem(md5($nonce));

        // Validate that the nonce is *not* in cache
        // if it is, this could be a replay attack
        if ($cacheItem->isHit()) {
           throw new NonceExpiredException('Previously used nonce detected');
        }

        // Store the item in cache for 5 minutes
//        $cacheItem->set(null)->expiresAfter(300);
        $this->cachePool->save($cacheItem);

        // Validate Secret
        //$expected = base64_encode(sha1(base64_decode($nonce).$created.$secret, true));
        $expected = base64_encode(sha1(base64_encode($nonce).$secret));
        return hash_equals($expected, $digest);
    }

    public function supports(TokenInterface $token)
    {
        return $token instanceof WsseUserToken;
    }
}