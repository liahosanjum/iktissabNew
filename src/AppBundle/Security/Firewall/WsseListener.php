<?php
/**
 * Created by PhpStorm.
 * User: abdulali
 * Date: 12/9/16
 * Time: 9:23 AM
 */
namespace AppBundle\Security\Firewall;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;
use AppBundle\Security\Authentication\Token\WsseUserToken;

class WsseListener implements ListenerInterface
{
    protected $tokenStorage;
    protected $authenticationManager;

    public function __construct(TokenStorageInterface $tokenStorage, AuthenticationManagerInterface $authenticationManager)
    {
//        die('---');
        $this->tokenStorage = $tokenStorage;
        $this->authenticationManager = $authenticationManager;
    }

    public function handle(GetResponseEvent $event)
    {
        $request = $event->getRequest();
//        var_dump($request);
//        die('handle');

        //$wsseRegex = '/UsernameToken Username="([^"]+)", area="(customer|anonymous)", PasswordDigest="([^"]+)", Nonce="([a-zA-Z0-9+\/]+={0,2})", Created="([^"]+)"/';
        $wsseRegex = '/UsernameToken Username="([^"]+)", area="(customer|anonymous)", PasswordDigest="([^"]+)", Nonce="([a-zA-Z0-9+\/]+={0,2})"/';
        if (!$request->headers->has('x-wsse') || 1 !== preg_match($wsseRegex, $request->headers->get('x-wsse'), $matches)) {
            return;
        }

        $token = new WsseUserToken();
        $token->setUser($matches[1]);
        $token->area     = $matches[2];
        $token->digest   = $matches[3];
        $token->nonce    = $matches[4];
        //$token->created  = $matches[5];

        try {
            $authToken = $this->authenticationManager->authenticate($token);
            $this->tokenStorage->setToken($authToken);

            return;
        } catch (AuthenticationException $failed) {
            $response = new Response();
            $response->setStatusCode(Response::HTTP_FORBIDDEN);
            $event->setResponse($response);
            $response->setContent("Invalid");
            $response->send();
            die();
            return;
        }


        // By default deny authorization
        $response = new Response();
        $response->setStatusCode(Response::HTTP_FORBIDDEN);
        $event->setResponse($response);
        return;
    }
}