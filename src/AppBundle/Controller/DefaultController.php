<?php

namespace AppBundle\Controller;

use AppBundle\AppConstant;
use AppBundle\Controller\Common\FunctionsController;
use AppBundle\Controller\Common\ExcelHTML;
use AppBundle\Controller\Swift_Attachment;
use AppBundle\Entity\Subscription;
use AppBundle\Entity\User;
use AppBundle\Form\SendPwdType;
use Doctrine\ORM\Query\ResultSetMapping;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

// use Symfony\Component\Form\Extension\Core\Type\IntegerType;


class DefaultController extends Controller
{
    /**
     * @Route("/", name="landingpage")
     */
    public function indexAction(Request $request)
    {
        try
        {
            $response = new Response();
            $cookie_country = $request->cookies->get('cookie_country');
            $store = $request->cookies->get('store');
            $cookieLocale  = $request->cookies->get(AppConstant::COOKIE_LOCALE);
            $cookieCountry = $request->cookies->get(AppConstant::COOKIE_COUNTRY);
            // home page image gallery listing
            $header_listing = $this->headerTopBanner($request);
            if(!empty($header_listing))
            {
                $image_data_header = $header_listing->getImage();
                $session = new Session();
                $image_data_header = $session->set('header_image_top' , $image_data_header);
            }

            if (isset($cookieLocale) && $cookieLocale <> '' && isset($cookieCountry) && $cookieCountry <> '') {
                return $this->redirect(AppConstant::BASE_URL."/".$cookieLocale."/".$cookieCountry."/");
            } else {
                
                // cookies is not set
                // here get cookie from othaimmarkets.com website for country
                if (empty($request->cookies->get('cookie_country')))
                {
                    if(empty($request->cookies->get(AppConstant::COOKIE_COUNTRY)))
                    {
                        //return $this->redirect(AppConstant::COOKIE_NOT_PRESENT_URL);
                        return $this->render('default/index.html.twig', [
                            'base_dir' => realpath(
                                    $this->getParameter('kernel.root_dir') . '/..') . DIRECTORY_SEPARATOR,
                        ]);
                    }
                    else
                    {
                        $locale_  = $request->cookies->get(AppConstant::COOKIE_LOCALE);
                        $country_ = $request->cookies->get(AppConstant::COOKIE_COUNTRY);
                        return $this->redirect(AppConstant::BASE_URL."/".$locale_."/".$country_."/");
                    }
                }
                else
                {
                    $country = $request->cookies->get("cookie_country");
                    // here get cookie from othaimmarkets.com website for language
                    if ($request->cookies->get("store") == '') {
                        // if language from othaimmarkets is empty then set language
                        // to arabic
                        $locale = AppConstant::SET_DEFAULT_LANGUAGE_ARABIC;
                    } else {
                        $locale = $request->cookies->get("store");
                    }
                    $cookieLocale  = $request->cookies->get(AppConstant::COOKIE_LOCALE);
                    $cookieCountry = $request->cookies->get(AppConstant::COOKIE_COUNTRY);
                    if (((!isset($cookieLocale) || ($cookieLocale == '')) || (!isset($cookieCountry) || ($cookieCountry == ''))))
                    {
                        $response->headers->setCookie(new Cookie(AppConstant::COOKIE_LOCALE, $locale, time() + AppConstant::COOKIE_EXPIRY, '/', null, false, false));
                        $response->headers->setCookie(new Cookie(AppConstant::COOKIE_COUNTRY, $country, time() + AppConstant::COOKIE_EXPIRY, '/', null, false, false));
                        $response->sendHeaders();
                        return $this->redirect(AppConstant::BASE_URL."/".$locale."/".$country."/");
                    } else {
                        return $this->redirect(AppConstant::BASE_URL."/".$locale."/".$country."/");
                        //return $this->redirect($this->generateUrl('homepage', array('_country' => $country, '_locale' => $locale)));
                    }
                }
            }
        }
        catch(\Exception $e)
        {
            //echo $e->getMessage();
            return $this->render('TwigBundle:Exception:error404.html.twig', [
                'base_dir' => realpath(
                        $this->getParameter('kernel.root_dir') . '/..') . DIRECTORY_SEPARATOR,
            ]);

        }

    }




    /**
     * @Route("/{_country}/{_locale}/setCooki", name="setCooki")
     * @param Request $request
     */
    public function setCookiAction(Request $request)
    {
        $response = new Response();
        $cookieLocale  = $request->cookies->get(AppConstant::COOKIE_LOCALE);
        $cookieCountry = $request->cookies->get(AppConstant::COOKIE_COUNTRY);
        $locale  = $request->getLocale();
        $country = $request->get('_country');
        if (((!isset($cookieLocale) || ($cookieLocale == '')) || (!isset($cookieCountry) || ($cookieCountry == '')))) {
            $response->headers->setCookie(new Cookie(AppConstant::COOKIE_LOCALE, $locale, time() + AppConstant::COOKIE_EXPIRY, '/', null, false, false));
            $response->headers->setCookie(new Cookie(AppConstant::COOKIE_COUNTRY, $country, time() + AppConstant::COOKIE_EXPIRY, '/', null, false, false));
            $response->sendHeaders();
            return $this->redirect($this->generateUrl('homepage', array('_country'=> $country,'_locale'=> $locale)));
        } else {
            return $this->redirect($this->generateUrl('homepage', array('_country'=> $country,'_locale'=> $locale)));
        }
    }

    /*
    public function setCookiFromOthaimMarkets(Request $request, $cookie_country_from , $cookie_language_from)
    {
        $response = new Response();
        echo $cookieLocale  = $request->cookies->get(AppConstant::COOKIE_LOCALE);
        echo $cookieCountry = $request->cookies->get(AppConstant::COOKIE_COUNTRY);
        $locale  = $request->getLocale();
        $country = $request->get('_country');

        echo $locale  = $cookie_language_from;
        echo $country = $cookie_country_from;
        exit;
        if((!isset($cookieLocale)) && ($cookieLocale == '') && (!isset($cookieCountry)) && ($cookieCountry == '')){
            $response->headers->setCookie(new Cookie(AppConstant::COOKIE_LOCALE, $locale, time() + AppConstant::COOKIE_EXPIRY, '/', null, false, false));
            $response->headers->setCookie(new Cookie(AppConstant::COOKIE_COUNTRY, $country, time() + AppConstant::COOKIE_EXPIRY, '/', null, false, false));
            $response->sendHeaders();
            return $this->redirect($this->generateUrl('homepage', array('_country'=> $country,'_locale'=> $locale)));
        }
    }
    */






    /**
     * @param Request $request
     * @param Response $response
     */
    private function setPreferences(Request $request)
    {
        $response = new Response();
        $commFunct = new FunctionsController();
        $request->cookies->get(AppConstant::COOKIE_LOCALE);
        $userLang = '';
        $locale = $request->getLocale();
        $request->query->get('lang');
        if($request->query->get('lang')) {
            $userLang = trim($request->query->get('lang'));
        }
        $country = $request->get('_country');
        if ($userLang != '' && $userLang != null) {
            // we will only modify cookies if the both the params are same for the langauge
            // this means that the query is modified from the change language link
            if($userLang == $locale)
            {
                $request->getLocale();
                $commFunct->changeLanguage($request, $userLang);
                $locale = $request->getLocale();
                return $this->redirect($this->generateUrl('homepage', array('_country' => $country, '_locale' => $locale)));
            }
            else
            {
                // if the lang param and the url param is not the same then donot change the language
                if($request->cookies->get(AppConstant::COOKIE_LOCALE)){
                     $cookieLocale = $request->cookies->get(AppConstant::COOKIE_LOCALE);
                      $cookieCountry = $request->cookies->get(AppConstant::COOKIE_COUNTRY);
                    return $this->redirect($this->generateUrl('homepage', array('_country' => $cookieCountry, '_locale' => $cookieLocale)));
                }
            }
        }



        $userCountry = '';
        $request->getLocale();
        if($request->query->get('ccid')) {
            $userCountry = $request->query->get('ccid');
        }
        $country = $request->get('_country');
        if ($userCountry != '' && $userCountry != null)
        {
            if($userCountry == $country)
            {
                $commFunct->changeCountry($request, $userCountry );
                $country = $request->get('_country');
                $request->cookies->get(AppConstant::COOKIE_COUNTRY);
            }
            else
            {
                if($request->cookies->get(AppConstant::COOKIE_COUNTRY))
                {
                    $cookieLocale = $request->cookies->get(AppConstant::COOKIE_LOCALE);
                    $cookieCountry = $request->cookies->get(AppConstant::COOKIE_COUNTRY);
                    return $this->redirect($this->generateUrl('homepage', array('_country' => $cookieCountry, '_locale' => $cookieLocale)));
                }
            }
        }

        if($request->cookies->get(AppConstant::COOKIE_LOCALE))
        {
            $cookieLocale = $request->cookies->get(AppConstant::COOKIE_LOCALE);
            $cookieCountry = $request->cookies->get(AppConstant::COOKIE_COUNTRY);
            if (isset($cookieLocale) && $cookieLocale <> '' && $cookieLocale != $locale) {
                return $this->redirect($this->generateUrl('homepage', array('_country' => $cookieCountry, '_locale' => $cookieLocale)));
            }

            if (isset($cookieCountry) && $cookieCountry <> '' && $cookieCountry != $country) {
                return $this->redirect($this->generateUrl('homepage', array('_country' => $cookieCountry, '_locale' => $cookieLocale)));
            }
        }
    }








    /**
     * @Route("/{_country}/{_locale}/", name="homepage")
     * @param Request $request
     * @param Response $response
     */
    public function homepageAction(Request $request)
    {
        try
        {
            $response  = new Response();
            $commFunct = new FunctionsController();
            $commFunct->setContainer($this->container);
            if ($commFunct->checkSessionCookies($request) == false) {
                return $this->redirect($this->generateUrl('landingpage'));
            }
            $userLang = '';
            $locale   = $request->getLocale();
            if ($request->query->get('lang')) {
                $userLang = trim($request->query->get('lang'));
            }
            if ($userLang != '' && $userLang != null) {
                // we will only modify cookies if the both the params are same for the langauge
                // this means that the query is modified from the change language link
                if ($userLang == $locale) {
                    $commFunct->changeLanguage($request, $userLang);
                    $locale = $request->getLocale();
                } else {
                    if ($request->cookies->get(AppConstant::COOKIE_LOCALE)) {
                        return $this->redirect($this->generateUrl('homepage', array('_country' => $request->cookies->get(AppConstant::COOKIE_COUNTRY), '_locale' => $request->cookies->get(AppConstant::COOKIE_LOCALE))));
                    }
                }
            }


            $userCountry = '';
            if ($request->query->get('ccid')) {
                $userCountry = $request->query->get('ccid');
            }
            $country = $request->get('_country');
            if ($userCountry != '' && $userCountry != null) {
                if ($userCountry == $country) {
                    $commFunct->changeCountry($request, $userCountry);
                    $country = $request->get('_country');
                } else {
                    if ($request->cookies->get(AppConstant::COOKIE_COUNTRY)) {
                        return $this->redirect($this->generateUrl('homepage', array('_country' => $request->cookies->get(AppConstant::COOKIE_COUNTRY), '_locale' => $request->cookies->get(AppConstant::COOKIE_LOCALE))));
                    }
                }
            }
            if ($request->cookies->get(AppConstant::COOKIE_LOCALE)) {
                $cookieLocale = $request->cookies->get(AppConstant::COOKIE_LOCALE);
                $cookieCountry = $request->cookies->get(AppConstant::COOKIE_COUNTRY);
                if (isset($cookieLocale) && $cookieLocale <> '' && $cookieLocale != $locale) {
                    return $this->redirect($this->generateUrl('homepage', array('_country' => $cookieCountry, '_locale' => $cookieLocale)));
                }
                if (isset($cookieCountry) && $cookieCountry <> '' && $cookieCountry != $country) {
                    return $this->redirect($this->generateUrl('homepage', array('_country' => $cookieCountry, '_locale' => $cookieLocale)));
                }
            }
            /********/
            // get all news from db
            if ($request->cookies->get(AppConstant::COOKIE_COUNTRY)) {
                $news_country = $request->cookies->get(AppConstant::COOKIE_COUNTRY);
            }
            else {
                $news_country = 'sa';
            }
            $em = $this->getDoctrine()->getManager();
            $homePageNews = $em->getRepository('AppBundle:CmsPages')
                ->findBy(array('status' => '1', 'type' => 'news', 'country' => $news_country ,
                'language' => $request->cookies->get(AppConstant::COOKIE_LOCALE) ));
            // print_r($homePageNews);
            $data_news = array();
            $i = 0;
            if(!empty($homePageNews)) {
                foreach ($homePageNews as $homePageNew)
                {
                    $data_news[$i]['id'] = $homePageNew->getId();
                    $data_news[$i]['details'] = $homePageNew->getpageContent();
                    $i++;
                }
            }
            else {
                $data_news = "";
            }
            // home page image gallery listing
            $image_listing = $this->listbanners($request);

            foreach ($image_listing as $imageListing )
            {
                $image_data[$i]['image']  = $imageListing->getImage();
                $image_data[$i]['atitle'] = $imageListing->getAtitle();
                $image_data[$i]['etitle'] = $imageListing->getEtitle();
                $image_data[$i]['edesc']  = $imageListing->getEdesc();
                $image_data[$i]['adesc']  = $imageListing->getAdesc();
                $i++;
            }

            // home page image gallery listing
            $header_listing = $this->headerTopBanner($request);
            if(!empty($header_listing)) {
                $image_data_header = $header_listing->getImage();
                $session = new Session();
                $image_data_header = $session->set('header_image_top' , $image_data_header);
            }
            /********/
            $restClient = $this->get('app.rest_client')->IsAdmin(true);
            $url        =  $commFunct->getOthaimServiceURL($request);
            $postData = "{\"service\":\"IktissabPromotions\"} ";
                try
                {
                    $data = $restClient->restPostForm($url,
                        $postData, array('Content-Type' => 'application/x-www-form-urlencoded', 'input-content-type' => "text/json", 'output-content-type' => "text/json",
                            'language' => $locale ));
                }
                catch(\Exception $e)
                {
                    $message = $this->get('translator')->trans('An invalid exception occurred');
                    return $this->render('front/homepage.html.twig', array('DataPromo' => "",
                        'image_data'        => $image_data,
                        'header_image_data' => $image_data_header,
                        'data_news'         => $data_news));
                }

                $data_dec = json_decode($data, true);
                $data_dec['success'];
                $listing = array();
                if ($data_dec['success'] == true) {
                    $products = json_decode($data);
                    // var_dump(json_decode($data));
                    // echo "<br>";
                    // var_dump($products->products[0]);
                    // var_dump($products->success);
                    if ($products->success == true) {
                        $pro = $products->products;
                        $i = 0;
                        foreach ($products->products as $data) {
                            $listing[$i]['ID'] = $data->ID;
                            $listing[$i]['Price'] = $data->Price;
                            $listing[$i]['SpecialPrice'] = $data->SpecialPrice;
                            $listing[$i]['Name']  = $data->Name;
                            $listing[$i]['SKU'] = $data->SKU;
                            $listing[$i]['URL'] = $data->URL;
                            $listing[$i]['SmallImage'] = $data->SmallImage;
                            $i++;
                            if ($i == 12) {
                                break;
                            }
                        }
                        // var_dump($listing);
                        return $this->render('front/homepage.html.twig', array('DataPromo' => $listing,
                            'image_data' => $image_data,
                            'header_image_data' => $image_data_header,
                            'data_news'  => $data_news));
                    } else {

                        return $this->render('front/homepage.html.twig', array('DataPromo' => "",
                            'image_data' => $image_data,
                            'header_image_data' => $image_data_header,
                            'data_news' => $data_news));
                    }
                } else {
                    return $this->render('front/homepage.html.twig', array('DataPromo' => "",
                        'image_data' => $image_data,
                        'header_image_data' => $image_data_header,
                        'data_news' => $data_news));
                }
        }
        catch(\Exception $e)
        {
            $message = $this->get('translator')->trans('An invalid exception occurred');
            $data_news = '';
            return $this->render('front/homepage.html.twig', array('DataPromo' => "",
                'image_data' => $image_data,
                'header_image_data' => $image_data_header,
                'data_news' => $data_news));
        }
        catch(AccessDeniedException $ad)
        {
            $message = $this->get('translator')->trans('An invalid exception occurred');
            $data_news = '';
            return $this->render('front/homepage.html.twig', array('DataPromo' => "",
                'image_data' => $image_data,
                'header_image_data' => $image_data_header,
                'data_news' => $data_news));
        }
    }


    /**
     * @Route("/{_country}/{_locale}/cms/{id}", name="front_cmscontent")
     * @param Request $request
     * @param $id
     * @param $type
     */
    public function getCmsContent(Request $request , $id)
    {
        try
        {
            $response = new Response();
            $commFunct = new FunctionsController();
            $commFunct->setContainer($this->container);
            if ($commFunct->checkSessionCookies($request) == false) {
                return $this->redirect($this->generateUrl('landingpage'));
            }
            // home page image gallery listing
            $header_listing = $this->headerTopBanner($request);
            if(!empty($header_listing))
            {
                $image_data_header = $header_listing->getImage();
                $session = new Session();
                $image_data_header = $session->set('header_image_top' , $image_data_header);
            }

            $userLang = '';
            $locale = $request->getLocale();
            if ($request->query->get('lang')) {
                $userLang = trim($request->query->get('lang'));
            }
            if ($userLang != '' && $userLang != null) {
                // we will only modify cookies if the both the params are same for the langauge
                // this means that the query is modified from the change language link
                if ($userLang == $locale) {
                    $commFunct->changeLanguage($request, $userLang);
                    $locale = $request->getLocale();
                } else {
                    if ($request->cookies->get(AppConstant::COOKIE_LOCALE)) {
                        return $this->redirect($this->generateUrl('homepage', array('_country' => $request->cookies->get(AppConstant::COOKIE_COUNTRY), '_locale' => $request->cookies->get(AppConstant::COOKIE_LOCALE))));
                    }
                }
            }
            $userCountry = '';
            if ($request->query->get('ccid')) {
                $userCountry = $request->query->get('ccid');
            }
            $country = $request->get('_country');
            if ($userCountry != '' && $userCountry != null) {
                if ($userCountry == $country) {
                    $commFunct->changeCountry($request, $userCountry);
                    $country = $request->get('_country');
                } else {
                    if ($request->cookies->get(AppConstant::COOKIE_COUNTRY)) {
                        return $this->redirect($this->generateUrl('homepage', array('_country' => $request->cookies->get(AppConstant::COOKIE_COUNTRY), '_locale' => $request->cookies->get(AppConstant::COOKIE_LOCALE))));
                    }
                }
            }

            if ($request->cookies->get(AppConstant::COOKIE_LOCALE)) {
                $cookieLocale = $request->cookies->get(AppConstant::COOKIE_LOCALE);
                $cookieCountry = $request->cookies->get(AppConstant::COOKIE_COUNTRY);
                if (isset($cookieLocale) && $cookieLocale <> '' && $cookieLocale != $locale) {
                    return $this->redirect($this->generateUrl('homepage', array('_country' => $cookieCountry, '_locale' => $cookieLocale)));
                }
                if (isset($cookieCountry) && $cookieCountry <> '' && $cookieCountry != $country) {
                    return $this->redirect($this->generateUrl('homepage', array('_country' => $cookieCountry, '_locale' => $cookieLocale)));
                }
            }
            if ($commFunct->checkSessionCookies($request) == false) {
                return $this->redirect($this->generateUrl('landingpage'));
            }
            $em = $this->getDoctrine()->getManager();
            $cmspages = $this->getDoctrine()
                ->getRepository('AppBundle:CmsPages')
                ->findOneBy(array('url_path' => $id, 'status' => 1, 'type' => 'CMS',
                    'country' => 'sa', 'language' => $cookieLocale ));
            $data = array();
            $i = 0;
            $error = 'alert-danger';
            if (!$cmspages) {
                return $this->render('front/cms/contents.html.twig', array('Data' => "",  'errorcl' => $error,
                    'image_data_header' => $image_data_header,
                    'message' => $this->get('translator')->trans('No record found')));
            } else {
                $error = '';
                return $this->render('front/cms/contents.html.twig', array('data' => $data, 'message' => '',
                    'errorcl' => $error,
                    'image_data_header' => $image_data_header,
                    'Data' => array('id' => $cmspages->getId(), 'page_title' => $cmspages->getpageTitle(),
                        'page_content' => $cmspages->getpageContent() , 'url_path' => $cmspages->geturlPath()
                    )
                ));
            }
        }
        catch(\Exception $e){
            $error = 'alert-danger';
            $e->getMessage();
            $message = $this->get('translator')->trans('Unable to process your request at this time.Please try later');
            return $this->render('front/cms/contents.html.twig', array('Data' => "", 'message' => $message,
                'errorcl' => $error,
                'image_data_header' => $image_data_header,
                ));
        }
        catch (AccessDeniedException $ed)
        {
            $error = 'alert-danger';
            $message = $this->get('translator')->trans('Unable to process your request at this time.Please try later');
            return $this->render('front/cms/contents.html.twig', array('Data' => "", 'message' => $message,
                'errorcl' => $error,  'image_data_header' => $image_data_header,
            ));
        }




    }



    /**
     * @Route("/{_country}/{_locale}/forgotpassword", name="forgotpassword")
     */

    public function forgotPassword(Request $request)
    {
        try
        {
            $response = new Response();
            $commFunct = new FunctionsController();
            $commFunct->setContainer($this->container);
            if($commFunct->checkSessionCookies($request) == false) {
                return $this->redirect($this->generateUrl('landingpage'));
            }
            // if user is logged in he should not see login page again
            if($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
                return $this->redirectToRoute('homepage', array('_country' => $request->cookies->get(AppConstant::COOKIE_COUNTRY), '_locale' => $request->cookies->get(AppConstant::COOKIE_LOCALE)));
            }
            // home page image gallery listing
            $header_listing = $this->headerTopBanner($request);
            if(!empty($header_listing)) {
                $image_data_header = $header_listing->getImage();
                $session = new Session();
                $image_data_header = $session->set('header_image_top' , $image_data_header);
            }


            $error          = "";
            $locale_cookie  = $request->getLocale();
            $country_cookie = $request->get('_country');
            $userLang       = trim($request->query->get('lang'));
            if ($userLang   != '' && $userLang != null) {
                if ($userLang == $locale_cookie) {
                    $request->getLocale();
                    $commFunct->changeLanguage($request, $userLang);
                    $locale_cookie = $request->getLocale();
                }
                else
                {
                    if ($request->cookies->get(AppConstant::COOKIE_LOCALE)) {
                        return $this->redirect($this->generateUrl('login', array('_country' => $request->cookies->get(AppConstant::COOKIE_COUNTRY), '_locale' => $request->cookies->get(AppConstant::COOKIE_LOCALE))));
                    }
                }
            }
            if ($request->cookies->get(AppConstant::COOKIE_LOCALE)) {
                $cookieLocale = $request->cookies->get(AppConstant::COOKIE_LOCALE);
                $cookieCountry = $request->cookies->get(AppConstant::COOKIE_COUNTRY);
                if (isset($cookieLocale) && $cookieLocale <> '' && $cookieLocale != $locale_cookie) {
                    // modify here if the language is to be changes forom the uprl
                    return $this->redirect($this->generateUrl('login', array('_country' => $cookieCountry, '_locale' => $cookieLocale)));
                }
                if (isset($cookieCountry) && $cookieCountry <> '' && $cookieCountry != $country_cookie) {
                    return $this->redirect($this->generateUrl('login', array('_country' => $cookieCountry, '_locale' => $cookieLocale)));
                }
            }
            $activityLog = $this->get('app.activity_log');
            $postData    = $request->request->all();
            // print_r($postData['email']);
            if($postData['email'] != "" &&  $postData['email'] != null )
            {
                $captchaCode = trim(strtoupper($this->get('session')->get('_CAPTCHA')));
                $captchaCodeSubmitted = trim($postData['captchaCodereset']);
                $filename = $commFunct->saveTextAsImage();
                $response->setContent($filename['filename']);
                $captcha_image = $filename['image_captcha'];
                if ($captchaCodeSubmitted != $captchaCode)
                {
                    $error_cl         = 'alert-danger';
                    $error['success'] = false;
                    $error['message'] = $this->get('translator')->trans('Invalid captcha code');
                    $message = $this->get('translator')->trans('Invalid captcha code');
                    $error   = "";
                    $errorcl = 'alert-danger';
                    return $this->render('default/login.html.twig', array(
                        'error'   => $error, 'errorcl'   => $errorcl,
                        'message' => $message, 'data'    => $captcha_image,
                    ));
                }
                $errors = "";
                $validate_data = array($postData['email'],$captchaCodeSubmitted);
                $errors        = $commFunct->validateDataPassword($validate_data);
                if($errors > 0)
                {
                    $message = $this->get('translator')->trans('Please provide valid data');
                    $errorcl = 'alert-danger';
                    return $this->render('default/login.html.twig', array(
                        'message' => $message, 'error' => $error, 'errorcl' => $errorcl
                    ));
                }
                // 1 get user password according to the email provided
                $em      = $this->getDoctrine()->getManager();
                $email   = $postData['email'];
                $country_id = $this->getCountryCode($request);
                $locale  = $this->getCountryLocal($request);
                $result  = $em->getRepository("AppBundle:User")->findOneBy(array("email"=>$email,'status' => 1));
                if ($result)
                {
                    $user_id = $result->getIktCardNo();
                    $time    = time();
                    $token   = uniqid() . sha1($email . $time. rand(111111, 999999) . $user_id);
                    $code    = rand(111111, 999999);
                    $this->get('session')->set('resetcode', $code);
                    $data  = serialize(array('time' => $time, 'token' => $token ));
                    $result->setData($data);
                    $em->persist($result);
                    $em->flush();
                    $message = \Swift_Message::newInstance()
                        ->addTo($email)
                        ->addFrom($this->container->getParameter('mailer_user'))
                        ->setSubject(AppConstant::EMAIL_SUBJECT);
                    $message->setBody(
                        $this->container->get('templating')->render(':email-templates/forgot_password:forgot_password.html.twig', [
                                'email' => $email,
                                'link' => $code
                        ]),
                        'text/html'
                    );
                    $this->container->get('mailer')->send($message);
                    return $this->redirect($this->generateUrl('verifycode', array('_country' => $country_id, '_locale' => $locale, 'time' => $time, 'token' => $token), UrlGenerator::ABSOLUTE_URL));
                }
                else
                {
                    $token = uniqid() . sha1(time(). rand(111111, 999999) . rand(11111,9999));
                    $code  = rand(111111111111, 999999999999);
                    $this->get('session')->set('resetcode', $code);
                    return $this->redirect($this->generateUrl('verifycode', array('_country' => $country_id, '_locale' => $locale, 'time' => time(), 'token' => $token), UrlGenerator::ABSOLUTE_URL));
                }
            }
            $message = "";
            $errorcl = "";
            return $this->render('default/login.html.twig', array(
                'message' => $message, 'error' => $error, 'errorcl' => $errorcl
            ));
        }
        catch(\Exception $e)
        {
            $message = $this->get('translator')->trans('An invalid exception occurred');
            $errorcl = 'alert-danger';
            return $this->render('default/login.html.twig', array(
                'message' => $message, 'error' => $error, 'errorcl' => $errorcl
            ));
        }
        catch(AccessDeniedException $ad)
        {
            $message = $this->get('translator')->trans('An invalid exception occurred');
            $errorcl = 'alert-danger';
            return $this->render('default/login.html.twig', array(
                'message' => $message, 'error' => $error, 'errorcl' => $errorcl
            ));
        }
    }

    /**
     * @Route("/{_country}/{_locale}/forgotpasswordsms", name="forgotpasswordsms")
     */

    public function forgotPasswordsmsAction(Request $request)
    {
        try
        {
            $response  = new Response();
            $commFunct = new FunctionsController();
            $commFunct->setContainer($this->container);
            if($commFunct->checkSessionCookies($request) == false) {
                return $this->redirect($this->generateUrl('landingpage'));
            }
            // if user is logged in he should not see login page again
            if($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
                return $this->redirectToRoute('homepage', array('_country' => $request->cookies->get(AppConstant::COOKIE_COUNTRY), '_locale' => $request->cookies->get(AppConstant::COOKIE_LOCALE)));
            }
            // home page image gallery listing
            $header_listing = $this->headerTopBanner($request);
            if(!empty($header_listing))
            {
                $image_data_header = $header_listing->getImage();
                $session = new Session();
                $image_data_header = $session->set('header_image_top' , $image_data_header);
            }
            $error          = "";
            $locale_cookie  = $request->getLocale();
            $country_cookie = $request->get('_country');
            $userLang       = trim($request->query->get('lang'));
            if ($userLang   != '' && $userLang != null) {
                if ($userLang == $locale_cookie) {
                    $request->getLocale();
                    $commFunct->changeLanguage($request, $userLang);
                    $locale_cookie = $request->getLocale();
                }
                else {
                    if ($request->cookies->get(AppConstant::COOKIE_LOCALE)) {
                        return $this->redirect($this->generateUrl('login', array('_country' => $request->cookies->get(AppConstant::COOKIE_COUNTRY), '_locale' => $request->cookies->get(AppConstant::COOKIE_LOCALE))));
                    }
                }
            }
            if ($request->cookies->get(AppConstant::COOKIE_LOCALE)) {
                $cookieLocale = $request->cookies->get(AppConstant::COOKIE_LOCALE);
                $cookieCountry = $request->cookies->get(AppConstant::COOKIE_COUNTRY);
                if (isset($cookieLocale) && $cookieLocale <> '' && $cookieLocale != $locale_cookie) {
                    // modify here if the language is to be changes forom the uprl
                    return $this->redirect($this->generateUrl('login', array('_country' => $cookieCountry, '_locale' => $cookieLocale)));
                }
                if (isset($cookieCountry) && $cookieCountry <> '' && $cookieCountry != $country_cookie) {
                    return $this->redirect($this->generateUrl('login', array('_country' => $cookieCountry, '_locale' => $cookieLocale)));
                }
            }
            $activityLog = $this->get('app.activity_log');
            $restClient  = $this->get('app.rest_client')->IsAdmin(true);
            $postData    = $request->request->all();
            // print_r($postData['email']);
            if($postData['email'] != "" &&  $postData['email'] != null ) {
                $captchaCode = trim(strtoupper($this->get('session')->get('_CAPTCHA')));
                $captchaCodeSubmitted = trim($postData['captchaCodereset']);
                $filename = $commFunct->saveTextAsImage();
                $response->setContent($filename['filename']);
                $captcha_image = $filename['image_captcha'];
                if ($captchaCodeSubmitted != $captchaCode) {
                    $error_cl         = 'alert-danger';
                    $error['success'] = false;
                    $error['message'] = $this->get('translator')->trans('Invalid captcha code');
                    $message = $this->get('translator')->trans('Invalid captcha code');
                    $error   = "";
                    $errorcl = 'alert-danger';
                    return $this->render('default/login.html.twig', array(
                        'error'   => $error, 'errorcl'   => $errorcl,
                        'message' => $message, 'data'    => $captcha_image,
                    ));
                }
                $errors = "";
                $validate_data = array($postData['email'],$captchaCodeSubmitted);
                $errors        = $commFunct->validateDataPassword($validate_data);
                if($errors > 0)
                {
                    $message = $this->get('translator')->trans('Please provide valid data');
                    $errorcl = 'alert-danger';
                    return $this->render('default/login.html.twig', array(
                        'message' => $message, 'error' => $error, 'errorcl' => $errorcl
                    ));
                }
                // 1 get user password according to the email provided
                $em      = $this->getDoctrine()->getManager();
                $email   = $postData['email'];
                $verifcodemediam   = $postData['verifcodemediam'];
                $country_id = $this->getCountryCode($request);
                $locale  = $this->getCountryLocal($request);
                $result  = $em->getRepository("AppBundle:User")->findOneBy(array("email"=>$email , 'status' => 1));
                if ($result) {
                    $user_id = $result->getIktCardNo();
                    $time    = time();
                    $token   = uniqid() . sha1($email . $time. rand(111111, 999999) . $user_id);
                    $code    = rand(111111, 999999);
                    $this->get('session')->set('resetcode', $code);
                    $this->get('session')->set('resetcode_typeemail_sms', $verifcodemediam);
                    $data  = serialize(array('time' => $time, 'token' => $token ));
                    $result->setData($data);
                    $em->persist($result);
                    $em->flush();
                    if($verifcodemediam == 1)
                    {
                        $message = \Swift_Message::newInstance()
                                ->addTo($email)
                                ->addFrom($this->container->getParameter('mailer_user'))
                                ->setSubject(AppConstant::EMAIL_SUBJECT);
                        $message->setBody(
                            $this->container->get('templating')->render(':email-templates/forgot_password:forgot_password.html.twig', [
                                'email' => $email,
                                'link'  => $code ]
                            ), 'text/html' );
                        $this->container->get('mailer')->send($message);
                        $message_sms = $this->get('translator')->trans('Reset password email with code sent to user');
                        $activityLog->logEvent(AppConstant::ACTIVITY_SEND_SMS_RESET_SUCCESS, $user_id,
                            array('iktissab_card_no' => $user_id, 'message' => $message_sms, 'session' => serialize($result)),
                            null,null
                        );
                    }
                    else
                    {
                        $smsService  = $this->get('app.sms_service');
                        $user_iktissabId = $user_id;
                        // echo '<br>';
                        $message_sms = $this->get('translator')->trans("Please enter the sms code")." : ".$code;
                        $url         = $request->getLocale() . '/api/' . $user_iktissabId . '/userdata.json';
                        $data_sms    = $restClient->restGet(AppConstant::WEBAPI_URL . $url, array('Country-Id' => strtoupper($request->get('_country'))));
                        // print_r($data_sms); exit;
                        $status_sms  = $smsService->sendSms($data_sms['user'][1], $message_sms, $request->get('_country'));
                        if ($status_sms == 1)
                        {
                            $message_sms = $this->get('translator')->trans('Reset password sms code sent to user');
                            $activityLog->logEvent(AppConstant::ACTIVITY_SEND_SMS_RESET_SUCCESS, $user_iktissabId,
                                array('iktissab_card_no' => $user_id, 'message' => $message_sms, 'session' => serialize($result)),
                                null,null );
                        }
                        else
                        {
                            $message_sms = $this->get('translator')->trans("Reset password sms not sent to user");
                            $activityLog->logEvent(AppConstant::ACTIVITY_SEND_SMS_RESET_FAILED, $user_iktissabId,
                                array('iktissab_card_no' => $user_id, 'message' => $message_sms, 'session' => serialize($result)),
                                null,null );
                        }
                    }
                    return $this->redirect($this->generateUrl('verifycodesms', array('_country' => $country_id, '_locale' => $locale, 'time' => $time, 'token' => $token), UrlGenerator::ABSOLUTE_URL));
                }
                else
                {
                    $this->get('session')->set('resetcode_typeemail_sms', $verifcodemediam);
                    $token = uniqid() . sha1(time(). rand(111111, 999999) . rand(11111,9999));
                    $code  = rand(111111111111, 999999999999);
                    $this->get('session')->set('resetcode', $code);
                    return $this->redirect($this->generateUrl('verifycodesms', array('_country' => $country_id, '_locale' => $locale, 'time' => time(), 'token' => $token), UrlGenerator::ABSOLUTE_URL));
                }
            }
            $message = "";
            $errorcl = "";
            return $this->render('default/login.html.twig', array(
                'message' => $message, 'error' => $error, 'errorcl' => $errorcl
            ));
        }
        catch(\Exception $e)
        {
            $message = $this->get('translator')->trans('An invalid exception occurred');
            $errorcl = 'alert-danger';
            return $this->render('default/login.html.twig', array(
                'message' => $message, 'error' => $error, 'errorcl' => $errorcl
            ));
        }
        catch(AccessDeniedException $ad)
        {
            $message = $this->get('translator')->trans('An invalid exception occurred');
            $errorcl = 'alert-danger';
            return $this->render('default/login.html.twig', array(
                'message' => $message, 'error' => $error, 'errorcl' => $errorcl
            ));
        }
    }

    public function forgotPasswordSohail(Request $request)
    {
        try
        {
            $commFunct = new FunctionsController();
            $commFunct->setContainer($this->container);
            if($commFunct->checkSessionCookies($request) == false)
            {
                return $this->redirect($this->generateUrl('landingpage'));
            }
            // if user is logged in he should not see login page again
            if($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY'))
            {
                return $this->redirectToRoute('homepage', array('_country' => $request->cookies->get(AppConstant::COOKIE_COUNTRY), '_locale' => $request->cookies->get(AppConstant::COOKIE_LOCALE)));
            }
            $error          = "";
            $locale_cookie  = $request->getLocale();
            $country_cookie = $request->get('_country');
            $userLang       = trim($request->query->get('lang'));
            if ($userLang   != '' && $userLang != null)
            {
                if ($userLang == $locale_cookie)
                {
                    $request->getLocale();
                    $commFunct->changeLanguage($request, $userLang);
                    $locale_cookie = $request->getLocale();
                }
                else
                {
                    if ($request->cookies->get(AppConstant::COOKIE_LOCALE)) {
                        return $this->redirect($this->generateUrl('login', array('_country' => $request->cookies->get(AppConstant::COOKIE_COUNTRY), '_locale' => $request->cookies->get(AppConstant::COOKIE_LOCALE))));
                    }
                }
            }
            if ($request->cookies->get(AppConstant::COOKIE_LOCALE)) {
                $cookieLocale = $request->cookies->get(AppConstant::COOKIE_LOCALE);
                $cookieCountry = $request->cookies->get(AppConstant::COOKIE_COUNTRY);
                if (isset($cookieLocale) && $cookieLocale <> '' && $cookieLocale != $locale_cookie) {
                    // modify here if the language is to be changes forom the uprl
                    return $this->redirect($this->generateUrl('login', array('_country' => $cookieCountry, '_locale' => $cookieLocale)));
                }
                if (isset($cookieCountry) && $cookieCountry <> '' && $cookieCountry != $country_cookie) {
                    return $this->redirect($this->generateUrl('login', array('_country' => $cookieCountry, '_locale' => $cookieLocale)));
                }
            }
            $activityLog = $this->get('app.activity_log');
            $posted      = array();
            $postData    = $request->request->all();

            if ($postData)
            {
                // 1 get user password according to the email provided
                $em      = $this->getDoctrine()->getManager();
                $conn    = $em->getConnection();
                $email   = $postData['email'];
                $country_id = $this->getCountryCode($request);
                $locale  = $this->getCountryLocal($request);
                $stm     = $conn->prepare('SELECT * FROM user WHERE  email = ?   ');
                // $stm  = $conn->prepare('SELECT * FROM user WHERE country = ? AND email = ?   ');
                $stm->bindValue(1, $email);
                // $stm->bindValue(2, $country_id);
                // here checking the others equal to 1.
                $stm->execute();
                $result = $stm->fetchAll();
                if ($result) {
                    // send email
                    $email = $email; //'sa.aspire@gmail.com';
                    //--> $email = $result[0]['email'];
                    //--> print_r($result);
                    $user_id = $result[0]['ikt_card_no'];
                    $user_id_en = $user_id; // $this->encrypt($user_id, AppConstant::SECRET_KEY_FP);
                    // $user_id_en = base64_encode($user_id . AppConstant::SECRET_KEY_FP);
                    $user_id_en = $this->base64url_encode($user_id . AppConstant::SECRET_KEY_FP);
                    $time  = time();
                    $token = uniqid() . sha1($email . time() . rand(111111, 999999) . $user_id);
                    //$link  = $this->generateUrl('', array('_country' => $country_id, '_locale' => $locale, 'time' => $time, 'token' => $token), UrlGenerator::ABSOLUTE_URL);
                    $code  = rand(111111, 999999);
                    $this->get('session')->set('resetcode', $code);
                    $data  = serialize(array('time' => $time, 'token' => $token ));
                    $data_values = array(
                        $data,
                        $user_id,
                    );
                    $stm = $conn->executeUpdate('UPDATE user  SET  
                                                    data    = ?
                                                    WHERE ikt_card_no = ? ', $data_values);

                    /*$data_values = array(
                        $data,
                        $country_id,
                        $user_id,
                    );

                    $stm = $conn->executeUpdate('UPDATE user  SET
                                                        data    = ?
                                                        WHERE country = ?  AND ikt_card_no = ? ', $data_values);*/


                    if ($stm == 1)
                    {
                        $message = \Swift_Message::newInstance();
                        $request = new Request();
                        // --> $email   = 'sa.aspire@gmail.com';
                        $message->addTo($email);
                        $message->addFrom($this->container->getParameter('mailer_user'))
                            ->setSubject(AppConstant::EMAIL_SUBJECT)
                            ->setBody(
                                $this->container->get('templating')->render(':email-templates/forgot_password:forgot_password.html.twig', [
                                    'email' => $email,
                                    'link' => $code
                                ]),
                                'text/html'
                            );

                        if ($this->container->get('mailer')->send($message)) {
                            $message = $this->get('translator')->trans('If there is an account associated with this E-mail then you will receive an email with a code to reset your password');
                            $activityLog->logEvent(AppConstant::ACTIVITY_FORGOT_PASSWORD_SUCCESS, $user_id,
                                array('iktissab_card_no' => $user_id, 'message' => $message, 'session' => serialize($result)),
                                null,null
                            );
                            $errorcl = 'alert-success';
                            return $this->redirect($this->generateUrl('verifycode', array('_country' => $country_id, '_locale' => $locale, 'time' => $time, 'token' => $token), UrlGenerator::ABSOLUTE_URL));

                        } else {
                            $message = $this->get('translator')->trans('If there is an account associated with this E-mail then you will receive an email with a code to reset your password');
                            $activityLog->logEvent(AppConstant::ACTIVITY_FORGOT_PASSWORD_ERROR, $user_id,
                               array('iktissab_card_no' => $user_id, 'message' => $message, 'session' => $result),
                                null,null
                                );
                            $errorcl = 'alert-danger';
                            return $this->render('default/login.html.twig', array(
                                'message' => $message, 'error' => $error, 'errorcl' => $errorcl
                            ));
                        }
                    }
                }
                else
                {
                    // $message = $this->get('translator')->trans('Sorry , ') . $email . $this->get('translator')->trans('is not recognized as a user name or an e-mail address');
                    $message = $this->get('translator')->trans('If there is an account associated with this E-mail then you will receive an email with a code to reset your password');

                    //$activityLog->logEvent(AppConstant::ACTIVITY_FORGOT_PASSWORD_ERROR, '0' . $email, array('iktissab_card_no' => 'unknownuser ' . $email, 'message' => $message, 'session' => $result));
                    $errorcl = 'alert-success';
                    return $this->render('default/login.html.twig', array(
                        'message' => $message, 'error' => $error, 'errorcl' => $errorcl
                    ));
                }
            }
            $message = "";
            $errorcl = "";
            return $this->render('default/login.html.twig', array(
                'message' => $message, 'error' => $error, 'errorcl' => $errorcl
            ));
        }
        catch(\Exception $e)
        {
            $message = $this->get('translator')->trans('An invalid exception occurred');
            $errorcl = 'alert-danger';
            return $this->render('default/login.html.twig', array(
                'message' => $message, 'error' => $error, 'errorcl' => $errorcl
            ));
        }
        catch(AccessDeniedException $ad)
        {
            $message = $this->get('translator')->trans('An invalid exception occurred');
            $errorcl = 'alert-danger';
            return $this->render('default/login.html.twig', array(
                'message' => $message, 'error' => $error, 'errorcl' => $errorcl
            ));
        }

    }


    private function getCountryCode(Request $request)
    {
        return $country_id = $request->get('_country');
    }

    private function getCountryLocal(Request $request)
    {
        return $locale = $request->getLocale();
    }



    /**
     * @Route("/{_country}/{_locale}/resetpassword/{time}/{token}", name="resetpassword")
     * @param $time
     * @param $token
     */
    public function resetPasswordAction(Request $request, $time, $token)
    {
        try
        {
            $commFunct = new FunctionsController();
            $commFunct->setContainer($this->container);
            $activityLog = $this->get('app.activity_log');
            $em = $this->getDoctrine()->getManager();
            $time = (integer)$time;
            $dataValue = serialize(array('time' => $time, 'token' => $token));
            // todo:
            // $id    = $id;
            // $this->decrypt($id, AppConstant::SECRET_KEY_FP);
            // $id = base64_decode($id . AppConstant::SECRET_KEY_FP);
            // $id = $this->base64url_decode($id . AppConstant::SECRET_KEY_FP);
            $user = $em->getRepository('AppBundle:User')->findOneBy(array("data" => $dataValue));
            //$user  = $em->getRepository('AppBundle:User')->findOneBy(array("data" => $dataValue , 'iktCardNo' => $id));
            if (isset($user) && $user != null)
            {
                $user_email = $user->getEmail();
                $id = $user->getIktCardNo();
            }
            else
            {
                $user_email = '';
                $id = '';
            }

            $form = $this->createFormBuilder(array('attr' => array('novalidate' => 'novalidate')))
                ->add('email', EmailType::class,
                    array(
                        'label_attr' => ['class' => 'formLayout    form_labels'],
                        'attr' => array('class' => 'form-control-modified col-lg-12 col-md-12 col-sm-12 col-xs-12 formLayout'),

                        'label' => 'E-mail address', 'disabled' => true,
                        'constraints' => array(
                            new NotBlank(array('message' => 'This field is required')),
                        )))
                ->add('password', RepeatedType::class, array(
                    'type' => PasswordType::class,
                    'constraints' => array(
                        new NotBlank(),
                        new Length(array('minMessage' => 'Password must be at least 6 characters', 'maxMessage' => 'Password must not be greater then 40 characters', 'min' => 6, 'max' => 40))
                    ),
                    'invalid_message' => 'Password and confirm password must match',
                    'first_options' => array('label' => 'Enter New Password',
                        'attr' => array('class' => ' form-control-modified col-lg-12 col-md-12 col-sm-12 col-xs-12 formLayout'),
                        'label_attr' => ['class' => 'required formLayout  form_labels'],
                    ),
                    'second_options' => array('label' => "Confirm Password",
                        'attr' => array('class' => '  form-control-modified col-lg-12 col-md-12 col-sm-12 col-xs-12 formLayout'),
                        'label_attr' => ['class' => 'required formLayout    form_labels'],
                    ),

                ))
                ->add('submit', SubmitType::class, array('label' => 'Reset Password', 'attr' => array('class' => 'btn btn-primary')))
                ->getForm();
            $form->get('email')->setData($user_email);
            // echo $data['time'] ;
            // echo '<br>';
            // echo time();


            $data_form['show_form'] = 1;
            if ($id != ""  && $id != null) {
                $data = unserialize($user->getData());
                $currentPassword = $user->getPassword();
                if (strtotime('+1 day', $data['time']) > time()) {
                    $form->handleRequest($request);
                    if ($form->isSubmitted() && $form->isValid()) {
                        $formData = $form->getData();
                        $newPassword = $formData['password'];
                        if ($currentPassword == md5($newPassword)) {
                            $message = $this->get('translator')->trans('New Password and old password must not be the same');
                            $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_RESETPASSWORD_ERROR, $id,
                                array('iktissab_card_no' => $id, 'message' => $message, 'session' => $user),
                                null,null
                                );
                            $errorcl = 'alert-danger';
                            return $this->render('front/resetpassword.html.twig', array(
                                'form' => $form->createView(), 'message' => $message, 'data' => $data_form,
                                'errorcl' => $errorcl

                            ));
                        } else
                        {
                            /*****************/
                            $C_id = $id;
                            // $reset_password = $this->resetPasswordOffline($request, md5($formData['password']) , $C_id);
                            // As offline DB is removed therefore no need to check resetPasswordOffline function
                            $reset_password = true;
                            /*****************/
                            if($reset_password == true)
                            {
                                $user->setPassword(md5($formData['password']));
                                $user->setData('');
                                $em->persist($user);
                                $em->flush();
                                $message = $this->get('translator')->trans('Your password has been reset successfully');
                                $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_RESETPASSWORD_SUCCESS, $id,
                                    array('iktissab_card_no' => $id, 'message' => $message, 'session' => $user),
                                    null,null
                                );
                                $errorcl = 'alert-success';
                                return $this->render('front/resetpassword.html.twig', array(
                                    'form' => $form->createView(), 'message' => $message, 'data' => $data_form,
                                    'errorcl' => $errorcl
                             
                                ));
                            }
                            else
                            {
                                $message = $this->get('translator')->trans('Your password has not been reset successfully');
                                $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_RESETPASSWORD_SUCCESS, $id,
                                    array('iktissab_card_no' => $id, 'message' => $message, 'session' => $user),
                                    null,null
                                    );
                                $errorcl = 'alert-success';
                                return $this->render('front/resetpassword.html.twig', array(
                                    'form' => $form->createView(), 'message' => $message, 'data' => $data_form,
                                    'errorcl' => $errorcl

                                ));  

                            }



                        }
                    } else {
                        $message = $this->get('translator')->trans('Please reset your password');
                        $errorcl = 'alert-success';
                        return $this->render('front/resetpassword.html.twig', array(
                            'form' => $form->createView(), 'message' => $message, 'data' => $data_form,
                            'errorcl' => $errorcl
                        ));
                    }
                } else {
                    $message = $this->get('translator')->trans('Your link to reset password has been expired');
                    $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_RESETPASSWORD_ERROR, $id,
                        array('iktissab_card_no' => $id, 'message' => $message, 'session' => $user),
                        null,null
                        );
                    $errorcl = 'alert-danger';
                    return $this->render('front/resetpassword.html.twig', array(
                        'form' => $form->createView(), 'message' => $message, 'data' => $data_form,
                        'errorcl' => $errorcl
                    ));
                }
            } else {
                $message = $this->get('translator')->trans('Your link to reset password has been expired');
                $data_form['show_form'] = 0;
                $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_RESETPASSWORD_ERROR, $id,
                    array('iktissab_card_no' => $id, 'message' => $message, 'session' => $user),
                    null,null
                    );
                $errorcl = 'alert-danger';
                return $this->render('front/resetpassword.html.twig', array(
                    'form' => $form->createView(), 'message' => $message, 'data' => $data_form,
                    'errorcl' => $errorcl
                ));
            }
        }
        catch(\Exception $e)
        {
            $message = $this->get('translator')->trans('An invalid exception occurred');
            $data_form['show_form'] = 0;
            $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_RESETPASSWORD_ERROR, $id,
                array('iktissab_card_no' => $id, 'message' => $message, 'session' => $user),
                null,null
            );
            $errorcl = 'alert-danger';
            return $this->render('front/resetpassword.html.twig', array(
                'form' => $form->createView(), 'message' => $message, 'data' => $data_form,
                'errorcl' => $errorcl
            ));
        }
    }




    /**
     * @Route("/{_country}/{_locale}/resetpasswordcode/{time}/{token}", name="resetpasswordcode")
     * @param $time
     * @param $token
     */
    public function resetPasswordCodeAction(Request $request, $time, $token)
    {
        try
        {
            if($this->get('session')->get('resetcode_reference') == "" || $this->get('session')->get('resetcode_reference') == null){
                return $this->redirect($this->generateUrl('login', array('_country' => $request->cookies->get(AppConstant::COOKIE_COUNTRY), '_locale' => $request->cookies->get(AppConstant::COOKIE_LOCALE))));
            }
            $commFunct = new FunctionsController();
            $commFunct->setContainer($this->container);
            $activityLog = $this->get('app.activity_log');
            $em          = $this->getDoctrine()->getManager();
            $time        = (integer)$time;
            $dataValue   = serialize(array('time' => $time , 'token' => $token));
            $resetcode   = $this->get('session')->get('resetcode');
            // $id    = $id;
            // $this->decrypt($id, AppConstant::SECRET_KEY_FP);
            // $id = base64_decode($id . AppConstant::SECRET_KEY_FP);
            // $id = $this->base64url_decode($id . AppConstant::SECRET_KEY_FP);
            $user        = $em->getRepository('AppBundle:User')->findOneBy(array("data" => $dataValue));
            // $user     = $em->getRepository('AppBundle:User')->findOneBy(array("data" => $dataValue , 'iktCardNo' => $id));
            // print_r($user);
            if (isset($user) && $user != null)
            {
                $user_email = $user->getEmail();
                $id = $user->getIktCardNo();
            }
            else
            {
                $user_email = '';
                $id = '';
            }

            // home page image gallery listing
            $header_listing = $this->headerTopBanner($request);
            if(!empty($header_listing))
            {
                $image_data_header = $header_listing->getImage();
                $session = new Session();
                $image_data_header = $session->set('header_image_top' , $image_data_header);
            }
            //$form = $this->createFormBuilder(array('attr' => array('novalidate' => 'novalidate', 'name' => 'resetpassword')))
            $form = $this->get('form.factory')->createNamedBuilder('reset_password','Symfony\Component\Form\Extension\Core\Type\FormType',null, ['attr' => ['novalidate' => 'novalidate']])
                ->add('password', RepeatedType::class, array(
                    'type' => PasswordType::class,
                    'constraints' => array(
                        new NotBlank(array('message' =>  'This field is required')),
                        new Length(array('min'=>6, 'minMessage'=>'Password must be at least 6 characters'))
                    ),
                    'invalid_message' => 'Password and confirm password must match',
                    'first_options' => array('label' => 'Enter New Password',
                        'attr' => array('class' => ' form-control-modified col-lg-12 col-md-12 col-sm-12 col-xs-12 formLayout'),
                        'label_attr' => ['class' => 'required formLayout  form_labels'],
                    ),
                    'second_options' => array('label' => "Confirm Password",
                        'attr' => array('class' => '  form-control-modified col-lg-12 col-md-12 col-sm-12 col-xs-12 formLayout'),
                        'label_attr' => ['class' => 'required formLayout    form_labels'],
                    ),
                ))
                ->add('reset', SubmitType::class, array('label' => 'Reset Password', 'attr' => array('class' => 'btn btn-primary')))
                ->getForm();
            $data_form['show_form'] = 1;
            if ($id != ""  && $id != null) {
                $data            = unserialize($user->getData());
                $currentPassword = $user->getPassword();
                if (strtotime('+1 day', $data['time']) > time()) {
                    $form->handleRequest($request);
                    if ($form->isSubmitted() && $form->isValid())
                    {
                        $formData = $form->getData();
                        $submitted_Data = $request->request->all();
                        $submitted_Data['reset_password']['password']['first'];
                        $submitted_Data['reset_password']['password']['second'];
                        if($submitted_Data['reset_password']['password']['first'] != $submitted_Data['reset_password']['password']['second']){
                            $message = $this->get('translator')->trans('Password fields must match');
                            $errorcl = 'alert-danger';
                            $data_form['show_form'] = 1;
                            return $this->render('front/resetpassword.html.twig', array(
                                'reset_password_form' => $form->createView(), 'message' => $message, 'data' => $data_form,
                                'errorcl' => $errorcl
                            ));
                        }
                        $newPassword     = $formData['password'];
                        $confirmPassword = $formData['password'];
                        $validate_data   = array( $submitted_Data['reset_password']['password']['first'],$submitted_Data['reset_password']['password']['second']);
                        $errors          = $commFunct->validateDataPassword($validate_data);
                        if($errors > 0)
                        {
                            $message = $this->get('translator')->trans('Please provide valid data');
                            $errorcl = 'alert-danger';
                            $data_form['show_form'] = 1;
                            return $this->render('front/resetpassword.html.twig', array(
                                'reset_password_form' => $form->createView(), 'message' => $message, 'data' => $data_form,
                                'errorcl'           => $errorcl
                            ));
                        }
                        $resetcode_verification   = $this->get('session')->get('resetcode');
                        if(!empty($resetcode_verification))
                        {
                            $this->get('session')->clear();
                            /*****************/
                            $C_id = $id;
                            // $reset_password = $this->resetPasswordOffline($request, md5(strip_tags($formData['password'])), $C_id);
                            // As offline DB is removed therefore no need to check resetPasswordOffline function
                            $reset_password = true;
                            /*****************/
                            if ($reset_password == true) {
                                $user->setPassword(md5(strip_tags($formData['password'])));
                                $user->setData('');
                                $em->persist($user);
                                $em->flush();
                                $data_form['show_form'] = 1;
                                $message = $this->get('translator')->trans('Your password has been reset successfully');
                                $errorcl = 'alert-success';
                                return $this->render('front/resetpassword.html.twig', array(
                                    'reset_password_form' => $form->createView(), 'message' => $message, 'data' => $data_form,
                                    'errorcl' => $errorcl
                                ));
                            }
                            else
                            {
                                $message = $this->get('translator')->trans('Your password has not been reset successfully');
                                $errorcl = 'alert-danger';
                                $data_form['show_form'] = 1;
                                return $this->render('front/resetpassword.html.twig', array(
                                    'reset_password_form' => $form->createView(), 'message' => $message, 'data' => $data_form,
                                    'errorcl' => $errorcl
                                ));
                            }
                        }
                        else
                        {
                            $message = $this->get('translator')->trans('Please enter correct verification code');
                            $errorcl = 'alert-danger';
                            return $this->render('front/resetpassword.html.twig', array(
                                'reset_password_form' => $form->createView(), 'message' => $message, 'data' => $data_form,
                                'errorcl' => $errorcl
                            ));
                        }
                    }
                    else
                    {
                        $message = $this->get('translator')->trans('Please reset your password');
                        $errorcl = 'alert-success';
                        return $this->render('front/resetpassword.html.twig', array(
                            'reset_password_form' => $form->createView(), 'message' => $message, 'data' => $data_form,
                            'errorcl' => $errorcl
                        ));
                    }
                }
                else
                {
                    $message = $this->get('translator')->trans('Please enter correct verification code');
                    $errorcl = 'alert-danger';
                    return $this->render('front/resetpassword.html.twig', array(
                        'reset_password_form' => $form->createView(), 'message' => $message, 'data' => $data_form,
                        'errorcl' => $errorcl
                    ));
                }
            } else {
                $message = $this->get('translator')->trans('Your link to reset password has been expired');
                $data_form['show_form'] = 0;
                $errorcl = 'alert-danger';
                return $this->render('front/resetpassword.html.twig', array(
                    'reset_password_form' => $form->createView(), 'message' => $message, 'data' => $data_form,
                    'errorcl' => $errorcl
                ));
            }
        }
        catch(\Exception $e)
        {
            $message = $this->get('translator')->trans('An invalid exception occurred');
            $data_form['show_form'] = 1;
            $errorcl = 'alert-danger';
            return $this->render('front/resetpassword.html.twig', array(
                'reset_password_form' => $form->createView(), 'message' => $message, 'data' => $data_form,
                'errorcl' => $errorcl
            ));
        }
    }

    /**
     * @Route("/{_country}/{_locale}/verifycodesms/{time}/{token}", name="verifycodesms")
     * @param $time
     * @param $token
     */
    public function verifycodesmsAction(Request $request, $time, $token)
    {
        try
        {
            $activityLog = $this->get('app.activity_log');
            $this->get('session')->get('resetcode_counter');
            if($this->get('session')->get('resetcode', '') == "" ) {
                return $this->redirect($this->generateUrl('login', array('_country' => $request->cookies->get(AppConstant::COOKIE_COUNTRY), '_locale' => $request->cookies->get(AppConstant::COOKIE_LOCALE))));
            }
            if($this->get('session')->get('resetcode_counter','') === '')
            {
                $this->get('session')->set('resetcode_counter', 4);
                $resetcode_counter = 4;
            }
            else
            {
                $resetcode_counter = (integer)$this->get('session')->get('resetcode_counter');
            }
            $em          = $this->getDoctrine()->getManager();
            $time        = (integer)$time;
            $dataValue   = serialize(array('time' => $time, 'token' => $token));
            $country_id  = $this->getCountryCode($request);
            $locale      = $this->getCountryLocal($request);
            $user        = $em->getRepository('AppBundle:User')->findOneBy(array("data" => $dataValue));
            $id   = ($user) ? $user->getIktCardNo():'';
            $form = $this->createFormBuilder(array('attr' => array('novalidate' => 'novalidate' , 'name' => 'myFormName')))
                ->add('resetcode', TextType::class, array(
                        'label' => 'Verification code:',
                        'attr'  => array('class' => ' form-control-modified col-lg-12 col-md-12 col-sm-12 col-xs-12 formLayout pass-reset-code' , 'maxlength'=> 6 ),
                        'label_attr'  => ['class' => 'required formLayout  form_labels'],
                        'constraints' => array(
                            new NotBlank(array('message' =>  'This field is required')) ,
                            new Assert\Regex(
                                array(
                                    'pattern' => '/^[0-9]+$/',
                                    'match'   => true,
                                    'message' => 'Invalid number')
                            )))
                )
                ->add('submit', SubmitType::class, array('label' => 'Submit', 'attr' => array('class' => 'btn btn-primary')))
                ->getForm();
            $data_form['show_form'] = 1;
            $form->handleRequest($request);

            $this->get('session')->get('resetcode_typeemail_sms');
            if($this->get('session')->get('resetcode_typeemail_sms') == 1) {
                $message = $this->get('translator')->trans('If there is an account associated with this E-mail then you will receive an email with a code to reset your password');
            }
            else
            {
                $message = $this->get('translator')->trans('If there is an account associated with this E-mail then you will receive an SMS with a code to reset your password');
            }

            if($resetcode_counter == 0)
            {
                $data_form['show_form'] = 0;
                $this->get('session')->set('resetcode_counter', '');
                // -->
                $this->get('session')->set('resetcode', '');
                return $this->redirect($this->generateUrl('login', array('_country' => $request->cookies->get(AppConstant::COOKIE_COUNTRY), '_locale' => $request->cookies->get(AppConstant::COOKIE_LOCALE))));
            }
            if($id != "" )
            {
                $data            = unserialize($user->getData());
                if (strtotime('+1 day', $data['time']) > time())
                {
                    if ($form->isSubmitted() && $form->isValid())
                    {
                        $resetcode_counter = $resetcode_counter - 1;
                        $formData = $form->getData();
                        $resetcode_verification   = (integer)$this->get('session')->get('resetcode');
                        $sub_code =  (integer)$formData['resetcode'];
                        if ($sub_code == $resetcode_verification) {
                            // this make sure resetpasswordcode is not called directly
                            $this->get('session')->set('resetcode_reference' , "1");
                            /*****************/
                            $C_id = $id;
                            /*****************/
                            $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_RESETPASSWORD_SUCCESS, $id,
                                array('iktissab_card_no' => $id, 'message' => $message),
                                null,null
                                );
                            $this->get('session')->set('resetcode_counter', '');
                            return $this->redirect($this->generateUrl('resetpasswordcode', array('_country' => $country_id, '_locale' => $locale, 'time' => $time, 'token' => $token), UrlGenerator::ABSOLUTE_URL));
                        }
                        else
                        {
                            $errorcl = 'alert-danger';
                            if($resetcode_counter == 0)
                            {
                                $message = $this->get('translator')->trans('Your link to reset password has been expired');
                                /*$data_form['show_form'] = 0;
                                $this->get('session')->set('resetcode_counter', '');
                                $this->get('session')->set('resetcode', '');
                                return $this->redirect($this->generateUrl('login', array('_country' => $request->cookies->get(AppConstant::COOKIE_COUNTRY), '_locale' => $request->cookies->get(AppConstant::COOKIE_LOCALE))));*/
                            }
                            else
                            {
                                $message = $this->get('translator')->trans('Please enter correct verification code');
                            }
                            $resetcode_counter;
                            $this->get('session')->set('resetcode_counter', $resetcode_counter);
                            return $this->render('front/verifycode.html.twig', array(
                                'form'    => $form->createView(), 'message' => $message, 'data' => $data_form,
                                'errorcl' => $errorcl
                            ));
                        }
                    }
                    else
                    {
                        $errorcl = 'alert-success';
                        $data_form['show_form'] = 1;
                        $this->get('session')->set('resetcode_counter', $resetcode_counter);
                        return $this->render('front/verifycode.html.twig', array(
                            'form'    => $form->createView(), 'message' => $message, 'data' => $data_form,
                            'errorcl' => $errorcl
                        ));
                    }
                }
                else
                {
                    $errorcl = 'alert-danger';
                    $message = $this->get('translator')->trans('Your link to reset password has been expired');
                    $this->get('session')->set('resetcode_counter', $resetcode_counter);
                    return $this->render('front/verifycode.html.twig', array(
                        'form'    => $form->createView(), 'message' => $message, 'data' => $data_form,
                        'errorcl' => $errorcl
                    ));
                }
            }
            else
            {
                // 1. check if the reset code is valid
                // 2. then print message to expiry
                // 3. this is for security to prevent guessable user name
                if($resetcode_counter != 0)
                {
                    if ($form->isSubmitted() && $form->isValid()) {
                        $resetcode_counter--;
                        // here user will always receive this message as he dont have account
                        // untill the reset counter expires due to security
                        $errorcl = 'alert-danger';
                        $message = $this->get('translator')->trans('Please enter correct verification code');
                        $this->get('session')->set('resetcode_counter', $resetcode_counter);
                        $data_form['show_form'] = 1;
                        return $this->render('front/verifycode.html.twig', array(
                            'form'    => $form->createView(), 'message' => $message, 'data' => $data_form,
                            'errorcl' => $errorcl
                        ));
                    }
                    else
                    {
                        $errorcl = 'alert-success';
                        $data_form['show_form'] = 1;
                        if($this->get('session')->get('resetcode_typeemail_sms') == 1) {
                            $message = $this->get('translator')->trans('If there is an account associated with this E-mail then you will receive an email with a code to reset your password');
                        }
                        else
                        {
                            $message = $this->get('translator')->trans('If there is an account associated with this E-mail then you will receive an SMS with a code to reset your password');
                        }
                        $this->get('session')->set('resetcode_counter', $resetcode_counter);
                        return $this->render('front/verifycode.html.twig', array(
                            'form' => $form->createView(), 'message' => $message, 'data' => $data_form,
                            'errorcl' => $errorcl
                        ));
                    }
                }
                else
                {
                    $message = $this->get('translator')->trans('Your link to reset password has been expired');
                    $data_form['show_form'] = 0;
                    $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_RESETPASSWORD_ERROR, $id,
                        array('iktissab_card_no' => $id, 'message' => $message, 'session' => $user),
                        null, null);
                    $errorcl = 'alert-danger';
                    return $this->render('front/verifycode.html.twig', array(
                        'form' => $form->createView(),
                        'message' => $message,
                        'data' => $data_form,
                        'errorcl' => $errorcl
                    ));
                }
            }
        }
        catch(\Exception $e)
        {
            $message = $this->get('translator')->trans('Unable to process your request at this time.Please try later');
            $data_form['show_form'] = 0;
            $errorcl = 'alert-danger';
            return $this->render('front/verifycode.html.twig', array(
                'form'    => $form->createView(), 'message' => $message, 'data' => $data_form,
                'errorcl' => $errorcl ));
        }
    }

    /**
     * @Route("/{_country}/{_locale}/verifycode/{time}/{token}", name="front_verifycode")
     * @param $time
     * @param $token
     */
    public function verifycodeAction(Request $request, $time, $token)
    {
        try
        {
            $activityLog = $this->get('app.activity_log');
            echo $this->get('session')->set('resetcode_counter');
            echo '<br>';
            echo "===>>".$this->get('session')->get('resetcode');
            if($this->get('session')->get('resetcode', '') == "" ) {
                return $this->redirect($this->generateUrl('login', array('_country' => $request->cookies->get(AppConstant::COOKIE_COUNTRY), '_locale' => $request->cookies->get(AppConstant::COOKIE_LOCALE))));
            }

            if($this->get('session')->get('resetcode_counter','') === '')
            {
                $this->get('session')->set('resetcode_counter', 4);
                $resetcode_counter = 4;
            }
            else
            {
                $resetcode_counter = (integer)$this->get('session')->get('resetcode_counter');
            }
            $em          = $this->getDoctrine()->getManager();
            $time        = (integer)$time;
            $dataValue   = serialize(array('time' => $time, 'token' => $token));
            $country_id  = $this->getCountryCode($request);
            $locale      = $this->getCountryLocal($request);
            $user        = $em->getRepository('AppBundle:User')->findOneBy(array("data" => $dataValue));
            $id   = ($user) ? $user->getIktCardNo():'';
            $form = $this->createFormBuilder(array('attr' => array('novalidate' => 'novalidate' , 'name' => 'myFormName')))
                ->add('resetcode', TextType::class, array(
                        'label' => 'Verification code:',
                        'attr'  => array('class' => ' form-control-modified col-lg-12 col-md-12 col-sm-12 col-xs-12 formLayout pass-reset-code' , 'maxlength'=> 6 ),
                        'label_attr'  => ['class' => 'required formLayout  form_labels'],
                        'constraints' => array(
                            new NotBlank(array('message' =>  'This field is required')) ,
                            new Assert\Regex(
                                array(
                                    'pattern' => '/^[0-9]+$/',
                                    'match'   => true,
                                    'message' => 'Invalid number')
                            )))
                )
                ->add('submit', SubmitType::class, array('label' => 'Submit', 'attr' => array('class' => 'btn btn-primary')))
                ->getForm();
            $data_form['show_form'] = 1;
            $form->handleRequest($request);

            $this->get('session')->get('resetcode_typeemail_sms');
            $message = $this->get('translator')->trans('If there is an account associated with this E-mail then you will receive an email with a code to reset your password');
            if($resetcode_counter == 0)
            {
                //94618830
                $data_form['show_form'] = 0;
                $this->get('session')->set('resetcode_counter', '');
                // -->
                $this->get('session')->set('resetcode', '');
                return $this->redirect($this->generateUrl('login', array('_country' => $request->cookies->get(AppConstant::COOKIE_COUNTRY), '_locale' => $request->cookies->get(AppConstant::COOKIE_LOCALE))));
            }
            if($id != "" )
            {
                $data            = unserialize($user->getData());
                if (strtotime('+1 day', $data['time']) > time())
                {
                    if ($form->isSubmitted() && $form->isValid())
                    {
                        $resetcode_counter = $resetcode_counter-1;
                        $formData = $form->getData();
                        $resetcode_verification   = (integer)$this->get('session')->get('resetcode');
                        $sub_code =  (integer)$formData['resetcode'];
                        if ($sub_code == $resetcode_verification) {
                            // this make sure resetpasswordcode is not called directly
                            $this->get('session')->set('resetcode_reference' , "1");
                            /*****************/
                            $C_id = $id;
                            /*****************/
                            $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_RESETPASSWORD_SUCCESS, $id,
                                array('iktissab_card_no' => $id, 'message' => $message),
                                null,null
                            );
                            $this->get('session')->set('resetcode_counter', '');
                            return $this->redirect($this->generateUrl('resetpasswordcode', array('_country' => $country_id, '_locale' => $locale, 'time' => $time, 'token' => $token), UrlGenerator::ABSOLUTE_URL));
                        }
                        else
                        {
                            $errorcl = 'alert-danger';
                            $message = $this->get('translator')->trans('Please enter correct verification code');
                            $this->get('session')->set('resetcode_counter', $resetcode_counter);
                            return $this->render('front/verifycode.html.twig', array(
                                'form'    => $form->createView(), 'message' => $message, 'data' => $data_form,
                                'errorcl' => $errorcl
                            ));
                        }
                    }
                    else
                    {
                        $errorcl = 'alert-success';
                        $data_form['show_form'] = 1;
                        $this->get('session')->set('resetcode_counter', $resetcode_counter);
                        return $this->render('front/verifycode.html.twig', array(
                            'form'    => $form->createView(), 'message' => $message, 'data' => $data_form,
                            'errorcl' => $errorcl
                        ));
                    }
                }
                else
                {
                    $errorcl = 'alert-danger';
                    $message = $this->get('translator')->trans('Your link to reset password has been expired');
                    $this->get('session')->set('resetcode_counter', $resetcode_counter);
                    return $this->render('front/verifycode.html.twig', array(
                        'form'    => $form->createView(), 'message' => $message, 'data' => $data_form,
                        'errorcl' => $errorcl
                    ));
                }
            }
            else
            {
                // 1. check if the reset code is valid
                // 2. then print message to expiry
                // 3. this is for security to prevent guessable user name
                if($resetcode_counter != 0)
                {
                    if ($form->isSubmitted() && $form->isValid()) {
                        $resetcode_counter = $resetcode_counter - 1;
                        // here user will always receive this message as he dont have account
                        // untill the reset counter expires due to security
                        $errorcl = 'alert-danger';
                        $message = $this->get('translator')->trans('Please enter correct verification code');
                        $this->get('session')->set('resetcode_counter', $resetcode_counter);
                        $data_form['show_form'] = 1;
                        return $this->render('front/verifycode.html.twig', array(
                            'form'    => $form->createView(), 'message' => $message, 'data' => $data_form,
                            'errorcl' => $errorcl
                        ));
                    }
                    else
                    {
                        $errorcl = 'alert-success';
                        $data_form['show_form'] = 1;
                        $message = $this->get('translator')->trans('If there is an account associated with this E-mail then you will receive an email with a code to reset your password');
                        $this->get('session')->set('resetcode_counter', $resetcode_counter);
                        return $this->render('front/verifycode.html.twig', array(
                            'form' => $form->createView(), 'message' => $message, 'data' => $data_form,
                            'errorcl' => $errorcl
                        ));
                    }
                }
                else
                {
                    $message = $this->get('translator')->trans('Your link to reset password has been expired');
                    $data_form['show_form'] = 0;
                    $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_RESETPASSWORD_ERROR, $id,
                        array('iktissab_card_no' => $id, 'message' => $message, 'session' => $user),
                        null, null);
                    $errorcl = 'alert-danger';
                    return $this->render('front/verifycode.html.twig', array(
                        'form' => $form->createView(),
                        'message' => $message,
                        'data' => $data_form,
                        'errorcl' => $errorcl
                    ));
                }
            }
        }
        catch(\Exception $e)
        {
            $message = $this->get('translator')->trans('Unable to process your request at this time.Please try later');
            $data_form['show_form'] = 0;
            $errorcl = 'alert-danger';
            return $this->render('front/verifycode.html.twig', array(
                'form'    => $form->createView(), 'message' => $message, 'data' => $data_form,
                'errorcl' => $errorcl ));
        }
    }


    public function verifycodeActionSohail(Request $request, $time, $token)
    {
        try
        {
            if($this->get('session')->get('resetcode') == "" || $this->get('session')->get('resetcode') == null){
                return $this->redirect($this->generateUrl('login', array('_country' => $request->cookies->get(AppConstant::COOKIE_COUNTRY), '_locale' => $request->cookies->get(AppConstant::COOKIE_LOCALE))));
            }
            $commFunct   = new FunctionsController();
            $activityLog = $this->get('app.activity_log');
            $em          = $this->getDoctrine()->getManager();
            $time        = (integer)$time;
            $dataValue   = serialize(array('time' => $time, 'token' => $token));
            $resetcode   = $this->get('session')->get('resetcode');
            $country_id = $this->getCountryCode($request);
            $locale  = $this->getCountryLocal($request);
            // todo:
            // $id    = $id;
            // $this->decrypt($id, AppConstant::SECRET_KEY_FP);
            // $id = base64_decode($id . AppConstant::SECRET_KEY_FP);
            // $id = $this->base64url_decode($id . AppConstant::SECRET_KEY_FP);
            $user        = $em->getRepository('AppBundle:User')->findOneBy(array("data" => $dataValue));
            // $user     = $em->getRepository('AppBundle:User')->findOneBy(array("data" => $dataValue , 'iktCardNo' => $id));
            if (isset($user) && $user != null)
            {
                $user_email = $user->getEmail();
                $id = $user->getIktCardNo();
            }
            else
            {
                $user_email = '';
                $id = '';
            }
            $form = $this->createFormBuilder(array('attr' => array('novalidate' => 'novalidate' , 'name' => 'myFormName')))
                ->add('resetcode', TextType::class, array(
                    'label' => 'Verification code:',
                    'attr' => array('class' => ' form-control-modified col-lg-12 col-md-12 col-sm-12 col-xs-12 formLayout' , 'maxlength'=> 9 ),
                    'label_attr' => ['class' => 'required formLayout  form_labels'],
                    'constraints' => array(
                        new NotBlank(array('message' =>  'This field is required')) ,

                        new Assert\Regex(
                        array(
                            'pattern' => '/^[0-9]+$/',
                            'match' => true,
                            'message' => 'Invalid number')
                    )))
                )
                ->add('submit', SubmitType::class, array('label' => 'Submit', 'attr' => array('class' => 'btn btn-primary')))
                ->getForm();
            // $form->get('email')->setData($user_email);
            $data_form['show_form'] = 1;
            if($id != ""  && $id != null)
            {
                $data            = unserialize($user->getData());
                $currentPassword = $user->getPassword();
                if (strtotime('+1 day', $data['time']) > time()) {
                    $form->handleRequest($request);
                    if ($form->isSubmitted() && $form->isValid())
                    {
                        $formData = $form->getData();
                            $resetcode_verification   = (integer)$this->get('session')->get('resetcode');
                            $sub_code =  (integer)$formData['resetcode'];
                            if ($sub_code == $resetcode_verification) {
                                // this make sure resetpasswordcode is not called directly
                                $this->get('session')->set('resetcode_reference' , "1");
                                /*****************/
                                $C_id = $id;
                                /*****************/
                                return $this->redirect($this->generateUrl('resetpasswordcode', array('_country' => $country_id, '_locale' => $locale, 'time' => $time, 'token' => $token), UrlGenerator::ABSOLUTE_URL));
                            }
                            else
                            {
                                $formData['resetcode'];
                                $message = $this->get('translator')->trans('Please enter correct verification code');
                                $errorcl = 'alert-danger';
                                return $this->render('front/verifycode.html.twig', array(
                                    'form' => $form->createView(), 'message' => $message, 'data' => $data_form,
                                    'errorcl' => $errorcl
                                ));
                            }

                    }
                    else
                    {
                        $message = $this->get('translator')->trans('If there is an account associated with this E-mail then you will receive an email with a code to reset your password');
                        $errorcl = 'alert-success';
                        return $this->render('front/verifycode.html.twig', array(
                            'form'    => $form->createView(), 'message' => $message, 'data' => $data_form,
                            'errorcl' => $errorcl
                        ));
                    }
                } else {
                    $message = $this->get('translator')->trans('If there is an account associated with this E-mail then you will receive an email with a code to reset your password');
                    $errorcl = 'alert-danger';
                    return $this->render('front/verifycode.html.twig', array(
                        'form' => $form->createView(), 'message' => $message, 'data' => $data_form,
                        'errorcl' => $errorcl
                    ));
                }
            } else {
                $message = $this->get('translator')->trans('Your link to reset password has been expired');
                $data_form['show_form'] = 0;
                $errorcl = 'alert-danger';
                return $this->render('front/verifycode.html.twig', array(
                    'form' => $form->createView(), 'message' => $message, 'data' => $data_form,
                    'errorcl' => $errorcl
                ));
            }
        }
        catch(\Exception $e)
        {
            $message = $this->get('translator')->trans('An invalid exception occurred');
            $data_form['show_form'] = 0;
            $errorcl = 'alert-danger';
            return $this->render('front/verifycode.html.twig', array(
                'form' => $form->createView(), 'message' => $message, 'data' => $data_form,
                'errorcl' => $errorcl
            ));
        }
    }


    public function decrypt($data, $key)
    {
        $decode = base64_decode($data);
        return mcrypt_decrypt(
            MCRYPT_RIJNDAEL_128,
            $key,
            $decode,
            MCRYPT_MODE_CBC,
            "\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0"
        );
    }

    /**
     * @Route("/{_country}/{_locale}/setpassword", name="setpassword")
     */
    public function setPasswordAction(Request $request)
    {
        //$this->get('session')->set('passwordrest', 'abc@123456');
        //$response = new Response();
        //$response->headers->setCookie(new Cookie(AppConstant::COOKIE_RESET_PASSWORD, 'reset_password',time()+AppConstant::COOKIE_EXPIRY_REST_PASSWORD,'/',null,false,false));
        //echo '==='.$cookieResetPassword = $request->cookies->get(AppConstant::COOKIE_RESET_PASSWORD);
        //$response->sendHeaders();
        //$this->get('session')->get('passwordrest');
        //return $this->redirect($this->generateUrl('resetpassword', array('_country'=>'sa','_locale'=>'en')));
    }

    /**
     * @Route("/{_country}/{_locale}/checkFormsOption", name="checkFormsOption")
     * @param $time
     */

    public function checkFormsOptionAction(Request $request, $form)
    {
        $current_time    = date('Y-m-d');
        $days_difference = 1;
        $em = $this->getDoctrine()->getManager();
        $formsettingsList = $this->getDoctrine()
            ->getRepository('AppBundle:FormSettings')
            ->findOneBy(array('status' => 1, 'formtype' => 'Contact Us'));
        // print_r($formsettingsList);
        // echo $formsettingsList->getId();
        $data  = array();
        $i     = 0;
        $time  = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
        // echo '<br>';
        $timeN = mktime(0, 0, 0, date('m'), date('d') + $days_difference, date('Y'));
        $status_from_db = true;
        if ($time > $timeN && $status_from_db == true) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @Route("/{_country}/{_locale}/getDataa", name="getDataa")
     * @param $time
     */


    public function getDataaAction()
    {
        $em = $this->getDoctrine()->getManager('default');
        $this->getDoctrine()->getManager();
        // $conn = $em->getConnection();
        // $em = $this->getDoctrine()->getEntityManager();
        $rsm = new ResultSetMapping();
        //$rsm->addScalarResult('name', 'name');
        $rsm->addScalarResult('name', 'name');
        // $em->createNativeQuery(, $rsm)
        $query = $em->createNativeQuery('SELECT * from test', $rsm);
        echo $query->getSQL();
        //print_r($query);
        $users = $query->getResult();
        print_r($users);
        // $this->get('session')->set('passwordrest', 'abc@123456');
        // $response = new Response();
        // $response->headers->setCookie(new Cookie(AppConstant::COOKIE_RESET_PASSWORD, 'reset_password',time()+AppConstant::COOKIE_EXPIRY_REST_PASSWORD,'/',null,false,false));
        // echo '==='.$cookieResetPassword = $request->cookies->get(AppConstant::COOKIE_RESET_PASSWORD);
        // $response->sendHeaders();
        // $this->get('session')->get('passwordrest');
        // return $this->redirect($this->generateUrl('resetpassword', array('_country'=>'sa','_locale'=>'en')));
    }

    /**
     * @Route("{_country}/{_locale}/subscription/", name="subscription")
     * @param Request $request
     * @return Response
     */
    public function subscriptionAction(Request $request)
    {
        $country = $request->get('_country');
        $commFunction = new FunctionsController();
        $commFunction->setContainer($this->container);
        $em = $this->getDoctrine()->getManager();
        $formEmail = $this->createFormBuilder(null, ['attr' => ['novalidate' => 'novalidate']    ])
            ->add('email', EmailType::class, array('label' => 'Email Id',
                    'attr' => array('placeholder'=> 'someone@gmail.com'),
                    'constraints' => array(

                    new Assert\NotBlank(array('message' => 'This field is required')),
                    new Assert\Email(array('message' => 'Invalid email address'))

                ))
            )
            ->add('join_us', SubmitType::class,array(

                'label' => $this->get('translator')->trans('Join Us'),
            ))
            ->getForm();
        // form for mobile subscription
        $formMobile = $this->get('form.factory')->createNamedBuilder('subs_mob',
            'Symfony\Component\Form\Extension\Core\Type\FormType', null ,
            ['attr' => ['novalidate' => 'novalidate'] ] , array('csrf_protection' => false) )
            ->add('mobile', TextType::class, array(
                'label' => 'Mobile',
                'attr'  => array('placeholder'=> ($country == 'sa' ? '05xxxxxxxx' : '0020xxxxxxxxxx' ) , 'maxlength'=> ($request->get('_country') == 'sa') ? 10 : 14),
                'constraints' => array(
                    new Assert\NotBlank(array('message' => 'This field is required')),
                    new Assert\Regex(
                        array(
                            'pattern' => ($country == 'sa') ? '/^[0]([0-9]){9}$/' : '/^([0-9]){14}$/',
                            'match'   => true,
                            'message' => "Mobile Number Must be ".($country == 'sa' ? '10' : '14' )." digits")
                    ),)))
            ->add('join_us', SubmitType::class,array('label' => $this->get('translator')->trans('Join Us'), ))
            ->getForm();
        $formEmail->handleRequest($request);
        $formEmailData = $formEmail->getData();
        if ($formEmail->isSubmitted()) {
            if (!$formEmail->isValid()) {
                $validate_data = array($formEmailData['email']);
                $errors = $commFunction->validateData($validate_data);
                if($errors > 0)
                {
                    $errormsg =  $this->get('translator')->trans('Please provide valid data');
                    $return   = array('error' => true, 'message' => $errormsg);
                    echo json_encode($return);
                    die();
                }
                foreach ($formEmail->getErrors(true, true) as $key => $value) {
                    $errormsg = "";
                    $errormsg .= $value->getMessage();
                }
                $return = array('error' => true, 'message' => $errormsg);
            }

            if ($formEmail->isValid())
            {
                $validate_data = array($formEmailData['email']);
                $errors = '';
                $errors = $commFunction->validateDataSubs($validate_data);
                if($errors > 0){
                    $errormsg =  $this->get('translator')->trans('Please provide valid data');
                    $return = array('error' => true, 'message' => $errormsg);
                    echo json_encode($return);
                    die();
                }

                $subscription = new Subscription();
                // check if already exist
                $emailExist = $em->getRepository('AppBundle:Subscription')->findOneBy(array('subsVal' => $formEmailData['email'], 'subsType' => 'email'));
                if (!$emailExist) {
                    $subscription->setSubsVal(strip_tags(trim($formEmailData['email'])));
                    $subscription->setSubsType('email');
                    $em->persist($subscription);
                    $em->flush();
                    $return = array('error' => false, 'message' => $this->get('translator')->trans('Thankyou'));
                } else {
                    $return = array('error' => true, 'message' => $this->get('translator')->trans('You have already subscribed'));
                }
            }
            echo json_encode($return);
            die();
        }
        // handle request for mobile subscription
        $formMobile->handleRequest($request);
        $formMobData = $formMobile->getData();
        if ($formMobile->isSubmitted())
        {
            if (!$formMobile->isValid())
            {
                $validate_data = array(strip_tags(trim($formMobData['mobile'])));
                $errors = $commFunction->validateDataSubs($validate_data);
                if($errors > 0)
                {
                    $errormsg =  $this->get('translator')->trans('Please provide valid data');
                    $return = array('error' => true, 'message' => $errormsg);
                    echo json_encode($return);
                    die();
                }
                // throw error
                // $errormsg = '';
                foreach ($formMobile->getErrors(true, true) as $key => $value) {
                    $errormsg = "";
                    $errormsg .= $value->getMessage();
                }
                $return = array('error' => true, 'message' => $errormsg);
            }

            if ($formMobile->isValid()) {
                $validate_data = array($formMobData['mobile']);
                $valida_mob_num = strip_tags(trim($formMobData['mobile']));
                $errors = '';
                $errors = $commFunction->validateData($validate_data);
                if($errors > 0){
                    $errormsg =  $this->get('translator')->trans('Please provide valid data');
                    $return = array('error' => true, 'message' => $errormsg);
                    echo json_encode($return);
                    die();
                }
                $validate_data_mobile = substr($valida_mob_num,0,2 );
                if($country == 'sa')
                {
                    if ($validate_data_mobile != "05")
                    {
                        $errormsg = $this->get('translator')->trans('Invalid mobile number');
                        $return = array('error' => true, 'message' => $errormsg);
                        echo json_encode($return);
                        die();
                    }
                }
                else
                {
                    $validate_data_mobile = substr($valida_mob_num,0,4 );
                    if ($validate_data_mobile != "0020")
                    {
                        $errormsg = $this->get('translator')->trans('Invalid mobile number');
                        $return = array('error' => true, 'message' => $errormsg);
                        echo json_encode($return);
                        die();
                    }
                }
                $subscription = new Subscription();
                // check if already exist
                $mobileExist = $em->getRepository('AppBundle:Subscription')->findOneBy(array('subsVal' => $formMobData['mobile'], 'subsType' => 'mobile'));
                if (!$mobileExist)
                {
                    $subscription->setSubsVal(strip_tags(trim($formMobData['mobile'])));
                    $subscription->setSubsType('mobile');
                    $em->persist($subscription);
                    $em->flush();
                    $return = array('error' => false, 'message' =>  $this->get('translator')->trans('Thankyou'));

                } else {
                    $return = array('error' => true, 'message' => $this->get('translator')->trans('You have already subscribed'));
                }
            }
            echo json_encode($return);
            die();
        }
        return $this->render('/default/subscription.html.twig',
            array(
                'form'      => $formEmail->createView(),
                'form_mob'  => $formMobile->createView(),
                'country'   => $country
            )
        );
    }
   
    private function getIP()
    {
        return $_SERVER['REMOTE_ADDR'];
    }

    /**
     * @Route("{_country}/{_locale}/promotions/", name="promotions")
     * @Route("{_country}/{_locale}/promotions/{id}", name="promotions_id")
     */
    public function promotionsAction(Request $request)
    {

        $restClient = $this->get('app.rest_client')->IsAdmin(true);
        if($request->get('_country') == 'eg') {
            $promoUrl = AppConstant::PROMOTIONS_PATH_EG;
        }else {
            $promoUrl = AppConstant::PROMOTIONS_PATH;
        }
        $promotions = $restClient->restGet($promoUrl);
        $promotions = json_decode($promotions,true);
        $enabledPromo = array();
        foreach ($promotions as $promo)
        {
            if ($promo['is_active'] == 1) {
                $enabledPromo[] = $promo['id'];
            }
        }
        if (in_array($request->get('id'), $enabledPromo)) {
            $id = $request->get('id');
        } else {
            $id = $enabledPromo[0];
        }
        return $this->render('/default/promotions.html.twig', array('promotions' => $promotions, 'id' => $id ));
    }

    /**
     * @param Request $request
     * @Route("/{_country}/{_locale}/setDefaultPath", name="setDefaultPath")
     */
    public function setDefaultPathAction(Request $request){
        $response = new Response();
        // $locale  = $request->getLocale();
        // $country = $request->get('_country');
        echo $cookieLocale = $request->cookies->get(AppConstant::COOKIE_LOCALE);
        $cookieCountry = $request->cookies->get(AppConstant::COOKIE_COUNTRY);
        /*if(isset($cookieLocale) && $cookieLocale <> '' && isset($cookieCountry) && $cookieLocale <> '') {
            // checking if the user has modified the url
            if ($country != $cookieCountry) {
                return $this->redirect($this->generateUrl('homepage', array('_country'=>$cookieCountry,'_locale'=>$locale)));
            }
        }*/
    }

    /**
     * @Route("/{_country}/{_locale}/setLanguage", name="setLanguage")
     * @param Request $request
     * @param $langauge
     */
    public function setLanguageAction(Request $request)
    {
        $response = new Response();
        $commFunct = new FunctionsController();
        $commFunct->setContainer($this->container);
        $commFunct->checkSessionCookies($request);
        if ($commFunct->checkSessionCookies($request) == false) {
            return $this->redirect($this->generateUrl('landingpage'));
        }
        $cookieCountry = $request->cookies->get(AppConstant::COOKIE_COUNTRY);
        $userLang  =  trim($request->query->get('lang'));
        $id     =  trim($request->query->get('id'));
        $param  = $request->query->get('param');
        if($param != null)
        {
           foreach ($param as $key => $data){
               if($key == '_country'){
                   $url_attributes['_country'] = $cookieCountry;
               }
               elseif($key == '_locale'){
                   $url_attributes['_locale']  = $userLang;
               }
               else
               {
                   $url_attributes[$key] = $data;
               }
            }
        }
        $url  =  trim($request->query->get('url'));
        $commFunct->changeLanguage($request, $userLang);
        if($url == "" || $url == null ) {
            $url = 'homepage';
        }
        if($id == "" || $id == null) {
            return $this->redirect($this->generateUrl($url, $url_attributes));
        }
        else {
            return $this->redirect($this->generateUrl($url, array('_country' => $cookieCountry, '_locale' => $userLang, 'id' => $id)));
        }
    }

    /**
     * @Route("/{_country}/{_locale}/setCountry", name="setCountry")
     * @param Request $request
     * @param $langauge
     */
    public function setCountryAction(Request $request)
    {
        $response = new Response();
        /***************/
        $commFunct = new FunctionsController();
        if ($commFunct->checkSessionCookies($request) == false) {
            return $this->redirect($this->generateUrl('landingpage'));
        }
        /***************/
        $tokenStorage = $this->get('security.token_storage');
        $userCountry  =  trim($request->query->get('ccid'));
        $cookieLocale = $request->cookies->get(AppConstant::COOKIE_LOCALE);
        $commFunct->changeCountry($request, $userCountry);
        if($this->get('session')->get('iktUserData')) {
            return $this->redirect($this->generateUrl('account_logout', array('_country' => $userCountry, '_locale' => $cookieLocale)));
        }
        else {
            return $this->redirect($this->generateUrl('homepage', array('_country' => $userCountry, '_locale' => $cookieLocale)));
        }
    }



    function base64url_encode($s) {
        return str_replace(array('+', '/'), array('-', '_'), base64_encode($s));
    }

    function base64url_decode($s) {
        return base64_decode(str_replace(array('-', '_'), array('+', '/'), $s));
    }



    /**
     * @Route("/{_country}/{_locale}/sendpassword", name="send_password")
     * Request $request
     */
    public function sendPwdAction(Request $request)
    {
        // Iktissab pincode recovery
        // we need to be logged in first
        $activityLog  = $this->get('app.activity_log');
        $commFunction = new FunctionsController();
        $commFunction->setContainer($this->container);
        if ($commFunction->checkSessionCookies($request) == false) {
            return $this->redirect($this->generateUrl('landingpage'));
        }
        $this->get('translator')->trans('Invalid Iqama Id/SSN Number' . $request->get('_country'));
        /**********/
        $userLang = '';
        $response = new Response();
        $locale   = $request->getLocale();
        if ($request->query->get('lang')) {
            $userLang = trim($request->query->get('lang'));
        }
        if ($userLang != '' && $userLang != null) {
            // we will only modify cookies if the both the params are same for the langauge
            // this means that the query is modified from the change language link
            if ($userLang == $locale) {
                $commFunction->changeLanguage($request, $userLang);
                $locale = $request->getLocale();
            } else {
                if ($request->cookies->get(AppConstant::COOKIE_LOCALE)) {
                    return $this->redirect($this->generateUrl('homepage', array('_country' => $request->cookies->get(AppConstant::COOKIE_COUNTRY), '_locale' => $request->cookies->get(AppConstant::COOKIE_LOCALE))));
                }
            }
        }

        $userCountry = '';
        if ($request->query->get('ccid')) {
            $userCountry = $request->query->get('ccid');
        }
        $country = $request->get('_country');
        if ($userCountry != '' && $userCountry != null) {
            if ($userCountry == $country) {
                $commFunction->changeCountry($request, $userCountry);
                $country = $request->get('_country');
            } else {
                if ($request->cookies->get(AppConstant::COOKIE_COUNTRY)) {
                    return $this->redirect($this->generateUrl('homepage', array('_country' => $request->cookies->get(AppConstant::COOKIE_COUNTRY), '_locale' => $request->cookies->get(AppConstant::COOKIE_LOCALE))));
                }
            }
        }
        if ($request->cookies->get(AppConstant::COOKIE_LOCALE)) {
            $cookieLocale = $request->cookies->get(AppConstant::COOKIE_LOCALE);
            $cookieCountry = $request->cookies->get(AppConstant::COOKIE_COUNTRY);
            if (isset($cookieLocale) && $cookieLocale <> '' && $cookieLocale != $locale) {
                return $this->redirect($this->generateUrl('homepage', array('_country' => $cookieCountry, '_locale' => $cookieLocale)));
            }
            if (isset($cookieCountry) && $cookieCountry <> '' && $cookieCountry != $country) {
                return $this->redirect($this->generateUrl('homepage', array('_country' => $cookieCountry, '_locale' => $cookieLocale)));
            }
        }
        /**********/
        $error = array('success' => true, 'message' => '');
        $restClient = $this->get('app.rest_client')->IsAdmin(true);
        $smsService = $this->get('app.sms_service');
        $form = $this->createForm(SendPwdType::class, array(), array(
            'additional' => array(
                'locale'     => $request->getLocale(),
                'country'    => $request->get('_country'),
            )));

        try
        {
            $form->handleRequest($request);
            $pData = $form->getData();
            $data  = $request->request->all();
            $logged_user_data = $this->get('session')->get('iktUserData');
            $message = "";
            $this->get('session')->get('_CAPTCHA');
            if ($form->isSubmitted() )
            {
                if($form->isValid())
                {
                    $errors = "";
                    $validate_data = array($data['send_pwd']['iqama'] , $data['send_pwd']['iktCardNo'],
                        $data['send_pwd']['captchaCode'] );
                    $errors = $commFunction->validateData($validate_data);
                    if($errors > 0)
                    {
                        $commFunction->setCsrfToken('front_forgot_pass');
                        $session = new Session();
                        $token   = $session->get('front_forgot_pass');
                        $filename      = $commFunction->saveTextAsImage();
                        $response->setContent($filename['filename']);
                        $captcha_image = $filename['image_captcha'];
                        $error['success'] = false;
                        $error['message'] = $this->get('translator')->trans('Please provide valid data');
                        return $this->render('/default/send_pwd.twig',
                            array(
                                'form'    => $form->createView(),
                                'error'   => $error,
                                'token'   => $token,
                                'data'    => $captcha_image, ));
                    }
                    try
                    {
                        if ($commFunction->checkCsrfToken($form->get('token')->getData(), 'front_forgot_pass'))
                        {
                            $captchaCode = trim(strtoupper($this->get('session')->get('_CAPTCHA')));
                            $captchaCodeSubmitted = trim($form->get('captchaCode')->getData());
                            $filename = $commFunction->saveTextAsImage();
                            $response->setContent($filename['filename']);
                            $captcha_image = $filename['image_captcha'];
                            if ($captchaCodeSubmitted != $captchaCode) {
                                $commFunction->setCsrfToken('front_forgot_pass');
                                $session = new Session();
                                $token = $session->get('front_forgot_pass');
                                $error_cl = 'alert-danger';
                                $error['success'] = false;
                                $error['message'] = $this->get('translator')->trans('Invalid captcha code');
                                return $this->render('default/send_pwd.twig',
                                    array
                                    (
                                        'form'    => $form->createView(),
                                        'error'   => $error,
                                        'token'   => $token,
                                        'data'    => $captcha_image,
                                    )
                                );
                            }

                            if ($request->get('_country') == 'sa') {
                                $validate_Iqama = $this->validateIqama($data['send_pwd']['iqama']);
                                $commFunction->setCsrfToken('front_forgot_pass');
                                $session = new Session();
                                $token = $session->get('front_forgot_pass');
                                if ($validate_Iqama == false) {
                                    $message = "";
                                    $error['success'] = false;
                                    $error['message'] = $this->get('translator')->trans('Invalid Iqama Id/SSN Number' . $request->get('_country'));

                                    return $this->render('default/send_pwd.twig',
                                        array(
                                            'form' => $form->createView(),
                                            'error' => $error,
                                            'token' => $token,

                                            'data' => $captcha_image,
                                        )
                                    );
                                }
                            }

                            $accountEmail = $this->iktCheck($pData['iktCardNo']);

                            if ($accountEmail != "" && $accountEmail != null) {
                                if (!empty($this->get('session')->get('iktUserData'))) {
                                    $logged_user_data = $this->get('session')->get('iktUserData');
                                    // print_r($logged_user_data);
                                    $logged_user_data['C_id'];
                                    $logged_user_data['ID_no'];
                                    $pData['iqama'];
                                    if ($pData['iqama'] != $logged_user_data['ID_no']) {
                                        $error['success'] = false;
                                        $error['message'] = $this->get('translator')->trans('Invalid Iqama Id/SSN Number' . $request->get('_country'));
                                    } elseif ($pData['iktCardNo'] != $logged_user_data['C_id']) {
                                        $error['success'] = false;
                                        $error['message'] = $this->get('translator')->trans('Please enter valid Iktissab card number');
                                    } else {
                                        $url = $request->getLocale() . '/api/' . $pData['iktCardNo'] . '/sendsms/' . $pData['iqama'] . '.json';
                                        AppConstant::WEBAPI_URL.$url;
                                        $data_user = $restClient->restGet(AppConstant::WEBAPI_URL . $url, array('Country-Id' => strtoupper($request->get('_country'))));
                                        $acrivityLog = $this->get('app.activity_log');
                                        if (!empty($data_user))
                                        {
                                            if ($data_user['success'] == true)
                                            {
                                                $message = $data_user['message'];
                                                $acrivityLog->logEvent(AppConstant::ACTIVITY_FORGOT_PASSWORD_SUCCESS, $pData['iktCardNo'],
                                                    array('message' => $message, 'session' => $logged_user_data ),
                                                    null , null );
                                                $error['success'] = true;
                                                $error['message'] = $this->get('translator')->trans('You will recieve sms on your mobile number ****') . substr($logged_user_data['mobile'], 8, 12);
                                            }
                                            else
                                            {
                                                $acrivityLog->logEvent(AppConstant::ACTIVITY_FORGOT_PASSWORD_ERROR, $pData['iktCardNo'],
                                                array('message' => $message, 'session' =>  $logged_user_data),
                                                    null , null );
                                                $error['success'] = false;
                                                $error['message'] = $data['message'];
                                            }
                                        }
                                        else
                                        {
                                            $error['success'] = false;
                                            $error['message'] = $this->get('translator')->trans('Unable to process your request at this time.Please try later');
                                        }
                                    }
                                    $commFunction->setCsrfToken('front_forgot_pass');
                                    $session = new Session();
                                    $token = $session->get('front_forgot_pass');
                                    return $this->render('/default/send_pwd.twig',
                                        array(
                                            'form'  => $form->createView(),
                                            'error' => $error,
                                            'token' => $token,
                                            'data'  => $captcha_image,
                                        )
                                    );

                                }
                                else
                                {
                                    $pData['iqama'];
                                    $url = $request->getLocale() . '/api/' . $pData['iktCardNo'] . '/sendsms/' . $pData['iqama'] . '.json';
                                    AppConstant::WEBAPI_URL.$url;
                                    $data_user = $restClient->restGet(AppConstant::WEBAPI_URL . $url, array('Country-Id' => strtoupper($request->get('_country'))));
                                    //print_r($data_user);exit;
                                    $acrivityLog = $this->get('app.activity_log');
                                    if (!empty($data_user)) {
                                        if ($data_user['success'] == true) {
                                            //$message = $data_user['message'];
                                            $acrivityLog->logEvent(AppConstant::ACTIVITY_FORGOT_PASSWORD_SUCCESS, $pData['iktCardNo'],
                                                array('message' => $message, 'session' => array('iqamaid' =>  $pData['iqama'] ,
                                                    'cardno' => $pData['iktCardNo'])), null , null);
                                            $error['success'] = true;
                                            $error['message'] = $data_user['message'];
                                        } else {
                                            $acrivityLog->logEvent(AppConstant::ACTIVITY_FORGOT_PASSWORD_ERROR, $pData['iktCardNo'],
                                                array('message' => $message, 'session' => array('iqamaid' =>  $pData['iqama'] ,
                                                    'cardno' => $pData['iktCardNo'])), null , null);
                                            $error['success'] = false;
                                            $error['message'] = $data_user['message'];
                                        }
                                    } else {
                                        $error['success'] = false;
                                        $error['message'] = $this->get('translator')->trans('Unable to process your request at this time.Please try later');
                                    }
                                    $commFunction->setCsrfToken('front_forgot_pass');
                                    $session = new Session();
                                    $token = $session->get('front_forgot_pass');
                                    return $this->render('/default/send_pwd.twig',
                                        array(
                                            'form'  => $form->createView(),
                                            'error' => $error,
                                            'token' => $token,
                                            'data'  => $captcha_image,
                                        )
                                    );
                                }
                            }
                        } else {
                            $this->get('security.token_storage')->setToken(null);
                            $response = new RedirectResponse($this->generateUrl('landingpage'));
                            return $response;
                        }
                    }
                    catch (\Exception $e)
                    {
                        $commFunction->setCsrfToken('front_forgot_pass');
                        $session = new Session();
                        $token = $session->get('front_forgot_pass');
                        $e->getMessage();
                        $filename = $commFunction->saveTextAsImage();
                        $response->setContent($filename['filename']);
                        $captcha_image = $filename['image_captcha'];
                        $error['success'] = false;
                        $error['message'] = $this->get('translator')->trans('Unable to process your request at this time.Please try later');
                        return $this->render('/default/send_pwd.twig',
                            array(
                                'form' => $form->createView(),
                                'error' => $error,

                                'token' => $token,
                                'data' => $captcha_image,
                            ));
                    }
                }
                else
                {
                    $commFunction->setCsrfToken('front_forgot_pass');
                    $session = new Session();
                    $token   = $session->get('front_forgot_pass');

                    $filename      = $commFunction->saveTextAsImage();
                    $response->setContent($filename['filename']);
                    $captcha_image = $filename['image_captcha'];
                    $error['success'] = false;
                    $error['message'] = $this->get('translator')->trans('Unable to process your request at this time.Please try later');

                    return $this->render('/default/send_pwd.twig',
                        array(
                            'form'    => $form->createView(),
                            'error'   => $error,

                            'token' => $token,
                            'data'    => $captcha_image,
                     ));
                }

            }
            else
            {
                $commFunction->setCsrfToken('front_forgot_pass');
                $session = new Session();
                $token   = $session->get('front_forgot_pass');

                $filename      = $commFunction->saveTextAsImage();
                $response->setContent($filename['filename']);
                $captcha_image = $filename['image_captcha'];

                return $this->render('/default/send_pwd.twig',
                    array(
                        'form'    => $form->createView(),
                        'error'   => $error,

                        'token'   => $token,
                        'data'    => $captcha_image,
                        ) );
            }
        }
        catch(\Exception $e){
            $filename      = $commFunction->saveTextAsImage();
            $e->getMessage();
            $response->setContent($filename['filename']);
            $captcha_image = $filename['image_captcha'];
            $message = $this->get('translator')->trans('An invalid exception occurred');
            $error['success'] = false;
            $error['message'] = $message;

            $commFunction->setCsrfToken('front_forgot_pass');
            $session = new Session();
            $token   = $session->get('front_forgot_pass');


            return $this->render('/default/send_pwd.twig',
                array(
                    'form'  => $form->createView(),
                    'token' => $token,
                    
                    'error' => $error, 'data'    => $captcha_image, ) );
        }
        catch (AccessDeniedException $ed)
        {
            $message = $ed->getMessage();
            $filename      = $commFunction->saveTextAsImage();
            $response->setContent($filename['filename']);
            $captcha_image = $filename['image_captcha'];
            $error['success'] = false;
            $error['message'] = $this->get('translator')->trans($ed->getMessage());

            $commFunction->setCsrfToken('front_forgot_pass');
            $session = new Session();
            $token   = $session->get('front_forgot_pass');

            return $this->render('/default/send_pwd.twig',
                array(
                    'form'  => $form->createView(),
                    'token' => $token,
                    'error' => $error,'data'    => $captcha_image, ) );
        }
    }

    public function resetPasswordOffline(Request $request , $password , $C_id)
    {
        try
        {
            $country_id  = $request->get('_country');
            $restClient  = $this->get('app.rest_client')->IsAdmin(true);
            if(!empty($password))
            {
                /************************/

                $url = $request->getLocale() . '/api/reset_password.json';
                $form_data   =   array(
                    'secret' =>  $password ,
                    'C_id'   =>    $C_id
                );
                $postData = json_encode($form_data);
                //dump($postData);
                $data = $restClient->restPostForm(AppConstant::WEBAPI_URL . $url, $postData, array('Country-Id' => $request->get('_country')));
                //dump($data);die('---');
                if($data['status'] == 1)
                {
                    if($data['success'] == 1) {
                        return true;
                    } else {
                        return false;
                    }
                }
                else
                {
                    return false;
                }
            }
        }
        catch (\Exception $e)
        {
            return false;
        }
        catch (AccessDeniedException $ad)
        {
            return false;
        }
    }





    public function setOthCookie()
    {
        $response = new Response();
        $value = 'eg';
        $store_value = "ar";
        setcookie("cookie_country", $value, time()+3600 , '/');
        setcookie("store", $store_value, time()+3600 , '/');
        $response->headers->setCookie(new Cookie('cookie_country', "eg" , time() + AppConstant::COOKIE_EXPIRY, '/', null, false, false));
        $response->headers->setCookie(new Cookie('store', 'ar' , time() + AppConstant::COOKIE_EXPIRY, '/', null, false, false));
        $response->sendHeaders();
        return;
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

    function iktCheck($ikt)
    {
        $em = $this->getDoctrine()->getManager();
        $checkIktCard = $em->getRepository('AppBundle:User')->find($ikt);
        if (is_null($checkIktCard)) {
            Throw new Exception($this->get('translator')->trans('Card is not registered on website'), 1);
        } else {
            return $checkIktCard->getEmail();
        }
    }


    /**
     * @Route("/{_country}/{_locale}/iktissabPromotions", name="front_iktissab_promotions")
     * @param Request $request
     * @param Response $response
     */
    public function iktissabPromotionsAction(Request $request)
    {
        try
        {
            $locale = $request->getLocale();
            $restClient = $this->get('app.rest_client')->IsAdmin(true);
            $commFunction = new FunctionsController();
            $commFunction->setContainer($this->container);
            if ($commFunction->checkSessionCookies($request) == false) {
                return $this->redirect($this->generateUrl('landingpage'));
            }

            $url = $commFunction->getOthaimServiceURL($request);

            /**********/
            $userLang = '';
            if ($request->query->get('lang')) {
                $userLang = trim($request->query->get('lang'));
            }
            if ($userLang != '' && $userLang != null) {
                if ($userLang == $locale) {
                    $commFunction->changeLanguage($request, $userLang);
                    $locale = $request->getLocale();
                } else {
                    if ($request->cookies->get(AppConstant::COOKIE_LOCALE)) {
                        return $this->redirect($this->generateUrl('homepage', array('_country' => $request->cookies->get(AppConstant::COOKIE_COUNTRY), '_locale' => $request->cookies->get(AppConstant::COOKIE_LOCALE))));
                    }
                }
            }

            $userCountry = '';
            if ($request->query->get('ccid')) {
                $userCountry = $request->query->get('ccid');
            }
            $country = $request->get('_country');
            if ($userCountry != '' && $userCountry != null) {
                if ($userCountry == $country) {
                    $commFunction->changeCountry($request, $userCountry);
                    $country = $request->get('_country');
                } else {
                    if ($request->cookies->get(AppConstant::COOKIE_COUNTRY)) {
                        return $this->redirect($this->generateUrl('homepage', array('_country' => $request->cookies->get(AppConstant::COOKIE_COUNTRY), '_locale' => $request->cookies->get(AppConstant::COOKIE_LOCALE))));
                    }
                }
            }
            if ($request->cookies->get(AppConstant::COOKIE_LOCALE)) {
                $cookieLocale = $request->cookies->get(AppConstant::COOKIE_LOCALE);
                $cookieCountry = $request->cookies->get(AppConstant::COOKIE_COUNTRY);
                if (isset($cookieLocale) && $cookieLocale <> '' && $cookieLocale != $locale) {
                    return $this->redirect($this->generateUrl('homepage', array('_country' => $cookieCountry, '_locale' => $cookieLocale)));
                }
                if (isset($cookieCountry) && $cookieCountry <> '' && $cookieCountry != $country) {
                    return $this->redirect($this->generateUrl('homepage', array('_country' => $cookieCountry, '_locale' => $cookieLocale)));
                }
            }
            /**********/


            $postData = "{\"service\":\"IktissabPromotions\"} ";
            $data     = $restClient->restPostForm($url,
                $postData, array('Content-Type' => 'application/x-www-form-urlencoded', 'input-content-type' => "text/json", 'output-content-type' => "text/json",
                    'language' => $locale
                ));

            $data_dec = json_decode($data, true);
            $data_dec['success'];
            $listing = array();
            if ($data_dec['success'] == true) {
                $products = json_decode($data);
                // var_dump(json_decode($data));
                // echo "<br>";
                // var_dump($products->products[0]);
                // var_dump($products->success);
                if ($products->success == true) {
                    $pro = $products->products;
                    $i = 0;
                    foreach ($products->products as $data)
                    {
                        $listing[$i]['ID'] = $data->ID;
                        $listing[$i]['Price'] = $data->Price;
                        $listing[$i]['SpecialPrice'] = $data->SpecialPrice;
                        $listing[$i]['Name'] = $data->Name;
                        $listing[$i]['SKU'] = $data->SKU;
                        $listing[$i]['URL'] = $data->URL;
                        $listing[$i]['SmallImage'] = $data->SmallImage;
                        $i++;
                    }
                    // var_dump($listing);
                    return $this->render('front/iktissabpromotions.html.twig', array('DataPromo' => $listing));
                } else {
                    return $this->render('front/iktissabpromotions.html.twig', array('DataPromo' => $listing));
                }
            } else {
                $listing = "";
                return $this->render('front/iktissabpromotions.html.twig', array('DataPromo' => $listing));
            }
        }
        catch(\Exception $e){
            $listing = "";
            return $this->render('front/iktissabpromotions.html.twig', array('DataPromo' => $listing));
        }
        catch (AccessDeniedException $ed)
        {
            $listing = "";
            return $this->render('front/iktissabpromotions.html.twig', array('DataPromo' => $listing));
        }
    }

    /**
     * @Route("/{_country}/{_locale}/test1243")
     * @param Request $request
     * @return Response
     */

    public function test1243(Request $request){

        $message = \Swift_Message::newInstance()
            ->addTo('sa.aspire@gmail.com')
            ->addFrom($this->container->getParameter('mailer_user'))
            ->setSubject(AppConstant::EMAIL_SUBJECT);
        $message->setBody(
            $this->container->get('templating')->render(':email-templates/forgot_password:forgot_password.html.twig', [
                'email' => 'sa.aspire@gmail.com',
                'link'  => 'asdf'
            ]),
            'text/html'
        );


        $this->get('mailer')->send($message);
        $message = $this->get('translator')->trans('lang-E');
        return $this->render('account/error.html.twig',
            array
            (
                'message' => $message,
                'errorcl' => 'alert-danger'
            )
        );
    }

    private function listbanners(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $imageList = $this->getDoctrine()
            ->getRepository('AppBundle:Gallery')
            ->findBy(array('status' => 1 , 'display' => 1 ));
        // Display 1 is for home page main banners.
        return $imageList;

        $image_data =  array();
        $i = 0;
        foreach ($imageList as $imageListing )
        {
            $image_data[$i]['image']  = $imageListing->getImage();
            $image_data[$i]['atitle'] = $imageListing->getAtitle();
            $image_data[$i]['etitle'] = $imageListing->getEtitle();
            $image_data[$i]['edesc']  = $imageListing->getEdesc();
            $image_data[$i]['adesc']  = $imageListing->getAdesc();
            $i++;
        }
        return $image_data;
    }



    public function headerTopBanner(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $imageListHeader = $this->getDoctrine()
            ->getRepository('AppBundle:Gallery')
            ->findOneBy(array('status' => 1 , 'display' => 2),array('id' => 'DESC'));
        // Display 2 is for home page mid header.
        return $imageListHeader;
    }


    /**
     * @Route("/{_country}/{_locale}/verifyevmail/{token}", name="front_verifyevmail")
     * @param Request $request
     * @param Response $response
     */
    public function verify_email($token, Request $request)
    {
        try
        {
            $token = $token;
            $em = $this->getDoctrine()->getManager();
            $data = $em->getRepository('AppBundle:user')
                ->findOneBy(array('useremailhashid' => $token));
            if (!empty($data))
            {
                if($data->getVerifyemail() == '1')
                {
                    if($data->getStatus() == '1'){
                        $message = $this->get('translator')->trans('Your email is already verified. You can login now');
                    }else {
                        $message = $this->get('translator')->trans('Your email is already verified. Once the Iktissab Team activates your account you can login');
                    }
                }
                else
                {
                    // for scenario 2
                    if($data->getStatus() == '1'){
                        $message = $this->get('translator')->trans('Your email is verified successfully. You can login now');
                    } else {
                        $message = $this->get('translator')->trans('Your email is verified successfully. Once iktissab team activates your account you will be able to login.');
                    }
                    $data->setVerifyemail('1');
                    $em->flush();
                }
                return $this->render('front/email_verifycode_user.html.twig', array('message' => $message , 'errorcl' => 'alert-success' ));
            }
            else
            {
                return $this->render('front/email_verifycode_user.html.twig', array('message' => $this->get('translator')->trans('Unable to verify your email'), 'errorcl' => 'alert-danger'));
            }
        }
        catch (\Exception $e)
        {
            return $this->render('front/email_verifycode_user.html.twig', array('message' => $this->get('translator')->trans('Unable to verify your email'), 'errorcl' => 'alert-danger'));
        }
    }


    /**
     * @Route("/{_country}/{_locale}/userslist", name="front_userslist")
     * @param Request $request
     * @param Response $response
     */
    public function UserslistAction()
    {
        $entityManager = $this->getDoctrine();
        $conn = $entityManager->getConnection();
        $xls = new ExcelHTML();
        $fromDate  = date('Y/m/d', strtotime( '-53 days' ) );
        $start     = $fromDate." 00:00:00";
        $toDate    = date('Y/m/d', strtotime( '+2 days' ) );
        $end       = $toDate ." 23:59:59";
        $sql  = "
        SELECT count(u.ikt_card_no) as user_count_website, FROM_UNIXTIME( `reg_date` , '%Y/%m/%d' ) AS created_date FROM user u 
        WHERE activation_source = 'W' AND reg_date BETWEEN UNIX_TIMESTAMP( '$start' ) AND UNIX_TIMESTAMP( '$end' ) GROUP BY created_date ";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $res = $stmt->fetchAll();
        $tableData = array();
        foreach($res as $rows)
        {
            $tableData[$rows['created_date']]['website'] = $rows['user_count_website'];
        }
        // var_dump($tableData);
        $sql_mob = "SELECT count(u.ikt_card_no) as user_count_mobile,  FROM_UNIXTIME( `reg_date` , '%Y/%m/%d' ) AS created_date FROM user u 
        WHERE activation_source = 'M' AND reg_date BETWEEN UNIX_TIMESTAMP( '$start' ) AND UNIX_TIMESTAMP( '$end' ) GROUP BY created_date ";
        $stmt_mob = $conn->prepare($sql_mob);
        $stmt_mob->execute();
        $res_mob  = $stmt_mob->fetchAll();
        foreach($res_mob as $rows_mob)
        {
            $tableData[$rows_mob['created_date']]['mobile'] = $rows_mob['user_count_mobile'];
        }
        $allKeys = array_keys($tableData);
        function sortData($item2, $item1){
            return strtotime( $item1) - strtotime($item2);
        };

    usort($allKeys, 'sortData');
    $css = "table td{border: 1px solid #bbbbbb;}
        .center { text-align: center;}
        .center-graylight {text-align: center; background-color: #bbbeee;}
        .highlight {background-color: #ffff00;}
        .center-highlight {text-align: center; background-color: #ffff00;}
        .left { padding-left:20px;text-align: left;}
        .h3{font-weight: bold; font-size: 18px;}
        .bold{font-weight: bold;}
    ";

    //  var_dump($tableData);  die();
    $htmlTable = '<table>
    <tr class ="center"><td colspan="4" class="h3">Number of Registered users from '.$fromDate.' to '. $toDate.'</td></tr>
    <tr class ="center"><td class ="bold">Sr.No</td><td class ="bold">Date</td><td class ="bold">Mobile</td><td class ="bold">Website</td></tr>';$mobile_total  = 0;
    $website_total = 0;
    $index=1;
    foreach($allKeys as $key){
        $row = $tableData[$key];
        $htmlTable      .= '<tr class="'.($index==1?'center-highlight':'center').'"><td>'.$index++.'</td><td>'.$key.'</td><td>'.$row['mobile'].'</td><td>'.$row['website'].'</td></tr>';
        $mobile_total   += $row['mobile'];
        $website_total  += $row['website'];
    }
    $htmlTable .= '<tr class="center-graylight"><td class="left" colspan="2"><strong>Sub Total</strong></td><td><strong>'.$mobile_total.'</strong></td><td><strong>'.$website_total.'</strong></td></tr>';
    $htmlTable .= '<tr class="highlight"><td class="left" colspan="3"><strong>Total</strong></td><td class="center"><strong>'.($mobile_total+$website_total).'</strong></td></tr>';
    $htmlTable .= "</table>";
    $emailBody  = "Number of Registered users from $fromDate to $toDate via mobile app =  $mobile_total  <br>";
    $emailBody .= "Number of Registered users from $fromDate to $toDate via Website    =  $website_total <br>";
    echo $htmlTable;
    // die();
    $xls->setCss($css);
    $xls->addSheet("Total Users", $htmlTable);
    $xls->headers();
    $contents =  $xls->buildFile();
    $this->getParameter('images_directory');
    $filePath = $this->getParameter('files_directory')."/register_log.xls";
    $fhandle  = fopen($filePath, "w+");
    fwrite($fhandle, $contents);
    fclose($fhandle);
        $message = \Swift_Message::newInstance();
        $message->addTo('sa.aspire@gmail.com')
        ->addFrom('hameedkhan97603@gmail.com')
        ->setSubject(AppConstant::EMAIL_SUBJECT)
        ->setBody($this->renderView(':email-templates/users:registered_users_list.html.twig', [
                'message'       => $emailBody,
                'mobile_total'  => $mobile_total,
                'website_total' => $website_total,
                'fromDate'      => $fromDate,
                'toDate'        => $toDate,
            ]), 'text/html');
        $message->attach(\Swift_Attachment::fromPath($this->getParameter('files_directory')."/register_log.xls"));
        $this->get('mailer')->send($message);
        $result = $this->container->get('mailer')->send($message);
    }


    /**
     * @Route("/{_country}/{_locale}/userslist_enq_suggs", name="front_userslist_enqs")
     * @param Request $request
     * @param Response $response
     */
    public function UserslistenqsuggsAction()
    {
        $xls = new ExcelHTML();
        echo $fromDate  = "2018-06-25 10:56:39";  //
        date('Y/m/d', strtotime( '-53 days' ) );
        // echo '<br>';
        $start     = $fromDate." 00:00:00";
        echo $toDate    = "2018-09-02 09:44:56";
        date('Y/m/d', strtotime( '+2 days' ) );
        // $end       = $toDate ." 23:59:59";

        $entityManager = $this->getDoctrine();
        $conn = $entityManager->getConnection();
        echo $sql  = "SELECT * FROM enquiry_and_suggestion WHERE
        created  BETWEEN '$fromDate' AND '$toDate'  ";
        $stmt1 = $conn->prepare($sql);
        $stmt1->execute();
        $res_enq = $stmt1->fetchAll();
        print_r($res_enq);
        $reason_arr = array('C' => 'COMPLAINTS' , 'S' => 'SUGGESTIONS', 'T' => 'TECHNICAL_SUPPORT' , 'E' => 'ENQUIRY' );
        usort($allKeys, 'sortData');
         $css = "table td{border: 1px solid #bbbbbb;}
        .center { text-align: center;}
        .center-graylight {text-align: center; background-color: #bbbeee;}
        .highlight {background-color: #ffff00;}
        .center-highlight {text-align: center; background-color: #ffff00;}
        .left { padding-left:20px;text-align: left;}
        .h3{font-weight: bold; font-size: 18px;}
        .bold{font-weight: bold;}";



        //  var_dump($tableData);  die();
        $htmlTable = '<table>
        <tr class ="center"><td colspan="8" class="h3"></td></tr>
        <tr class ="center">
        <td class ="bold">Sr.No</td>
        <td class ="bold">Name</td> 
        <td class ="bold">Date</td></tr>
        <td class ="bold">Job</td></tr>';
        $htmlTable .= '<tr><td></td></tr>';

        $index = 1;
        foreach($res_enq as $res_enq){
            $htmlTable  .= '<tr class="'.($index==1?'center-highlight':'center').'">
            <td>'.$index++.'</td>
            <td>'.$res_enq["name"].'</td>
            <td>'.$res_enq["job"].'</td>
            <td>'.$res_enq["mobile"].'</td>
            <td>'.$res_enq["email"].'</td>
            <td>'.$res_enq['reason'].'</td>
            <td>'.$res_enq["comments"].'</td>
            <td>'.$res_enq["Date"].'</td>
            </tr>';
        }

        $serializer = new Serializer([new ObjectNormalizer()], [new CsvEncoder()]);

        // instantiation, when using it inside the Symfony framework
        $serializer = $this->get('serializer');
        $data = [
            'foo' => 'aaa',
            'bar' => [
                ['id'    => 111, 1 => 'bbb'],
                ['lorem' => 'ipsum'],
            ]
        ];

        file_put_contents (
            'data.csv',
            $this->get('serializer')->encode($data, 'csv')
        );

        $htmlTable .= "</table>";
        echo $htmlTable;
        // die();
        $xls->setCss($css);
        $xls->addSheet("Total Records", $htmlTable);
        $xls->headers();
        $contents =  $xls->buildFile();
        $filePath = $this->getParameter('files_directory')."/enq_sugg_list.xls";
        $fhandle  = fopen($filePath, "w+");
        fwrite($fhandle, $contents);
        fclose($fhandle);
    }
    /**
     * @Route("/{_country}/{_locale}/userslist_enq_sugg", name="front_userslist_enq")
     * @param Request $request
     * @param Response $response
     */
    public function userslist_enq_suggAction()
    {
        $xls = new ExcelHTML();
        echo $fromDate  = "2018-06-25 10:56:39";  //
        date('Y/m/d', strtotime( '-53 days' ) );
        // echo '<br>';
        $start     = $fromDate." 00:00:00";
        echo $toDate    = "2018-09-02 09:44:56";
        date('Y/m/d', strtotime( '+2 days' ) );
        // $end       = $toDate ." 23:59:59";

        $entityManager = $this->getDoctrine();
        $conn = $entityManager->getConnection();
        echo $sql  = "SELECT * FROM enquiry_and_suggestion WHERE
        created  BETWEEN '$fromDate' AND '$toDate'  ";
        $stmt1 = $conn->prepare($sql);
        $stmt1->execute();
        $res_enq = $stmt1->fetchAll();
        print_r($res_enq);
        $reason_arr = array('C' => 'COMPLAINTS' , 'S' => 'SUGGESTIONS', 'T' => 'TECHNICAL_SUPPORT' , 'E' => 'ENQUIRY' );
        usort($allKeys, 'sortData');
        $css = "table td{border: 1px solid #bbbbbb;}
        .center { text-align: center;}
        .center-graylight {text-align: center; background-color: #bbbeee;}
        .highlight {background-color: #ffff00;}
        .center-highlight {text-align: center; background-color: #ffff00;}
        .left { padding-left:20px;text-align: left;}
        .h3{font-weight: bold; font-size: 18px;}
        .bold{font-weight: bold;}";



        //  var_dump($tableData);  die();
        $htmlTable = '<table>
        <tr class ="center"><td colspan="8" class="h3"></td></tr>
        <tr class ="center">
        <td class ="bold">Sr.No</td>
        <td class ="bold">Name</td> 
        <td class ="bold">Date</td></tr>
        <td class ="bold">Job</td></tr>';
        $htmlTable .= '<tr><td></td></tr>';

        $index = 1;
        foreach($res_enq as $res_enq){
            $htmlTable  .= '<tr class="'.($index==1?'center-highlight':'center').'">
            <td>'.$index++.'</td>
            <td>'.$res_enq["name"].'</td>
            <td>'.$res_enq["job"].'</td>
            <td>'.$res_enq["mobile"].'</td>
            <td>'.$res_enq["email"].'</td>
            <td>'.$res_enq['reason'].'</td>
            <td>'.$res_enq["comments"].'</td>
            <td>'.$res_enq["Date"].'</td>
            </tr>';
        }
        $htmlTable .= "</table>";
        echo $htmlTable;
        die();
        $xls->setCss($css);
        $xls->addSheet("Total Records", $htmlTable);
        $xls->headers();
        $contents =  $xls->buildFile();
        $filePath =  $this->getParameter('files_directory')."/enq_sugg_list.xls";
        $fhandle  =  fopen($filePath, "w+");
        fwrite($fhandle, $contents);
        fclose($fhandle);
    }




}