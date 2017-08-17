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
            $user->getCountry();
            $card_country = substr($card, 0,1);
            $user_country = $request->get('_country');
            //header('Location: ' . $this->router->generate('account_home', array('_locale' => $request->getLocale(), '_country' => $user->getCountry())));
            if($card_country == '9' && $user_country != 'sa'  ) {

                $response = new RedirectResponse($this->router->generate('account_home', array('_locale' => $request->getLocale(), '_country' => 'sa')));
                $response->headers->setCookie(new Cookie(AppConstant::COOKIE_COUNTRY, 'sa', time() + AppConstant::COOKIE_EXPIRY, '/', null, false, false));
                $response->sendHeaders();

            }
            else if($card_country == '5' && $user_country != 'eg'  ) {

                $response = new RedirectResponse($this->router->generate('account_home', array('_locale' => $request->getLocale(), '_country' => 'eg')));
                $response->headers->setCookie(new Cookie(AppConstant::COOKIE_COUNTRY, 'eg', time() + AppConstant::COOKIE_EXPIRY, '/', null, false, false));
                $response->sendHeaders();

            }else{
                $response = new RedirectResponse($this->router->generate('account_home', array('_locale' => $request->getLocale(), '_country' => $request->get('_country'))));

            }



        }
        return $response;
    }
}
?>