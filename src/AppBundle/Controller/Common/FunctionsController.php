<?php

namespace AppBundle\Controller\Common;

use AppBundle\Entity\User;
use Doctrine\ORM\Query\ResultSetMapping;

use AppBundle\AppConstant;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\HttpFoundation\RedirectResponse;


class FunctionsController extends Controller
{
    Public function getIP()
    {
        return $_SERVER['REMOTE_ADDR'];
    }

    Public function getCountryCode(Request $request)
    {
        return $country_id = $request->get('_country');
    }

    Public function getCountryLocal(Request $request)
    {
        return $locale = $request->getLocale();
    }


    public function changeLanguage(Request $request, $langauge)
    {
        $response = new Response();
        $userLang = $langauge;
        if ($userLang != '' && $userLang != null) {
            $locale = $userLang;
            $response->headers->clearCookie('c_locale');
            //$response->sendHeaders();
        }
        $request->setLocale($langauge);
        // sohail: todo
        // $request->getSession()->set('_locale', $langauge);
        $response->headers->setCookie(new Cookie(AppConstant::COOKIE_LOCALE, $locale, time() + AppConstant::COOKIE_EXPIRY, '/', null, false, false));
        //
        $response->sendHeaders();
        return $request->cookies->get(AppConstant::COOKIE_LOCALE);
    }

    public function changeCountry(Request $request, $country)
    {
        $response = new Response();
        $userCountry = $country;
        if ($userCountry != '' && $userCountry != null) {
            $country = $userCountry;
            $response->headers->clearCookie(AppConstant::COOKIE_COUNTRY);
            $response->sendHeaders();
        }
        $country = $request->get('_country');
        $response->headers->setCookie(new Cookie(AppConstant::COOKIE_COUNTRY, $country, time() + AppConstant::COOKIE_EXPIRY, '/', null, false, false));
        //
        $response->sendHeaders();
        return $country;
    }

    public function resetCountry(Request $request, $country)
    {
        $response = new Response();
        $userCountry = $country;
        if ($userCountry != '' && $userCountry != null) {
            $country = $userCountry;
            $response->headers->clearCookie('c_country');
            $response->sendHeaders();
        }
        $country = $request->get('_country');
        $response->headers->setCookie(new Cookie(AppConstant::COOKIE_COUNTRY, $country, time() + AppConstant::COOKIE_EXPIRY, '/', null, false, false));
        $response->sendHeaders();
        return $country;
    }



    public function checkSessionCookies(Request $request)
    {
        $cookieLocale = $request->cookies->get(AppConstant::COOKIE_LOCALE);
        $cookieCountry = $request->cookies->get(AppConstant::COOKIE_COUNTRY);
        if((!isset($cookieLocale)) && ($cookieLocale == '') && (!isset($cookieCountry)) && ($cookieCountry == '')){
            return false;
        }
        else{
            return true;
        }
    }

    function iktExist($ikt)
    {
        $em = $this->getDoctrine()->getManager();
        $checkIktCard = $em->getRepository('AppBundle:User')->find($ikt);
        if (is_null($checkIktCard)) {
            Throw new Exception($this->get('translator')->trans('Card is not registered on website'), 1);
        } else {
            return $checkIktCard->getEmail();
        }
    }

    public function validateIqama($iqama)
    {
        $evenSum = 0;
        $oddSum  = 0;
        $entireSum = 0;
        for ($i = 0; $i < strlen($iqama); $i++) {
            $temp = '';
            if ($i % 2) { // odd number

                $oddSum = $oddSum + $iqama[$i];

            } else {
                //even
                $multE = $iqama[$i] * 2;
                if (strlen($multE) > 1) {
                    $temp = (string)$multE;
                    $evenSum = $evenSum + ($temp[0] + $temp[1]);
                } else {
                    $evenSum = $evenSum + $multE;
                }
            }
        }
        $entireSum = $evenSum + $oddSum;
        if (($entireSum % 10) == 0) {
            return true;
        } else {
            return false;
        }

    }

   






}
