<?php
namespace AppBundle\Security;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Router;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

class IktissabAdminSuccessHandler implements AuthenticationSuccessHandlerInterface
{

    protected $router;
    protected $containerInterface;

    public function __construct(Router $router, ContainerInterface $containerInterface)
    {
        $this->router = $router;
        $this->containerInterface = $containerInterface;
    }
    /**
     * This is called when an interactive authentication attempt succeeds. This
     * is called by authentication listeners inheriting from
     * AbstractAuthenticationListener.
     *
     * @param Request $request
     * @param TokenInterface $token
     *
     * @return Response never null
     */

    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        $response = '';
        if ($this->containerInterface->get('security.authorization_checker')->isGranted('ROLE_IKTADMIN'))
        {
            // $this->get('security.authorization_checker')->isGranted('ROLE_ADMIN');
            $user = $token->getUser()->getId();
            $acrivityLog = $this->containerInterface->get('app.activity_log');
            //$acrivityLog->logLoginEvent($user);
            $acrivityLog->logLoginAdminEvent($user);
            $response = new RedirectResponse($this->router->generate('cmslistall'));
        }
        return $response;
    }
}
?>