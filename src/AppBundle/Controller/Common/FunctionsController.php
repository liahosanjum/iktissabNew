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

    Public function getOthaimServiceURL(Request $request)
    {
        $country_code = $this->getCountryCode($request);
        if($country_code == 'eg') {
            $url = AppConstant::OTHAIM_WEBSERVICE_URL_EG;
        }
        else if($country_code == 'sa') {
            $url = AppConstant::OTHAIM_WEBSERVICE_URL;
        }
        else{
            // default
            $url = AppConstant::OTHAIM_WEBSERVICE_URL;
        }
        return $url;
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
        // if((!isset($cookieLocale)) || ($cookieLocale == '') || (!isset($cookieCountry)) || ($cookieCountry == ''))

        if (((!isset($cookieLocale) || ($cookieLocale == '')) || (!isset($cookieCountry) || ($cookieCountry == '')))) {
            return false;
        } else {
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

    /**
     * Method convert given text to PNG image and returs
     * file name
     * @param type $text Text
     * @return string File Name
     */
     function saveTextAsImage($config = array()) {
        // Create the image

        $bg_path   = ""; // $this->get('kernel')->getRootDir()  . '/../web/backgrounds/';
        $font_path = $this->get('kernel')->getRootDir()  . '/../fonts/captcha-fonts/';
        // Default values
        $captcha_config = array(
            'code'        => '',
            'min_length'  => 4,
            'max_length'  => 4,
            'backgrounds' => array(
                $bg_path . '45-degree-fabric.png',
                $bg_path . 'cloth-alike.png',
                $bg_path . 'grey-sandbag.png',
                $bg_path . 'kinda-jean.png',
                $bg_path . 'polyester-lite.png',
                $bg_path . 'stitched-wool.png',
                $bg_path . 'white-carbon.png',
                $bg_path . 'white-wave.png'
            ),
            'fonts'     => array(
                $font_path . 'times_new_yorker.ttf'
            ),
            'characters' => 'ABCDEFGHJKLMNPRSTUVWXYZabcdefghjkmnprstuvwxyz23456789',
            'min_font_size' => 10,
            'max_font_size' => 12,
            'color' => '#666',
            'angle_min' => 0,
            'angle_max' => 10,
            'shadow' => true,
            'shadow_color' => '#FF0000',
            'shadow_offset_x' => -1,
            'shadow_offset_y' => 1
        );

        // Overwrite defaults with custom config values
        if( is_array($config) ) {
            foreach( $config as $key => $value ) $captcha_config[$key] = $value;
        }

        // Restrict certain values
        if( $captcha_config['min_length'] < 1 ) $captcha_config['min_length'] = 1;
        if( $captcha_config['angle_min'] < 0 ) $captcha_config['angle_min'] = 0;
        if( $captcha_config['angle_max'] > 10 ) $captcha_config['angle_max'] = 10;
        if( $captcha_config['angle_max'] < $captcha_config['angle_min'] ) $captcha_config['angle_max'] = $captcha_config['angle_min'];
        if( $captcha_config['min_font_size'] < 10 ) $captcha_config['min_font_size'] = 10;
        if( $captcha_config['max_font_size'] < $captcha_config['min_font_size'] ) $captcha_config['max_font_size'] = $captcha_config['min_font_size'];

        // Generate CAPTCHA code if not set by user
        if( empty($captcha_config['code']) ) {
            $captcha_config['code'] = '';
            $length = mt_rand($captcha_config['min_length'], $captcha_config['max_length']);
            while( strlen($captcha_config['code']) < $length ) {
                $captcha_config['code'] .= substr($captcha_config['characters'], mt_rand() % (strlen($captcha_config['characters'])), 1);
            }
        }
        $imageCreator = imagecreatetruecolor(100, 30);
        // Create some colors
        $white        = imagecolorallocate($imageCreator, 239, 239, 239);
        $grey         = imagecolorallocate($imageCreator, 128, 128, 128);
        $black        = imagecolorallocate($imageCreator, 0, 0, 0);
        imagefilledrectangle($imageCreator, 0, 0, 399, 29, $white);
        $this->get('session')->set('_CAPTCHA', $captcha_config['code']);

        $font = $captcha_config['fonts'][0];
        // Add some shadow to the text
        imagettftext($imageCreator, 20, 0, 11, 21, $grey, $font, $captcha_config['code']);
        // Add the text
        imagettftext($imageCreator, 20, 0, 10, 20, $black, $font, $captcha_config['code']);
        $file_name   = $this->get('kernel')->getRootDir() .'/../img/'.$captcha_config['backgrounds'][0];
        $captcha_config['backgrounds'][0];
        $file_config = array(
            'filename'      => $file_name ,
            'image_captcha' => $captcha_config['backgrounds'][0],
        );

        imagepng($imageCreator, $file_name);
        imagedestroy($imageCreator);
        return $file_config;
    }








}
