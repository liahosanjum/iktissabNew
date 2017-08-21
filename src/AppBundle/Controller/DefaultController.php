<?php

namespace AppBundle\Controller;

use AppBundle\AppConstant;
use AppBundle\Entity\Subscription;
use AppBundle\Entity\User;
use Captcha\Bundle\CaptchaBundle\Form\Type\CaptchaType;
use Captcha\Bundle\CaptchaBundle\Validator\Constraints as CaptchaAssert;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Doctrine\ORM\Query\ResultSetMapping;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
// use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use AppBundle\Controller\Common\FunctionsController;



class DefaultController extends Controller
{
    /**
     * @Route("/", name="landingpage")
     */
    public function indexAction(Request $request)
    {

        try {


            $cookieLocale = $request->cookies->get(AppConstant::COOKIE_LOCALE);
            $cookieCountry = $request->cookies->get(AppConstant::COOKIE_COUNTRY);
            if (isset($cookieLocale) && $cookieLocale <> '' && isset($cookieCountry) && $cookieCountry <> '') {
                return $this->redirect($this->generateUrl('homepage', array('_country' => $cookieCountry, '_locale' => $cookieLocale)));
            }
            /*
            else
            {
                // here get cookie from othaimmarkets.com website for country
                $cookie_country_from  = 'sa';
                // here get cookie from othaimmarkets.com website for language
                $cookie_language_from = 'ar';
                $response = new Response();
                $cookieLocale  = $request->cookies->get(AppConstant::COOKIE_LOCALE);
                $cookieCountry = $request->cookies->get(AppConstant::COOKIE_COUNTRY);
                $locale  = $request->getLocale();
                $country = $request->get('_country');

                $locale  = $cookie_language_from;
                $country = $cookie_country_from;
                if((!isset($cookieLocale)) && ($cookieLocale == '') && (!isset($cookieCountry)) && ($cookieCountry == '')){
                    $response->headers->setCookie(new Cookie(AppConstant::COOKIE_LOCALE, $locale, time() + AppConstant::COOKIE_EXPIRY, '/', null, false, false));
                    $response->headers->setCookie(new Cookie(AppConstant::COOKIE_COUNTRY, $country, time() + AppConstant::COOKIE_EXPIRY, '/', null, false, false));
                    $response->sendHeaders();
                    return $this->redirect($this->generateUrl('homepage', array('_country'=> $country,'_locale'=> $locale)));
                }
            }
            */

            return $this->render('default/index.html.twig', [
                'base_dir' => realpath(
                        $this->getParameter('kernel.root_dir') . '/..') . DIRECTORY_SEPARATOR,
            ]);
        }
        catch (Exception $e){
            return $this->render('default/index.html.twig', [
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
        $cookieLocale = $request->cookies->get(AppConstant::COOKIE_LOCALE);
        $cookieCountry = $request->cookies->get(AppConstant::COOKIE_COUNTRY);
        $locale  = $request->getLocale();
        $country = $request->get('_country');
        if((!isset($cookieLocale)) && ($cookieLocale == '') && (!isset($cookieCountry)) && ($cookieCountry == '')){
            $response->headers->setCookie(new Cookie(AppConstant::COOKIE_LOCALE, $locale, time() + AppConstant::COOKIE_EXPIRY, '/', null, false, false));
            $response->headers->setCookie(new Cookie(AppConstant::COOKIE_COUNTRY, $country, time() + AppConstant::COOKIE_EXPIRY, '/', null, false, false));
            $response->sendHeaders();
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
        return;

    }








    /**
     * @Route("/{_country}/{_locale}/", name="homepage")
     * @param Request $request
     * @param Response $response
     */
    public function homepageAction(Request $request)
    {
        try {


            $response = new Response();

            $commFunct = new FunctionsController();
            if ($commFunct->checkSessionCookies($request) == false) {
                return $this->redirect($this->generateUrl('landingpage'));
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


            /********/
            // get all news from db
            if ($request->cookies->get(AppConstant::COOKIE_COUNTRY)) {
                $news_country = $request->cookies->get(AppConstant::COOKIE_COUNTRY);
            } else {
                $news_country = 'sa';
            }
            $em = $this->getDoctrine()->getManager();
            $homePageNews = $em->getRepository('AppBundle:CmsPages')
                ->findBy(array('status' => '1', 'type' => 'news', 'country' => $news_country));
            //print_r($homePageNews);

            $data_news = array();
            $i = 0;
            foreach ($homePageNews as $homePageNew) {
                $data_news[$i]['id'] = $homePageNew->getId();
                // echo '<br>';
                if ($request->cookies->get(AppConstant::COOKIE_LOCALE) == 'ar') {
                    $data_news[$i]['details'] = $homePageNew->getAdesc();
                } else {
                    $data_news[$i]['details'] = $homePageNew->getEdesc();
                }
                $i++;
            }


            /********/

            $restClient = $this->get('app.rest_client');
            $url = AppConstant::OTHAIM_WEBSERVICE_URL;


            $postData = "{\"service\":\"IktissabPromotions\"} ";
            $data = $restClient->restPostForm($url,
                $postData, array('input-content-type' => "text/json", 'output-content-type' => "text/json",
                    'language' => $locale
                ));
            
            $data_dec = json_decode($data, true);
            $data_dec['success'];
            $listing="";
            if ($data_dec['success'] == true) {
                $products = json_decode($data);
                // var_dump(json_decode($data));
                // echo "<br>";
                // var_dump($products->products[0]);
                // var_dump($products->success);
                if ($products->success == true) {


                    $pro = $products->products;

                    // var_dump($pro);
                    $i = 0;
                    foreach ($products->products as $data) {
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
                    return $this->render('front/homepage.html.twig', array('DataPromo' => $listing, 'data_news' => $data_news));
                } else {
                    return $this->render('front/homepage.html.twig', array('DataPromo' => "", 'data_news' => $data_news));
                }
            } else {
                return $this->render('front/homepage.html.twig', array('DataPromo' => "", 'data_news' => $data_news));
            }
        }
        catch(Exception $e){
            $message = $this->get('translator')->trans('An invalid exception occurred');

            return $this->render('front/homepage.html.twig', array('DataPromo' => "", 'data_news' => $data_news));
        }
        /********/

        // return $this->render('front/homepage.html.twig');
    }


    /**
     * @Route("/new", name="new")
     */
    public function newAction(Request $request)
    {
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());

        $serializer = new Serializer($normalizers, $encoders);
        echo "i am in the new action";

        $person = array('name' => 'Abdul basit',
            'age' => '44',
            'address' => 'office colony '
        );
        $jsonContent = $serializer->serialize($person, 'json');
        echo $jsonContent;
        return new Response();

    }


    /**
     * @Route("/{_country}/{_locale}/list/{id}/", name="front_cmscontent")
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
            if ($commFunct->checkSessionCookies($request) == false) {
                return $this->redirect($this->generateUrl('landingpage'));
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
            $em = $this->getDoctrine()->getEntityManager();
            $cmspages = $this->getDoctrine()
                ->getRepository('AppBundle:CmsPages')
                ->findOneBy(array('id' => $id, 'status' => 1, 'type' => 'CMS',
                    'country' => 'sa',
                ));
            $data = array();
            $i = 0;
            $error = 'alert-danger';
            if (!$cmspages) {
                return $this->render('front/cms/contents.html.twig', array('Data' => "",  'errorcl' => $error,

                    'message' => 'No record found'));
            } else {
                $error = '';
                return $this->render('front/cms/contents.html.twig', array('data' => $data, 'message' => '',
                    'errorcl' => $error,
                    'Data' => array('id' => $cmspages->getId(), 'Atitle' => $cmspages->getAtitle(), 'Adesc' => $cmspages->getAdesc(), 'Edesc' => $cmspages->getEdesc(), 'Etitle' => $cmspages->getEtitle())));
            }
        }
        catch(Exception $e){
            $error = 'alert-danger';
            $message = $this->get('translator')->trans('An invalid exception occurred');
            return $this->render('front/cms/contents.html.twig', array('Data' => "", 'message' => $message,
                'errorcl' => $error
                ));
        }
        catch (AccessDeniedException $ed)
        {
            $message = $ed->getMessage();
            $error['success'] = false;
            $error['message'] = $this->get('translator')->trans($ed->getMessage());
            return $this->render('/default/send_pwd.twig',
                array(
                    'form'    => $form->createView(),
                    'error'   => $error
                )
            );
        }




    }


    /**
     * @Route("/{_country}/{_locale}/forgotpassword", name="forgotpassword")
     */
    public function forgotPassword(Request $request)
    {
        try {
            $commFunct = new FunctionsController();
            if ($commFunct->checkSessionCookies($request) == false) {
                return $this->redirect($this->generateUrl('landingpage'));
            }
            $error = "";
            $locale_cookie = $request->getLocale();
            $country_cookie = $request->get('_country');
            $userLang = trim($request->query->get('lang'));
            if ($userLang != '' && $userLang != null) {
                if ($userLang == $locale_cookie) {
                    $request->getLocale();
                    $commFunct->changeLanguage($request, $userLang);
                    $locale_cookie = $request->getLocale();
                } else {
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
            $posted = array();
            $postData = $request->request->all();
            if ($postData) {
                // 1 get user password according to the email provided
                $em = $this->getDoctrine()->getManager();
                $conn = $em->getConnection();
                $email = $postData['email'];

                $country_id = $this->getCountryCode($request);
                $locale = $this->getCountryLocal($request);
                $stm = $conn->prepare('SELECT * FROM user WHERE  email = ?   ');
                // $stm = $conn->prepare('SELECT * FROM user WHERE country = ? AND email = ?   ');
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
                    //$user_id_en = base64_encode($user_id . AppConstant::SECRET_KEY_FP);
                    $user_id_en = $this->base64url_encode($user_id . AppConstant::SECRET_KEY_FP);
                    $time = time();
                    $token = uniqid() . sha1($email . time() . rand(111111, 999999) . $user_id);
                    $link = $this->generateUrl('resetpassword', array('_country' => $country_id, '_locale' => $locale, 'time' => $time, 'token' => $token), UrlGenerator::ABSOLUTE_URL);
                    $data = serialize(array('time' => $time, 'token' => $token));
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


                    if ($stm == 1) {
                        $message = \Swift_Message::newInstance();
                        $request = new Request();
                        //--> $email   = 'sa.aspire@gmail.com';
                        $message->addTo($email);
                        $message->addFrom($this->container->getParameter('mailer_user'))
                            ->setSubject(AppConstant::EMAIL_SUBJECT)
                            ->setBody(
                                $this->container->get('templating')->render(':email-templates/forgot_password:forgot_password.html.twig', [
                                    'email' => $email,
                                    'link' => $link
                                ]),
                                'text/html'
                            );

                        if ($this->container->get('mailer')->send($message)) {
                            $message = $this->get('translator')->trans('If there is an account associated with this E-mail then you will receive an email with a link to reset your password');
                            $activityLog->logEvent(AppConstant::ACTIVITY_FORGOT_PASSWORD_SUCCESS, $user_id, array('iktissab_card_no' => $user_id, 'message' => $message, 'session' => serialize($result)));
                            $errorcl = 'alert-success';
                            return $this->render('default/login.html.twig', array(
                                'message' => $message, 'error' => $error, 'errorcl' => $errorcl
                            ));
                        } else {
                            $message = $this->get('translator')->trans('Email has not been sent');
                            $activityLog->logEvent(AppConstant::ACTIVITY_FORGOT_PASSWORD_ERROR, $user_id, array('iktissab_card_no' => $user_id, 'message' => $message, 'session' => $result));
                            $errorcl = 'alert-danger';
                            return $this->render('default/login.html.twig', array(
                                'message' => $message, 'error' => $error, 'errorcl' => $errorcl

                            ));
                        }

                    }
                } else {
                    //$message = $this->get('translator')->trans('Sorry , ') . $email . $this->get('translator')->trans('is not recognized as a user name or an e-mail address');
                    $message = $this->get('translator')->trans('If there is an account associated with this E-mail then you will receive an email with a link to reset your password');

                    $activityLog->logEvent(AppConstant::ACTIVITY_FORGOT_PASSWORD_ERROR, 'unknownuser ' . $email, array('iktissab_card_no' => 'unknownuser ' . $email, 'message' => $message, 'session' => $result));
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
        catch(Exception $e){
            $message = $this->get('translator')->trans('An invalid exception occurred');

            $errorcl = 'alert-danger';
            return $this->render('default/login.html.twig', array(
                'message' => $message, 'error' => $error, 'errorcl' => $errorcl
            ));
        }

    }

    /**
     * @Route("/{_country}/{_locale}/forgotpassword", name="forgotpasswordbk")
     */
    public function forgotPasswordbkAction(Request $request, $message)
    {

        $commFunct = new FunctionsController();
        if($commFunct->checkSessionCookies($request) == false){
            return $this->redirect($this->generateUrl('landingpage'));
        }

        $form = $this->createFormBuilder(null, ['attr' => ['novalidate' => 'novalidate']])
            ->add('email', EmailType::class, array('label' => 'E-mail address',
                'constraints' => array(
                    new NotBlank(array('message' => 'This field is required')),
                )))
            ->add('captchaCode', CaptchaType::class, array('constraints' => array(
                new NotBlank(array('message' => 'This field is required')),
                new CaptchaAssert\ValidCaptcha (array('message' => 'Invalid captcha code'))),
                'captchaConfig' => 'FormCaptcha',
                'label' => 'Captcha Code'
            ))
            ->add('forgot_password', SubmitType::class, array(
                'attr' => array('class' => 'btn btn-primary', 'id' => 'forgot_password'),
                'label' => $this->get('translator')->trans('E-mail new password'),
            ))->getForm();


        $locale_cookie  = $request->getLocale();
        $country_cookie = $request->get('_country');
        $userLang = trim($request->query->get('lang'));
        if ($userLang != '' && $userLang != null) {
            if($userLang == $locale_cookie) {
                $request->getLocale();
                $commFunct->changeLanguage($request, $userLang);
                $locale_cookie = $request->getLocale();
            } else {
                if($request->cookies->get(AppConstant::COOKIE_LOCALE)){
                    return $this->redirect($this->generateUrl('forgotpassword', array('_country' => $request->cookies->get(AppConstant::COOKIE_COUNTRY), '_locale' => $request->cookies->get(AppConstant::COOKIE_LOCALE))));
                }
            }
        }
        if($request->cookies->get(AppConstant::COOKIE_LOCALE)) {
            $cookieLocale = $request->cookies->get(AppConstant::COOKIE_LOCALE);
            $cookieCountry = $request->cookies->get(AppConstant::COOKIE_COUNTRY);
            if (isset($cookieLocale) && $cookieLocale <> '' && $cookieLocale != $locale_cookie) {
                // modify here if the language is to be changes forom the uprl
                return $this->redirect($this->generateUrl('forgotpassword', array('_country' => $cookieCountry, '_locale' => $cookieLocale)));
            }
            if (isset($cookieCountry) && $cookieCountry <> '' && $cookieCountry != $country_cookie) {
                return $this->redirect($this->generateUrl('forgotpassword', array('_country' => $cookieCountry, '_locale' => $cookieLocale)));
            }
        }









        $activityLog = $this->get('app.activity_log');
        $form->handleRequest($request);
        // $posted = array();
        // $postData = $request->request->all();
        if ($form->isSubmitted() && $form->isValid()) {
            // 1 get user password according to the email provided
            $em = $this->getDoctrine()->getManager();
            $conn = $em->getConnection();
            $email = $form->get('email')->getData();

            $country_id = $this->getCountryCode($request);
            $locale = $this->getCountryLocal($request);

            $stm = $conn->prepare('SELECT * FROM user WHERE country = ? AND email = ?   ');
            $stm->bindValue(1, $country_id);
            $stm->bindValue(2, $email);
            // here checking the others equal to 1.
            $stm->execute();
            $result = $stm->fetchAll();
            if ($result) {
                // send email
                $email = $email; //'sa.aspire@gmail.com';
                //--> $email = $result[0]['email'];
                //--> print_r($result);
                $user_id = $result[0]['ikt_card_no'];
                //todo: bk
                $user_id_en = $user_id  ;//$this->encrypt($user_id, AppConstant::SECRET_KEY_FP);
                $user_id_en = base64_encode($user_id . AppConstant::SECRET_KEY_FP);

                $time  = time();
                $token = uniqid() . md5($email . time() . rand(111111, 999999));

                $link = $this->generateUrl('resetpassword', array('_country' => $country_id, '_locale' => $locale, 'time' => $time, 'token' => $token, 'id' => $user_id_en), UrlGenerator::ABSOLUTE_URL);
                $data = serialize(array('time' => $time, 'token' => $token));
                $data_values = array(
                    $data,
                    $country_id,
                    $user_id,
                );
                //print_r($data_values);
                $stm = $conn->executeUpdate('UPDATE user  SET  
                                                    data    = ?
                                                    WHERE country = ?  AND ikt_card_no = ? ', $data_values);
                if ($stm == 1) {
                    $message = \Swift_Message::newInstance();
                    $request = new Request();
                    // --> $email   = 'sa.aspire@gmail.com';
                    $message->addTo($email);
                    $message->addFrom($this->container->getParameter('mailer_user'))
                        ->setSubject(AppConstant::EMAIL_SUBJECT)
                        ->setBody(
                            $this->container->get('templating')->render(':email-templates/forgot_password:forgot_password.html.twig', [
                                'email' => $email,
                                'link' => $link
                            ]),
                            'text/html'
                        );

                    if ($this->container->get('mailer')->send($message)) {
                        $message = $this->get('translator')->trans('Further instructions have been sent to your e-mail address');
                        $activityLog->logEvent(AppConstant::ACTIVITY_FORGOT_PASSWORD_SUCCESS, $user_id, array('iktissab_card_no' => $user_id, 'message' => $message, 'session' => serialize($result)));

                        return $this->render('front/forgotpassword.html.twig', array(
                            'form' => $form->createView(), 'message' => $message,
                        ));
                    } else {
                        $message = $this->get('translator')->trans('Email has not been sent');
                        $activityLog->logEvent(AppConstant::ACTIVITY_FORGOT_PASSWORD_ERROR, $user_id, array('iktissab_card_no' => $user_id, 'message' => $message, 'session' => $result));

                        return $this->render('front/forgotpassword.html.twig', array(
                            'form' => $form->createView(), 'message' => $message,
                        ));
                    }

                }
            } else {
                $message = $this->get('translator')->trans('Sorry , ') . $email . $this->get('translator')->trans(' is not recognized as a user name or an e-mail address');
                $activityLog->logEvent(AppConstant::ACTIVITY_FORGOT_PASSWORD_ERROR, 'unknownuser ' . $email, array('iktissab_card_no' => 'unknownuser ' . $email, 'message' => $message, 'session' => $result));

                return $this->render('front/forgotpassword.html.twig', array(
                    'form' => $form->createView(), 'message' => $message,
                ));
            }
        }
        $message = "";
        return $this->render('front/forgotpassword.html.twig', array(
            'form' => $form->createView(), 'message' => $message,
        ));
    }







    private function getCountryCode(Request $request)
    {
        return $country_id = $request->get('_country');
    }

    private function getCountryLocal(Request $request)
    {
        return $locale = $request->getLocale();
    }

    public function encrypt($data, $key)
    {
        return base64_encode(
            mcrypt_encrypt(MCRYPT_RIJNDAEL_128,
                $key,
                $data,
                MCRYPT_MODE_CBC,
                "\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0"
            )
        );
    }

    /**
     * @Route("/{_country}/{_locale}/resetpassword/{time}/{token}", name="resetpassword")
     * @param $time
     * @param $token
     */

    public function resetPasswordAction(Request $request, $time, $token)
    {
        try {

            $commFunct = new FunctionsController();
            //
            $activityLog = $this->get('app.activity_log');
            $em = $this->getDoctrine()->getManager();
            $time = (integer)$time;
            $dataValue = serialize(array('time' => $time, 'token' => $token));

            //todo:
            //$id    = $id;
            ////$this->decrypt($id, AppConstant::SECRET_KEY_FP);
            // $id = base64_decode($id . AppConstant::SECRET_KEY_FP);
            //$id = $this->base64url_decode($id . AppConstant::SECRET_KEY_FP);


            $user = $em->getRepository('AppBundle:User')->findOneBy(array("data" => $dataValue));

            //$user  = $em->getRepository('AppBundle:User')->findOneBy(array("data" => $dataValue , 'iktCardNo' => $id));


            if (isset($user) && $user != null) {
                $user_email = $user->getEmail();
                $id = $user->getIktCardNo();
            } else {
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
                            $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_RESETPASSWORD_ERROR, $id, array('iktissab_card_no' => $id, 'message' => $message, 'session' => $user));
                            $errorcl = 'alert-danger';
                            return $this->render('front/resetpassword.html.twig', array(
                                'form' => $form->createView(), 'message' => $message, 'data' => $data_form,
                                'errorcl' => $errorcl

                            ));
                        } else
                        {
                            /*****************/
                            $C_id = $id;
                            $reset_password = $this->resetPasswordOffline($request, md5($formData['password']) , $C_id);
                            /*****************/
                            if($reset_password == true)
                            {
                                $user->setPassword(md5($formData['password']));
                                $user->setData('');
                                $em->persist($user);
                                $em->flush();
                                $message = $this->get('translator')->trans('Your password has been reset successfully');
                                $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_RESETPASSWORD_SUCCESS, $id, array('iktissab_card_no' => $id, 'message' => $message, 'session' => $user));
                                $errorcl = 'alert-success';
                                return $this->render('front/resetpassword.html.twig', array(
                                    'form' => $form->createView(), 'message' => $message, 'data' => $data_form,
                                    'errorcl' => $errorcl
                             
                                ));
                            }
                            else{
                                $message = $this->get('translator')->trans('Your password has not been reset successfully');
                                $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_RESETPASSWORD_SUCCESS, $id, array('iktissab_card_no' => $id, 'message' => $message, 'session' => $user));
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
                    $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_RESETPASSWORD_ERROR, $id, array('iktissab_card_no' => $id, 'message' => $message, 'session' => $user));
                    $errorcl = 'alert-danger';
                    return $this->render('front/resetpassword.html.twig', array(
                        'form' => $form->createView(), 'message' => $message, 'data' => $data_form,
                        'errorcl' => $errorcl
                    ));
                }
            } else {
                $message = $this->get('translator')->trans('Your link to reset password has been expired');
                $data_form['show_form'] = 0;
                $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_RESETPASSWORD_ERROR, $id, array('iktissab_card_no' => $id, 'message' => $message, 'session' => $user));
                $errorcl = 'alert-danger';
                return $this->render('front/resetpassword.html.twig', array(
                    'form' => $form->createView(), 'message' => $message, 'data' => $data_form,
                    'errorcl' => $errorcl
                ));
            }
        }
        catch(Exception $e)
        {
            $message = $this->get('translator')->trans('An invalid exception occurred');
            $data_form['show_form'] = 0;
            $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_RESETPASSWORD_ERROR, $id, array('iktissab_card_no' => $id, 'message' => $message, 'session' => $user));
            $errorcl = 'alert-danger';
            return $this->render('front/resetpassword.html.twig', array(
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
        $current_time = date('Y-m-d');
        $days_difference = 1;
        $em = $this->getDoctrine()->getEntityManager();
        $formsettingsList = $this->getDoctrine()
            ->getRepository('AppBundle:FormSettings')
            ->findOneBy(array('status' => 1, 'formtype' => 'Contact Us'));
        print_r($formsettingsList);
        echo '<br>';
        //echo $formsettingsList->getId();
        $data = array();
        $i = 0;
        $time = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
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
        $em = $this->getDoctrine()->getEntityManager();
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
        $formMobile = $this->get('form.factory')->createNamedBuilder('subs_mob','Symfony\Component\Form\Extension\Core\Type\FormType',null, ['attr' => ['novalidate' => 'novalidate']])
            ->add('mobile', TextType::class, array(
                'label' => 'Mobile',

                'attr' => array('placeholder'=> ($country == 'sa' ? '0xxxxxxxxx' : '00201xxxxxxxxx' ) , 'maxlength'=> ($request->get('country') == 'sa') ? 10 : 14),
                'constraints' => array(
                    new Assert\NotBlank(array('message' => 'This field is required')),
                    new Assert\Regex(
                        array(
                            'pattern' => ($country == 'sa') ? '/^[5]([0-9]){8}$/' : '/^([0-9]){14}$/',
                            'match' => true,
                            'message' => "Mobile Number Must be ".($country == 'sa' ? '10' : '14' )." digits")
                    ),

                )
            ))
            ->add('join_us', SubmitType::class,array(

                'label' => $this->get('translator')->trans('Join Us'),
            ))
            ->getForm();

        $formEmail->handleRequest($request);
        if ($formEmail->isSubmitted()) {
            if (!$formEmail->isValid()) {
                //throw error
                $errormsg = '';
                foreach ($formEmail->getErrors(true, true) as $key => $value) {
                    $errormsg .= $value->getMessage();
                }
                $return = array('error' => true, 'message' => $errormsg);
            }

            if ($formEmail->isValid()) {
                $formEmailData = $formEmail->getData();
                $subscription = new Subscription();
                // check if already exist
                $emailExist = $em->getRepository('AppBundle:Subscription')->findOneBy(array('subsVal' => $formEmailData['email'], 'subsType' => 'email'));
                if (!$emailExist) {
                    $subscription->setSubsVal($formEmailData['email']);
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
        if ($formMobile->isSubmitted()) {
            if (!$formMobile->isValid()) {
                //throw error
                $errormsg = '';
                foreach ($formMobile->getErrors(true, true) as $key => $value) {
                    $errormsg .= $value->getMessage();
                }
                $return = array('error' => true, 'message' => $errormsg);
            }

            if ($formMobile->isValid()) {
                $formMobData = $formMobile->getData();
                $subscription = new Subscription();
                // check if already exist
                $mobileExist = $em->getRepository('AppBundle:Subscription')->findOneBy(array('subsVal' => $formMobData['mobile'], 'subsType' => 'mobile'));
                if (!$mobileExist) {
                    $subscription->setSubsVal($formMobData['mobile']);
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
                'form' => $formEmail->createView(),
                'form_mob' => $formMobile->createView(),
                'country' => $country
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

        $restClient = $this->get('app.rest_client');
        if($request->get('_country') == 'eg'){
            $promoUrl = AppConstant::PROMOTIONS_PATH_EG;
        }else {
            $promoUrl = AppConstant::PROMOTIONS_PATH;
        }
        
        $promotions = $restClient->restGet($promoUrl);
        $promotions = json_decode($promotions,true);
        $enabledPromo = array();
        foreach ($promotions as $promo) {
            if ($promo['is_active'] == 1)
                $enabledPromo[] = $promo['id'];
        }
        if (in_array($request->get('id'), $enabledPromo)) {
            $id = $request->get('id');
        } else {
            $id = $enabledPromo[0];
        }
        return $this->render('/default/promotions.html.twig',
            array(
                'promotions' => $promotions,
                'id' => $id
            )
        );
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


        $userLang  =  trim($request->query->get('lang'));
        $id  =  trim($request->query->get('id'));
        //$extra_param = '/sa/en/16/cms';
        //print_r($extra_param);
        $url  =  trim($request->query->get('url'));
        $commFunct = new FunctionsController();
        $cookieCountry = $request->cookies->get(AppConstant::COOKIE_COUNTRY);
        $commFunct->changeLanguage($request, $userLang);


        if($url == "" || $url == null ){
            $url = 'homepage';
        }
        //return $this->redirect($this->generateUrl($url, array('_country'=>$cookieCountry , '_locale'=>$userLang,)));
        if($id == "" || $id == null){
            return $this->redirect($this->generateUrl($url, array('_country'=>$cookieCountry , '_locale'=>$userLang)));
        } else {
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
        $tokenStorage = $this->get('security.token_storage');
        $userCountry  =  trim($request->query->get('ccid'));
        $commFunct = new FunctionsController();
        $cookieLocale = $request->cookies->get(AppConstant::COOKIE_LOCALE);
        $commFunct->changeCountry($request, $userCountry);
        if($this->get('session')->get('iktUserData')) {
            //return $this->redirect($this->generateUrl('account_logout', array('_country' => $userCountry, '_locale' => $cookieLocale)));
            return $this->redirect($this->generateUrl('account_logout', array('_country' => $userCountry, '_locale' => $cookieLocale)));
        }
        else{
            return $this->redirect($this->generateUrl('homepage', array('_country' => $userCountry, '_locale' => $cookieLocale)));
        }
    }



    function base64url_encode($s) {
        return str_replace(array('+', '/'), array('-', '_'), base64_encode($s));
    }

    function base64url_decode($s) {
        return base64_decode(str_replace(array('-', '_'), array('+', '/'), $s));
    }





    public function resetPasswordOffline(Request $request , $password , $C_id)
    {
        try
        {
            $country_id  = $request->get('_country');
            $restClient  = $this->get('app.rest_client');
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
                    if($data['success'] == 1){
                        return true;
                    }else{
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

















}

