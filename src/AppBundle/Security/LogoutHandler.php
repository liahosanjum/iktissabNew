<?php
/**
 * Created by PhpStorm.
 * User: abdulali
 * Date: 12/18/16
 * Time: 12:41 PM
 */
namespace AppBundle\Security;

use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Router;
use Symfony\Component\Security\Http\Logout\LogoutSuccessHandlerInterface;

class LogoutHandler implements LogoutSuccessHandlerInterface
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

          
        if ($this->containerInterface->get('security.token_storage')->getToken() != '') {
                $user = $this->containerInterface->get('security.token_storage')->getToken()->getUser()->getIktCardNo();
                $acrivityLog = $this->containerInterface->get('app.activity_log');
                $acrivityLog->logLogoutEvent($user);
            }

            if ($this->session->get('IktUserData')) {
                $this->session->remove('iktUserData');
            }

            $response = new RedirectResponse($this->router->generate('account_home', array('_locale' => $request->getLocale(), '_country' => $request->get('_country'))));
            return $response;


        // TODO: Implement onLogoutSuccess() method.
    }
}