<?php
/**
 * Created by PhpStorm.
 * User: abdulali
 * Date: 12/2/16
 * Time: 2:45 PM
 */
namespace AppBundle\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authentication\SimpleFormAuthenticatorInterface;


class IktissabAuthenticator implements SimpleFormAuthenticatorInterface
{
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder){

        $this->encoder = $encoder;
    }

    public function authenticateToken(TokenInterface $token, UserProviderInterface $userProvider, $providerKey)
    {
        try{
            /**
             * Add Login Attempts feature
             */
            if(!$userProvider->canLogin()){
                throw new UsernameNotFoundException(
                    sprintf('Username or password is incorrect or account has been blocked due to many invalid login attempts')
                );
            }

            $user = $userProvider->loadUserByUsername($token->getUsername());
        }
        catch (UsernameNotFoundException $e){
            throw new CustomUserMessageAuthenticationException('Username or password is incorrect or account has been blocked due to many invalid login attempts');
        }
        $passwordValid = $this->encoder->isPasswordValid($user, $token->getCredentials());
        if($passwordValid)
        {
            //TODO: check member status here
            $userProvider->clearLoginAttempts();
            return new UsernamePasswordToken($user, $user->getPassword(), $providerKey, $user->getRoles());
        }
        else
        {
            $userProvider->incrementLoginAttempts();
            //$re = new Request();
            throw new CustomUserMessageAuthenticationException('Username or password is incorrect or account has been blocked due to many invalid login attempts');
        }
    }

    public function supportsToken(TokenInterface $token, $providerKey)
    {
        return $token instanceof UsernamePasswordToken && $token->getProviderKey() == $providerKey;
    }

    public function createToken(Request $request, $username, $password, $providerKey)
    {
        return new UsernamePasswordToken($username, $password, $providerKey);
    }
}
