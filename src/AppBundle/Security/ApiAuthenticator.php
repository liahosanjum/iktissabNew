<?php
namespace AppBundle\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authentication\SimpleFormAuthenticatorInterface;

class ApiAuthenticator implements SimpleFormAuthenticatorInterface{

    public function authenticateToken(TokenInterface $token, UserProviderInterface $userProvider, $providerKey)
    {
        // TODO: Implement authenticateToken() method.
        try
        {
            $user = $userProvider->loadUserByUsername($token->getUsername());
        }catch (UsernameNotFoundException $e){
            throw new CustomUserMessageAuthenticationException('Invalid App');
        }

        $passwordValid = $this->encoder->isPasswordValid($user, $token->getCredentials());



        if($passwordValid){
            //TODO: check member status here

            return new UsernamePasswordToken($user, $user->getPassword(), $providerKey, $user->getRoles());
        }
        else{
            throw new CustomUserMessageAuthenticationException('Invalid Username or Password');
        }
    }

    public function supportsToken(TokenInterface $token, $providerKey)
    {
        // TODO: Implement supportsToken() method.
        return $token instanceof UsernamePasswordToken && $token->getProviderKey() == $providerKey;
    }

    public function createToken(Request $request, $username, $password, $providerKey)
    {
        // TODO: Implement createToken() method.
        return new UsernamePasswordToken($username, $password, $providerKey);
    }
}
?>