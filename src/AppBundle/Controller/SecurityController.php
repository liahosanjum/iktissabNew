<?php
/**
 * Created by PhpStorm.
 * User: abdulali
 * Date: 12/1/16
 * Time: 12:00 PM
 */
namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\BrowserKit\Response;
use AppBundle\Controller\Common\FunctionsController;
use AppBundle\AppConstant;

class SecurityController extends Controller
{
    /**
     * @Route("/{_country}/{_locale}/login", name="login")
     */
    public function loginAciton(Request $request){


        $response = new Response();
        $commFunct = new FunctionsController();
        if($commFunct->checkSessionCookies($request) == false){
            return $this->redirect($this->generateUrl('landingpage'));
        }

        $userLang = '';
        $locale = $request->getLocale();
        if($request->query->get('lang')) {
            $userLang = trim($request->query->get('lang'));
        }
        if ($userLang != '' && $userLang != null) {
            // we will only modify cookies if the both the params are same for the langauge
            // this means that the query is modified from the change language link
            if($userLang == $locale)
            {
                $commFunct->changeLanguage($request, $userLang);
                $locale = $request->getLocale();
            }
            else
            {
                if($request->cookies->get(AppConstant::COOKIE_LOCALE)){
                    return $this->redirect($this->generateUrl('account_home',
                        array('_country' => $request->cookies->get(AppConstant::COOKIE_COUNTRY),
                            '_locale' => $request->cookies->get(AppConstant::COOKIE_LOCALE))));
                }
            }
        }




        $userCountry = '';
        if($request->query->get('ccid')) {
            $userCountry = $request->query->get('ccid');
        }
        $country = $request->get('_country');
        if ($userCountry != '' && $userCountry != null) {
            if($userCountry == $country) {
                $commFunct->changeCountry($request, $userCountry);
                $country = $request->get('_country');}
            else {
                if($request->cookies->get(AppConstant::COOKIE_COUNTRY)) {
                    return $this->redirect($this->generateUrl('account_home', array('_country' => $request->cookies->get(AppConstant::COOKIE_COUNTRY), '_locale' => $request->cookies->get(AppConstant::COOKIE_LOCALE))));
                }
            }
        }




        if($request->cookies->get(AppConstant::COOKIE_LOCALE))
        {
            $cookieLocale  = $request->cookies->get(AppConstant::COOKIE_LOCALE);
            $cookieCountry = $request->cookies->get(AppConstant::COOKIE_COUNTRY);
            if (isset($cookieLocale) && $cookieLocale <> '' && $cookieLocale != $locale) {
                return $this->redirect($this->generateUrl('homepage', array('_country' => $cookieCountry, '_locale' => $cookieLocale)));
            }
            if(isset($cookieCountry) && $cookieCountry <> '' && $cookieCountry != $country) {
                return $this->redirect($this->generateUrl('homepage', array('_country' => $cookieCountry, '_locale' => $cookieLocale)));
            }
        }


        if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')){
            // create current user country session
            
            $this->get('session')->set('userSelectedCountry',$request->get('_country'));
            return $this->redirectToRoute('homepage', array('_country' => $cookieCountry, '_locale' => $cookieLocale));
        }




        $authenticationUtils = $this->get('security.authentication_utils');

        $error = $authenticationUtils->getLastAuthenticationError();

        $lastUsername = $authenticationUtils->getLastUsername();
        $message = "";
        $errorcl = 'alert-danger';
        return $this->render(':default:login.html.twig', array(
           'last_username' => $lastUsername, 'message' => $message,
            'error' => $error , 'errorcl' => $errorcl
        ));
    }

    /**
     * @Route("/admin/admin")
     */
    public function adminAction(Request $request)
    {
        if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')){
            return $this->redirectToRoute('homepage');
        }


        $authenticationUtils = $this->get('security.authentication_utils');

        $error = $authenticationUtils->getLastAuthenticationError();

        $lastUsername = $authenticationUtils->getLastUsername();
          $message = "";
        return $this->render(':admin:login.html.twig', array(
            'last_username' => $lastUsername, 'message' => $message,
            'error' => $error
        ));
    }


    /**
     * @Route("/admin/logout", name="admin_logout")
     */
    public function adminLogout(Request $request)
    {
    }


    /**
     * @Route("{_country}/{_locale}/account/logout", name="account_logout")
     */
    public function accountLogout(Request $request)
    {
        // die('account logout');
    }


}