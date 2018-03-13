<?php
/**
 * Created by PhpStorm.
 * User: abdulali
 * Date: 12/18/16
 * Time: 12:41 PM
 */
namespace AppBundle\Security;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Router;
use Symfony\Component\Security\Http\Logout\LogoutSuccessHandlerInterface;

class LogoutAdminHandler implements LogoutSuccessHandlerInterface
{

    protected $router;
    protected $containerInterface;
    protected $session;

    public function __construct(Router $router, ContainerInterface $containerInterface, Session $session)
    {
        $this->router = $router;
        $this->containerInterface = $containerInterface;
        $this->session = $session;
    }


    /**
     * Creates a Response object to send upon a successful logout.
     *
     * @param Request $request
     *
     * @return Response never null
     */
    public function onLogoutSuccess(Request $request)
    {
        // echo 'testing';exit;
        $user = $this->containerInterface->get('security.token_storage')->getToken()->getUser()->getId();
        $acrivityLog = $this->containerInterface->get('app.activity_log');
        $acrivityLog->logLogoutEvent($user,'admin');
        $this->containerInterface->get('security.token_storage')->setToken(null);
        $response = new RedirectResponse($this->router->generate('admin_admin'));
        return $response;
    }
}