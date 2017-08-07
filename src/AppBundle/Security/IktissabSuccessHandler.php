<?php
namespace AppBundle\Security;

use AppBundle\AppConstant;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Router;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

class IktissabSuccessHandler implements AuthenticationSuccessHandlerInterface
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
        if ($this->containerInterface->get('security.authorization_checker')->isGranted('ROLE_IKTUSER')) {
//            $this->get('security.authorization_checker')->isGranted('ROLE_ADMIN');
            $user = $token->getUser();
            $card = $user->getIktCardNo();
            $acrivityLog = $this->containerInterface->get('app.activity_log');
            $acrivityLog->logLoginEvent($card);


            //header('Location: ' . $this->router->generate('account_home', array('_locale' => $request->getLocale(), '_country' => $user->getCountry())));

            $response = new RedirectResponse($this->router->generate('account_home', array('_locale' => $request->getLocale(), '_country' => $user->getCountry())));
            $response->headers->setCookie(new Cookie(AppConstant::COOKIE_COUNTRY, $user->getCountry(), time() + AppConstant::COOKIE_EXPIRY, '/', null, false, false));
            $response->sendHeaders();
        }
        return $response;
    }
}
?>