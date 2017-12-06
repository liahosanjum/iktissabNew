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
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authentication\SimpleFormAuthenticatorInterface;

class IktissabAdminAuthenticator implements SimpleFormAuthenticatorInterface
{
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder){

        $this->encoder = $encoder;
    }

    public function authenticateToken(TokenInterface $token, UserProviderInterface $userProvider, $providerKey)
    {
        try{

            $user = $userProvider->loadUserByUsername($token->getUsername());
        }
        catch (UsernameNotFoundException $e){
            throw new CustomUserMessageAuthenticationException('Invalid Username or Password');
        }

//        var_dump(unserialize($_SESSION['_sf2_attributes']['_security_member_area']));
//        var_dump(unserialize($_SESSION['_sf2_attributes']['_security_admin_area']));die();
        //var_dump($user);exit;
//        echo "<hr />";
//        var_dump($token->getRoles());


        $passwordValid = $this->encoder->isPasswordValid($user, $token->getCredentials());


        if($passwordValid)
        {
            //TODO: check member status here
            return new UsernamePasswordToken($user, $user->getPassword(), $providerKey, $user->getRoles());
        }
        else
        {
            throw new CustomUserMessageAuthenticationException('Invalid Username or Password');
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