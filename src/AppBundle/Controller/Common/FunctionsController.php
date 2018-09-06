<?php

namespace AppBundle\Controller\Common;

use AppBundle\Entity\User;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\EntityManager;
use AppBundle\AppConstant;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
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
        $cookieLocale  = $request->cookies->get(AppConstant::COOKIE_LOCALE);
        $cookieCountry = $request->cookies->get(AppConstant::COOKIE_COUNTRY);
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
            'fonts'     => array( $font_path . 'times_new_yorker.ttf' ),
            'characters'    => 'ABCDEFGHJKLMNPRSTUVWXYZabcdefghjkmnprstuvwxyz23456789',
            'min_font_size' => 10,
            'max_font_size' => 12,
            'color'         => '#666',
            'angle_min'     => 0,
            'angle_max'     => 10,
            'shadow'        => true,
            'shadow_color'  => '#FF0000',
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
        $this->get('session')->set('_CAPTCHA', strtoupper($captcha_config['code']));

        $font = $captcha_config['fonts'][0];
        // Add some shadow to the text
        imagettftext($imageCreator, 20, 0, 11, 21, $grey, $font, strtoupper($captcha_config['code']));
        // Add the text
        imagettftext($imageCreator, 20, 0, 10, 20, $black, $font, strtoupper($captcha_config['code']));
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

    public function EncDec( $string, $action = 'e' ) {
        // you may change these values to your own
        $secret_key = 'abc123abcnmkgk';
        $secret_iv  = 'gfhjdkslwlsa';
        $output     = false;
        $encrypt_method = "AES-256-CBC";
        $key = hash( 'sha256', $secret_key );
        $iv  = substr( hash( 'sha256', $secret_iv ), 0, 16 );
        if( $action == 'e' ) {
            $output = base64_encode( openssl_encrypt( $string, $encrypt_method, $key, 0, $iv ) );
        }
        else if( $action == 'd' ){
            $output = openssl_decrypt( base64_decode( $string ), $encrypt_method, $key, 0, $iv );
        }
        return $output;
    }


    public function checkAccessRole($roleName , $resourceNameRequested)
    {
        $resourceOfEditor = array('MANAGE_USER',
             'MANAGE_EMAIL_SETTINGS','MANAGE_EMAIL_SETTINGS_ADD', 'MANAGE_EMAIL_SETTINGS_EDIT','MANAGE_EMAIL_SETTINGS_DELETE',
             'MANAGE_FORM_VIEW', 'MANAGE_FORM_ADD','MANAGE_FORM_EDIT','MANAGE_FORM_DELETE',
             //'MANAGE_CMS', 'MANAGE_CMS_ADD','MANAGE_CMS_EDIT','MANAGE_CMS_DELETE',
             //'MANAGE_NEWS', 'MANAGE_NEWS_ADD','MANAGE_NEWS_EDIT','MANAGE_NEWS_DELETE',
             'MANAGE_LOGS',
        );
        $resourceOfEditor2 = array('MANAGE_USER',
            'MANAGE_EMAIL_SETTINGS','MANAGE_EMAIL_SETTINGS_ADD', 'MANAGE_EMAIL_SETTINGS_EDIT','MANAGE_EMAIL_SETTINGS_DELETE',
            'MANAGE_FORM_VIEW', 'MANAGE_FORM_ADD','MANAGE_FORM_EDIT','MANAGE_FORM_DELETE',
            //'MANAGE_CMS', 'MANAGE_CMS_ADD','MANAGE_CMS_EDIT','MANAGE_CMS_DELETE',
            //'MANAGE_NEWS', 'MANAGE_NEWS_ADD','MANAGE_NEWS_EDIT','MANAGE_NEWS_DELETE',
            //'MANAGE_LOGS',
        );
        /*
        $resourceOfEditorViewer = a array('MANAGE_USER',
             'MANAGE_EMAIL_SETTINGS','MANAGE_EMAIL_SETTINGS_ADD', 'MANAGE_EMAIL_SETTINGS_EDIT','MANAGE_EMAIL_SETTINGS_DELETE',
             'MANAGE_FORM_VIEW', 'MANAGE_FORM_ADD','MANAGE_FORM_EDIT','MANAGE_FORM_DELETE',
             'MANAGE_CMS', 'MANAGE_CMS_ADD','MANAGE_CMS_EDIT','MANAGE_CMS_DELETE',
             'MANAGE_NEWS', 'MANAGE_NEWS_ADD','MANAGE_NEWS_EDIT','MANAGE_NEWS_DELETE',
             'MANAGE_LOGS',
        ); */
        if($roleName == 'ADMIN_ROLE')
        {
            return true;
        }
        else
        {
            if($roleName == 'EDITOR_ROLE')
            {
                if(in_array($resourceNameRequested, $resourceOfEditor))
                {
                    return true;
                }
            }
            elseif($roleName == 'EDITOR_ROLE2')
            {
                if (in_array($resourceNameRequested, $resourceOfEditor2))
                {
                    return true;
                }
            }
            return false;
        }
    }

    //
    public function checkAccessRole2($roleName='EDITOR_ROLE' , $resourceNameRequested='MANAGE_CMS_VIEW')
    {
        if($roleName == 'ADMIN_ROLE')
        {
            return true;
        }
        $em = $this->getDoctrine()->getManager();
        $resourceList = $this->getDoctrine()
            ->getRepository('AppBundle:Resources')
            ->findBy(array( 'status' => '1' , 'resourceName' => $resourceNameRequested , 'assignedTo' => $roleName ));
        if(!empty($resourceList)) {
            if($resourceList[0]->getId() != "" && $resourceList[0]->getId() != null) {
                return true;
            }
            return false;
        } else {
            return false;
        }
    }


    public function isValidRule($resource) {
        $tokenStorage  = $this->get('security.token_storage');
        $roleName = $tokenStorage->getToken()->getUser()->getRoleName();
        // $roleId   =  $tokenStorage->getToken()->getUser()->getRoleId();
        if(!$this->checkAccessRole2($roleName , $resource))
        {
            return false;
        }
        else {
            return true;
        }
    }

    public function checkAccessRole3($roleId , $resourceNameRequested)
    {
        if($roleId == '1')
        {
            // ADMIN_ROLE
            return true;
        }

        $em = $this->getDoctrine()->getManager();
        $resourceList = $this->getDoctrine()
            ->getRepository('AppBundle:Resources')
            ->findBy(array( 'status' => '1' , 'resourceId' => $resourceNameRequested , 'assignedTo' => $roleId ));

        if(!empty($resourceList))
        {
            if($resourceList[0]->getId() != "" && $resourceList[0]->getId() != null)
            {
                return true;
            }
            return false;
        } else {
            return false;
        }
    }


    public function setCsrfToken($token_name){
        $csrf = $this->get('security.csrf.token_manager');
        $guid = sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 61535), mt_rand(0, 61535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
        $d = new \DateTime("NOW");
        $currentDate = $d->format("Y/m/d H:i:s");
        $nonce = md5($guid);
        $passwordHash   = sha1(base64_encode($nonce) . $currentDate . AppConstant::IKTISSAB_API_SECRET);
        $passwordDigest =  base64_encode($passwordHash);
        $token = $csrf->refreshToken($passwordDigest);
        $session = new Session();
        $token_name = $token_name;
        $session->set($token_name, $token);
    }

    public function checkCsrfToken($csf_token , $token_name){
        $session = new Session();
        $token_name = $token_name;
        $token_val  = $session->get($token_name);
        if($token_val == $csf_token) {
            return true;
        }
        else {
            return false;
        }
    }

    public function validateDataName ($data)
    {
        $count_errors = 0;
        try
        {
            foreach ($data as $value)
            {
                if (!preg_match("/^[a-zA-Z\p{Arabic}\s]+$/u", $value))
                {
                    $count_errors++;
                }
            }
            return $count_errors;
        }
        catch(\Exception $e)
        {
            $count_errors = 1;
            return $count_errors;
        }
    }

    public function validateData ($data)
    {
        $count_errors = 0;
        try
        {
            foreach ($data as $value)
            {
                if (!preg_match("/^[a-zA-Z\p{Arabic}0-9\s\-+؛،,َ ً ِ ٍ ُ ٌ ْ ّ ،!@#٪&*١٢٣٤٥٦٧٨٩٠._]*$/u", $value))
                {
                    echo $count_errors++;
                }
            }
            return $count_errors;
        }
        catch(\Exception $e)
        {
            $count_errors = 1;
            return $count_errors;
        }
    }



    public function validateDataSubs ($data)
    {
        $count_errors = 0;
        try
        {
            foreach ($data as $value)
            {
                $specialCh = array(
                    '>'   , '</script>' , '<javascript>' , '</javascript>' ,
                    '<'   ,
                    '&gt' , '&GT' ,
                    '&lt' , '&LT' ,
                    'script' , 'SCRIPT' ,
                    '<script>' , '<SCRIPT>' ,
                    '</>' ,
                    '&gtscript&lt' ,
                    '&gtjavascript&lt',

                );

                foreach ($specialCh as $ch){
                    if (strpos($value, $ch) !== false) {
                        //echo 'true'.$ch;
                        //echo '<br>';
                        $count_errors++;
                    }
                }
            }
            return $count_errors;
        }
        catch(\Exception $e)
        {
            $count_errors = 1;
            return $count_errors;
        }
    }

    public function validateSpecialCharacters ($data)
    {
        $count_errors = 0;
        try
        {
            foreach ($data as $value)
            {
                $specialCh = array('!' , '@' , '#' , '$' , '%' , '^' , '&' , '*' , '(' , ')' , '-' 
                , '>' , '<' , ',' , '.' , '|' , ':' , '+' , '=' , '{' , '}','[',']', '/' , '_' , '§' , '±' , '~'
                  ,  '?' , '؟' , ';' , '"' , '؛', '&gt' , '&lt' , '&GT' , '&LT'
                );

                foreach ($specialCh as $ch){
                    if (strpos($value, $ch) !== false) {
                        //echo 'true'.$ch;
                        //echo '<br>';
                        $count_errors++;
                    }
                }
            }
            return $count_errors;
        }
        catch(\Exception $e)
        {
            $count_errors = 1;
            return $count_errors;
        }
    }






    public function validateDataPassword ($data)
    {
        $count_errors = 0;
        try
        {
            foreach ($data as $value)
            {
                if (!preg_match("/^[a-zA-Z\p{Arabic}0-9\s\-@%&*@#$!^.-_+=]+$/u", $value))
                {
                    $count_errors++;
                }
            }
            return $count_errors;
        }
        catch(\Exception $e)
        {
            $count_errors = 1;
            return $count_errors;
        }
    }
    
    
    



    public function checkJSSecurity()
    {

        if(!isset($_SESSION['js'])||$_SESSION['js']==""){
            echo "<noscript><meta http-equiv='refresh' content='0;url=/get-javascript-status.php&js=0'> </noscript>";
            $js = true;

        }elseif(isset($_SESSION['js'])&& $_SESSION['js']=="0"){
            $js = false;
            $_SESSION['js']="";

        }elseif(isset($_SESSION['js'])&& $_SESSION['js']=="1"){
            $js = true;
            $_SESSION['js']="";
        }

        if ($js) {
            echo 'Javascript is enabled';
        } else {
            echo 'Javascript is disabled';
        }
    }

    public function getBrowserInfo()
    {
        return str_replace("/","-",$_SERVER['HTTP_USER_AGENT']);
    }

    public function email_vrfy(EntityManager $entityManager)
    {
        // for DBAL
        // $entityManager = $this->get('doctrine.orm.default_entity_manager');
        try
        {
            $conn   = $entityManager->getConnection();
            $stm    = $conn->prepare("SELECT * FROM registration_settings");
            $stm->execute();
            $result = $stm->fetch();
            if(!empty($result)){
                return $result['email_verification'];
            } else {
                return 0;
            }
        }
        catch (\Exception $e){
            return 0;
        }
    }
}
