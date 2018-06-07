<?php
/**
 * Created by PhpStorm.
 * User: abdulali
 * Date: 12/28/16
 * Time: 12:44 PM
 */

namespace AppBundle\Controller\Front;


use AppBundle\AppConstant;
use AppBundle\Controller\Common\FunctionsController;
use AppBundle\Entity\User;
use AppBundle\Form\ActivateCardoneType;
use AppBundle\Form\EnterOtpType;
use AppBundle\Form\IktCustomerInfo;
use AppBundle\Form\IktRegType;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Debug\Exception\UndefinedFunctionException;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

class ActivationController extends Controller
{
    /**
     * @Route("/{_country}/{_locale}/card-activation", name="front_card_activation")
     * @param Request $request
     * @return Response
     */
    public function cardActivationAction(Request $request)
    {
        // if user is logged in send him to home page
        if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('homepage',array('_country' => $request->cookies->get(AppConstant::COOKIE_COUNTRY), '_locale' => $request->cookies->get(AppConstant::COOKIE_LOCALE)));
        }

        /***************/
        $commFunct = new FunctionsController();
        $commFunct->setContainer($this->container);

        if ($commFunct->checkSessionCookies($request) == false) {
            return $this->redirect($this->generateUrl('landingpage'));
        }
        /***********/

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




        $error = array('success' => true);
        $country_id  = $request->get('_country');
        $form = $this->createForm(ActivateCardoneType::class, array() ,
            array('extras' => array('country' => $country_id)
            ));
        $form->handleRequest($request);
        /***************/

        $response         = new Response();
        $form->get('captchaCode')->getData();
        $this->get('session')->get('_CAPTCHA');

        /***************/
        $pData = $form->getData();
        if ($form->isSubmitted() && $form->isValid())
        {
            $errors = "";
            //exit;
            $validate_data = array($pData['iktCardNo'] , strtolower($pData['email']),
                $pData['captchaCode'] );
            //exit;
            $errors = $commFunct->validateData($validate_data);

            if($errors > 0)
            {
                $commFunct->setCsrfToken('front_activation_step1');
                $session = new Session();
                $token = $session->get('front_activation_step1');

                $config = array();
                $filename = $commFunct->saveTextAsImage($config);
                $response->setContent($filename['filename']);
                $captcha_image = $filename['image_captcha'];
                $error['success'] = false;
                $error['message'] = $this->get('translator')->trans('Please provide valid data');
                $error_cl = 'alert-danger';
                return $this->render('/activation/activation.twig',
                    array(
                        'form'  => $form->createView(),
                        'error' => $error,
                        'token' => $token,
                        'data'  => $captcha_image,
                    )
                );

            }




            try
            {
                if($commFunct->checkCsrfToken($form->get('token')->getData(), 'front_activation_step1' ))
                {
                    /***********************/
                    $captchaCode = trim(strtoupper($this->get('session')->get('_CAPTCHA')));
                    $captchaCodeSubmitted = trim(strtoupper($form->get('captchaCode')->getData()));
                    $captchaCodeSubmitted = trim($form->get('captchaCode')->getData());
                    if ($captchaCodeSubmitted != $captchaCode) {
                        $commFunct->setCsrfToken('front_activation_step1');
                        $session = new Session();
                        $token = $session->get('front_activation_step1');

                        $config = array();
                        $filename = $commFunct->saveTextAsImage($config);
                        $response->setContent($filename['filename']);
                        $captcha_image = $filename['image_captcha'];
                        $error['success'] = false;
                        $error['message'] = $this->get('translator')->trans('Invalid captcha code');
                        $error_cl = 'alert-danger';
                        return $this->render('/activation/activation.twig',
                            array(
                                'form'  => $form->createView(),
                                'error' => $error,
                                'token' => $token,
                                'data'  => $captcha_image,
                            )
                        );
                    }
                    /***********************/
                    $this->checkOnline($pData);

                    // check if card valid from local/office ikt database
                    $scenerio = $this->checkScenerio($pData['iktCardNo']);

                    // proceed to next step with full form registration
                    $this->get('session')->set('scenerio', $scenerio);
                    $this->get('session')->set('iktCardNo', strip_tags($pData['iktCardNo']));
                    $this->get('session')->set('email', strip_tags(strtolower($pData['email'])));
                    $acrivityLog = $this->get('app.activity_log');
                    $this->checkInStagging($pData['iktCardNo']);
                    if ($this->get('session')->get('scenerio') == AppConstant::IKT_REG_SCENERIO_1) {
                        // make log

                        $acrivityLog->logEvent(AppConstant::ACTIVITY_NEW_CARD_REGISTRATION, $pData['iktCardNo'],
                            array('ikt_card' => strip_tags($pData['iktCardNo']), 'email' => strip_tags(strtolower($pData['email'])) ,
                                'step_info' => 'step 1 of scenario 1'), null , null);
                        // proceed to full form registration
                        return $this->redirectToRoute('customer_information', array('_locale' => $request->getLocale(), '_country' => $request->get('_country')));
                    } elseif ($this->get('session')->get('scenerio') == AppConstant::IKT_REG_SCENERIO_2) {
                        //echo "inside elseif";
                        $acrivityLog->logEvent(AppConstant::ACTIVITY_EXISTING_CARD_REGISTRATION, $pData['iktCardNo'],
                            array('ikt_card' => strip_tags($pData['iktCardNo']), 'email' => strip_tags(strtolower($pData['email'])),
                                'step_info' => 'step 1 of scenario 2' ),null,null);
                        // proceed to only update email page to login
                        return $this->redirectToRoute('activate_card', array('_locale' => $request->getLocale(), '_country' => $request->get('_country')));
                    } else {
                        //echo "inside else";
                    }
                }
                else
                {
                    $error['success'] = false;
                    $filename     = $commFunct->saveTextAsImage();
                    $commFunct->setCsrfToken('front_activation_step1');
                    $session = new Session();
                    $token   = $session->get('front_activation_step1');
                    $error['message'] = $this->get('translator')->trans('Unable to process your request at this time.Please try later');
                    $file_name        = $response->setContent($filename['filename']);
                    $captcha_image    = $filename['image_captcha'];
                    return $this->render('/activation/activation.twig',
                        array(
                            'form'  => $form->createView(),
                            'error' => $error,
                            'token' => $token,
                            'data'  => $captcha_image ,
                        )
                    );
                }
            }
            catch (\Exception $e)
            {
                $error['success'] = false;
                $error['message'] = $e->getMessage();
                $filename     = $commFunct->saveTextAsImage();

                $commFunct->setCsrfToken('front_activation_step1');
                $session = new Session();
                $token   = $session->get('front_activation_step1');

                $file_name    = $response->setContent($filename['filename']);
                $captcha_image = $filename['image_captcha'];
                return $this->render('/activation/activation.twig',
                    array(
                        'form' => $form->createView(),
                        'error' => $error,
                        'token' => $token,
                        'data' => $captcha_image,
                    )
                );
            }
        }
        else
        {
            $commFunct->setCsrfToken('front_activation_step1');
            $session = new Session();
            $token   = $session->get('front_activation_step1');

            $filename     = $commFunct->saveTextAsImage();
            $response->setContent($filename['filename']);
            $error['success'] = true;
            $error['message'] = "";
            $captcha_image = $filename['image_captcha'];
            return $this->render('/activation/activation.twig',
                array(
                    'form'  => $form->createView(),
                    'error' => $error,
                    'token' => $token,
                    'data'  => $captcha_image ,
                )
            );
        }
    }




    public function checkMob($mobile)
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $em          = $this->getDoctrine()->getManager();
        $mobile = ($request->get('_country') == 'sa' ? "0" : "002") . $mobile;
        // First step to check the  email in mysql db
        // $mobile      = '05083847000';
        $country_id  = $request->get('_country');
        $restClient  = $this->get('app.rest_client')->IsAdmin(true);

        //print_r($form_param);
        /************************/
        //echo $url  = $request->getLocale() . '/api/create_offlineuser.json';
        $url  = $request->getLocale().'/api/'.$mobile ;
        if($mobile != "" )
        {
            // check_mobile_registration
            $url  = $request->getLocale() . '/api/'.$mobile.'/check_mobile_registration.json';
            $data = $restClient->restGet(AppConstant::WEBAPI_URL . $url, array('Country-Id' => strtoupper($request->get('_country'))));
            if($data['status'] == 1) {
                if ($data['success'] == false) {
                    // false means we cannot process to register
                    Throw new Exception($this->get('translator')->trans('The entered mobile number is already used for 5 cards. you can not use the same mobile number more than 5 times'), 100);
                }
            }
            else
            {
                Throw New Exception($this->get('translator')->trans('An invalid exception occurred'));
            }
        }
    }

    /**
     * @param Request $request
     * @Route("/{_country}/{_locale}/checkBlacklisted", name="checkBlacklisted")
     * @return \Symfony\Component\HttpFoundation\Response
     */

    public function checkBlacklistedAction($iktissab)
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $em          = $this->getDoctrine()->getManager();
        $iktissab = $iktissab;
        // First step to check the  email in mysql db
        // $mobile      = '05083847000';
        $country_id  = $request->get('_country');
        $restClient  = $this->get('app.rest_client')->IsAdmin(true);

        //print_r($form_param);
        /************************/
        //echo $url  = $request->getLocale() . '/api/create_offlineuser.json';
        $url  = $request->getLocale().'/api/'.$iktissab ;

        if($iktissab != "" )
        {
            $url  = $request->getLocale() . '/api/'.$iktissab.'/blacklisted_user.json';
            $data = $restClient->restGet(AppConstant::WEBAPI_URL . $url, array('Country-Id' => strtoupper($request->get('_country'))));
            // print_r($data);
            if($data['status'] == 1) {
                //print_r($data['success'] == false);
                if ($data['success'] == false) {
                    // false means we cannot process to register
                    Throw new Exception($this->get('translator')->trans('The entered mobile number is already used for 5 cards. you can not use the same mobile number more than 5 times'), 100);
                }
                else
                {
                    Throw new Exception($this->get('translator')->trans('The entered mobile number is already used for 5 cards. you can not use the same mobile number more than 5 times'), 100);

                }
            }
            else
            {
                Throw New Exception($this->get('translator')->trans('An invalid exception occurred'));
            }
        }
    }







    public function checkOnline($pData)
    {
        $em = $this->getDoctrine()->getManager();
        // First step to check the  email in mysql db
        $checkEmail = $em->getRepository('AppBundle:User')->findOneByEmail(strip_tags(strtolower($pData['email'])));
        if (!is_null($checkEmail)) {
            Throw new Exception($this->get('translator')->trans('The new email is already registered before , please enter a valid email'), 100);
        }
        // Second step to check the  ikt card in mysql db
        $checkIktCard = $em->getRepository('AppBundle:User')->find(strip_tags($pData['iktCardNo']));

        if (!is_null($checkIktCard)) {
            Throw new Exception($this->get('translator')->trans('This Card is already registered'), 1);
        }
    }

    /**
     * @param $iktCardNo
     * @return bool|string
     */

    public function checkScenerio($iktCardNo)
    {
            try
            {
              // f31e315baf4654ff9a5a1cd0a31d17de
            $restClient = $this->get('app.rest_client')->IsAdmin(true);
            $request = $this->container->get('request_stack')->getCurrentRequest();
            $locale  = $request->getLocale();
            $url    = $request->getLocale() . '/api/' . $iktCardNo . '/card_status.json';
            $data = $restClient->restGet(AppConstant::WEBAPI_URL . $url, array('Country-Id' => strtoupper($request->get('_country'))));

                if ($data['success'] != true) {
                    Throw new Exception($this->get('translator')->trans('Invalid Iktissab Card Number'), 1);
                }
                else
                {
                    if ($data['data']['cust_status'] == 'Active' || $data['data']['cust_status'] == 'In-Active')
                    {
                        return AppConstant::IKT_REG_SCENERIO_2;
                    }
                    elseif ($data['data']['cust_status'] == 'NEW' || $data['data']['cust_status'] == 'Distributed')
                    {
                        return AppConstant::IKT_REG_SCENERIO_1;
                    }
                }
            }
            catch(\Exception $e)
            {
                Throw new Exception($this->get('translator')->trans('Unable to process your request at this time.Please try later'), 1);
            }
        return true;
    }

    /**
     * @Route("/{_country}/{_locale}/activate-card", name="activate_card")
     */
    public function activateCardAction(Request $request)
    {
        $response = new Response();
        // check referal
        // to do: uncomment below

        if (!$this->isReferalValid('card_activation')) {
         //return $this->redirectToRoute('front_card_activation',array('_locale'=> $request->getLocale(),'_country' => $request->get('_country')));
        }
        /***************/
        $commFunct = new FunctionsController();
        $commFunct->setContainer($this->container);
        if ($commFunct->checkSessionCookies($request) == false) {
            return $this->redirect($this->generateUrl('landingpage'));
        }
        /***************/
        $restClient = $this->get('app.rest_client')->IsAdmin(true);
        $smsService = $this->get('app.sms_service');
        // get existing user data
        try
        {
            $this->get('session')->get('iktCardNo');
            $url = $request->getLocale() . '/api/' . $this->get('session')->get('iktCardNo') . '/userdata.json';
            // AppConstant::WEBAPI_URL.$url;

            $data = $restClient->restGet(AppConstant::WEBAPI_URL . $url, array('Country-Id' => strtoupper($request->get('_country'))));
            if ($data['success'] == "true") {
                $this->get('session')->set('iktUserDataACC', $data['user']);
            }
            // print_r($data['user']);

            $error = array('success' => true);
            $fData = array('iktCardNo' => $this->get('session')->get('iktCardNo'), 'email' => $this->get('session')->get('email'));
            $form = $this->createForm(IktCustomerInfo::class, $fData);
            $form->handleRequest($request);
            $pData = $form->getData();
            if ($form->isSubmitted() && $form->isValid()) {

                $this->get('session')->set('pass', md5($pData['password']));
                $this->get('session')->set('password_unmd5', $pData['password']);
                $this->get('session')->set('mobile',$data['user'][1] );

                $otp = rand(111111, 999999);
                $this->get('session')->set('otp', $otp);
                if ($request->getLocale() == 'ar') {
                    $message = $this->get('translator')->trans("الرجاء إدخال الرمز المؤقت التالي للاستمرار بعملية التسجيل في موقع اكتساب:" . $otp);
                }
                else
                {
                    $message = $this->get('translator')->trans("Please insert this temporary code to continue with Iktissab Card registration:" . $otp);
                }
                $acrivityLog = $this->get('app.activity_log');
                // send sms code
                // echo $data['user'][1];
                // echo '<br>';
                // echo $data['user'][1]; exit;
                $request->get('_country');
                $sms_sended = $smsService->sendSms($data['user'][1], $message, $request->get('_country'));
                if ($sms_sended == '1')
                {
                    $acrivityLog->logEvent(AppConstant::ACTIVITY_SEND_SMS, $data['user']['C_id'],
                        array('message' => $message, 'session' => $data['user']),null, null);
                    $request->getSession()
                        ->getFlashBag()
                        ->add('smsSuccess', 'One time password has been sent to your mobile, please enter to continue!');
                    return $this->redirectToRoute('enter_otp', array('_locale' => $request->getLocale(), '_country' => $request->get('_country')));
                }
                else
                {
                    $acrivityLog->logEvent(AppConstant::ACTIVITY_SEND_SMS_FAILED, $data['user']['C_id'],
                        array('message' => $message, 'session' => $data['user']), null, null);

                    $error = array('success' => false, 'message' => $this->get('translator')->trans('SMS not sent.Please try again'));
                }


            }


        }
        catch (\Exception $e)
        {

                $error = array('success' => false , 'message' => $this->get('translator')->trans('SMS not sent.Please try again'));
                //return $this->redirectToRoute('front_card_activation', array('_locale' => $request->getLocale(), '_country' => $request->get('_country'), 'q' => '1'));

        }

        return $this->render('/activation/customer_information_sc2.twig',
            array(
                'form' => $form->createView(),
                'error' => $error,
            )
        );


    }

    function isReferalValid($url)
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $referer = $request->headers->get('referer');
        $baseUrl = $request->getBaseUrl();
        $lastPath = substr($referer, strpos($referer, $baseUrl) + strlen($baseUrl));
        $matcher = $this->get('router')->getMatcher();
        $parameters = $matcher->match($lastPath);
        //var_dump($request);
        //echo "<br />base url = ".$baseUrl;
        //echo "<br /> Last path = ".$lastPath;
        //var_dump($parameters);
        //die("---");
        if ($parameters['_route'] != $url) {
            return false;
        }
        return true;

    }

    /**
     * @Route("/{_country}/{_locale}/customer-information", name="customer_information")
     *
     */
    public function customerInformationAction(Request $request)
    {
        // check referal
        if (!$this->isReferalValid('card_activation')) {
            //return $this->redirectToRoute('front_card_activation',array('_locale'=> $request->getLocale(),'_country' => $request->get('_country')));
        }
        if($this->get('session')->get('iktCardNo') == "" || $this->get('session')->get('iktCardNo') == null){
            return $this->redirectToRoute('front_card_activation',array('_locale'=> $request->getLocale(),'_country' => $request->get('_country')));
        }
        /***************/
        $commFunct = new FunctionsController();
        $commFunct->setContainer($this->container);
        if ($commFunct->checkSessionCookies($request) == false) {
            return $this->redirect($this->generateUrl('landingpage'));
        }
        /***********/
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
        /***************/
        // get all cities
        $restClient = $this->get('app.rest_client');
        $smsService = $this->get('app.sms_service');
        $url = $request->getLocale() . '/api/cities_areas_and_jobs.json';
        $cities_jobs_area = $restClient->restGet(AppConstant::WEBAPI_URL . $url, array('Country-Id' => strtoupper($request->get('_country'))));
        // var_dump($cities_jobs_area); die('---');
        $cities = $cities_jobs_area['cities'];
        $citiesArranged = array();
        foreach ($cities as $key => $value) {
            $citiesArranged[$value['name']] = $value['city_no'];
        }
        $jobs = $cities_jobs_area['jobs'];
        $jobsArranged = array();
        foreach ($jobs as $key => $value) {
            $jobsArranged[$value['name']] = $value['job_no'];
        }
        $areas = $cities_jobs_area['areas'];

        $areasArranged = array();
        foreach ($areas as $key => $value) {
            if(!isset($value['name'])){
                continue;
            }
            $areasArranged[$value['name']] = $value['area_code'];
        }
        // print_r($areasArranged);
        $areasArranged['-1'] = '-1';
        $areas = $this->json($cities_jobs_area['areas']);

        $pData = array('iktCardNo' => $this->get('session')->get('iktCardNo'), 'email' => $this->get('session')->get('email'));

        $form = $this->createForm(IktRegType::class, $pData, array(
                    'additional' => array(
                    'locale'     => $request->getLocale(),
                    'country'    => $request->get('_country'),
                    'cities'     => $citiesArranged,
                    'jobs'       => $jobsArranged,
                    'areas'      => $areasArranged,
                )
            )
        );
        $form->handleRequest($request);
        $data_submitted = $request->request->all();
        $pData = $form->getData();

        $error = array('success' => true);
//        dump($_POST); die('--');
        if ($form->isValid() && $form->isSubmitted())
        {
            if($commFunct->checkCsrfToken($form->get('token')->getData(), 'front_activation_step2' )) {

                $errors_validity = "";
                $data_submitted['ikt_reg']['area_no'];
                $area_valid = ($pData['area_no'] == '-1') ? $pData['area_text'] : $data_submitted['ikt_reg']['area_no'];

                //$area_valid = ($pData['area_no'] == '-1') ? $pData['area_text'] : $pData['area_no'];

                $validate_data = array($pData['iktCardNo'],  $area_valid,
                    $pData['mobile'],
                    $pData['city_no'], $pData['email'], $pData['maritial_status'], $pData['iqama'], $pData['job_no'],
                    $pData['gender'], $pData['pur_group'],
                );

                $validate_dataName = array( $pData['fullName'] );


                try
                {
                    if (strip_tags(trim($pData['fullName'])) != "")
                    {
                        $fullname_arr = explode(' ', strip_tags(trim($pData['fullName'])));
                        if (count($fullname_arr) < 2)
                        {
                            $error['success'] = false;
                            $error['message'] = $this->get('translator')->trans('Name must be in two parts');
                            Throw new Exception($this->get('translator')->trans('Name must be in two parts'));
                        }
                    }

                    $errors_validity = $commFunct->validateData($validate_data);
                    if ($errors_validity > 0) {
                        $error['success'] = false;
                        $error['message'] = $this->get('translator')->trans('Please provide valid data');
                        Throw new Exception($this->get('translator')->trans('Please provide valid data'));

                    }
                    // FOR NAME VALIDATION REMOVING SPECIAL CHARACTERS
                    $errors_validity = $commFunct->validateSpecialCharacters($validate_dataName);
                    if ($errors_validity > 0) {
                        $error['success'] = false;
                        $error['message'] = $this->get('translator')->trans('Please provide valid data for name');
                        Throw new Exception($this->get('translator')->trans('Please provide valid data for name'));
                    }
                    $errors_validity = 0;
                    $errors_validity = $commFunct->validateDataName($validate_dataName);
                    if ($errors_validity > 0) {
                        $error['success'] = false;
                        $error['message'] = $this->get('translator')->trans('Please provide valid data for name');
                        Throw new Exception($this->get('translator')->trans('Please provide valid data for name'));
                    }


                    $area = ($pData['area_no'] == '-1') ? $pData['area_text'] : $data_submitted['ikt_reg']['area_no'];
                    // commented by sohail
                    // $area = ($pData['area_no'] == '-1') ? $pData['area_text'] : $pData['area_no'];

                    // check iqama validity
                    if ($request->get('_country') == 'sa') {
                        $this->validateIqamaNationality($pData['iqama'], $pData['nationality']->getId());
                        $this->validateIqama($pData['iqama']);
                    }
                    // check iqama in local SQl db
                    $this->checkIqamaLocal($pData['iqama']);

                    $this->checkMob($pData['mobile']);


                    if ($pData['date_type'] == 'h') {
                        $dob = $pData['dob_h']->format('Y-m-d h:i:s');
                    } else {
                        $dob = $pData['dob']->format('Y-m-d h:i:s');
                    }
                    // save the provided data to session
                    $newCustomer  = array(
                        "C_id"    => $pData['iktCardNo'],
                        "cname"   => $pData['fullName'],
                        "area"    => $area,
                        "city_no" => $pData['city_no'],
                        "mobile"  => ($request->get('_country') == 'sa' ? "0" : "002") . $pData['mobile'],
                        "email"   => strtolower($pData['email']),
                        "nat_no"  => $pData['nationality']->getId(),
                        "Marital_status" => $pData['maritial_status'],
                        "ID_no"   => $pData['iqama'],
                        "job_no"    => $pData['job_no'],
                        "gender"    => $pData['gender'],
                        "pur_grp"   => $pData['pur_group'],
                        "birthdate" => $dob,
                        "pincode"   => mt_rand(1000, 9999),
                        "source"    => User::ACTIVATION_SOURCE_WEB
                    );

                    if ($request->get('_country') == 'eg') {
                        $mob_with_country_code = "002" . $pData['mobile'];
                    } else {
                        // here no country code
                        $mob_with_country_code = $pData['mobile'];
                    }
                    $this->get('session')->set('new_customer', $newCustomer);
                    $this->get('session')->set('password_unmd5', $pData['password']);
                    $this->get('session')->set('pass', md5($pData['password']));
                    $this->get('session')->set('mobile', $mob_with_country_code);

                    $otp = rand(111111, 999999);
                    $this->get('session')->set('otp', $otp);
                    if ($request->getLocale() == 'ar') {
                        $message = $this->get('translator')->trans("الرجاء إدخال الرمز المؤقت التالي للاستمرار بعملية التسجيل في موقع اكتساب:" . $otp);
                    } else {
                        $message = $this->get('translator')->trans("Please insert this temporary code to continue with Iktissab Card registration:" . $otp);
                    }
                    $acrivityLog = $this->get('app.activity_log');
                    //send sms code

                    $sms_sent = $smsService->sendSms($pData['mobile'], $message, $request->get('_country'));
                    if ($sms_sent == true) {
                        $acrivityLog->logEvent(AppConstant::ACTIVITY_SEND_SMS, $newCustomer['C_id'],
                            array('message' => $message, 'session' => $newCustomer), null, null);
                        // one status code mobily that sms sent sucessfully
                        $request->getSession()
                            ->getFlashBag()
                            ->add('smsSuccess', 'One time password has been sent to your mobile, please enter to continue!');
                        return $this->redirectToRoute('enter_otp', array('_locale' => $request->getLocale(), '_country' => $request->get('_country')));
                    } else {
                        $acrivityLog->logEvent(AppConstant::ACTIVITY_SEND_SMS_FAILED, $newCustomer['C_id'],
                            array('message' => $message, 'session' => $newCustomer), null, null);
                        $error['success'] = false;
                        $error['message'] = $this->get('translator')->trans('SMS not sent.Please try again');
                    }
                } catch (\Exception $e) {
                    $error['success'] = false;
                    $error['message'] = $e->getMessage();
                }
            }
            else
            {
                $error['success'] = false;
                $error['message'] = $this->get('translator')->trans('Unable to process your request at this time.Please try later');
            }
        }
        $reference_year = array('gyear'=>2017, 'hyear'=>1438);
        $current_year   = date('Y');
        $islamicYear    = ($current_year - $reference_year['gyear']) + $reference_year['hyear'];
        $iktCardNo      = $this->get('session')->get('iktCardNo');

        $commFunct->setCsrfToken('front_activation_step2');
        $session = new Session();
        $token   = $session->get('front_activation_step2');

        return $this->render('/activation/customer_information_'.$country.'.twig',
            array(
                'form'          => $form->createView(),
                'error'         => $error,
                'token'         => $token,
                'areas'         => $areas,
                'islamicyear'   => $islamicYear,
                'iktCardNo'     => $iktCardNo
        ));


    }

    function checkIqamaLocal($iqama)
    { // iqama validation in local MSSQL db
        $restClient = $this->get('app.rest_client')->IsAdmin(true);
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $url = $request->getLocale() . '/api/' . $iqama . '/is_ssn_used.json';

        $data = $restClient->restGet(AppConstant::WEBAPI_URL . $url, array('Country-Id' => strtoupper($request->get('_country'))));
        // added by sohail  $data['status'] ifelse
        // print_r($data);exit;
        if ($data['status'] == 1)
        {
            if ($data['success'] == false) { // this iqama is not registered previously
                return true;
            } else {
                Throw new Exception($this->get('translator')->trans($data['message']), 1);
            }
        }
        else{
            Throw new Exception($this->get('translator')->trans($data['message']), 1);
        }
    }

    /**
     * @param Request $request
     * @Route("/{_country}/{_locale}/enter_otp", name="enter_otp")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function enterOtpAction(Request $request)
    {
        // added by sohail
        if($this->get('session')->get('otp','') == "" )
        {
            return $this->redirect($this->generateUrl('login', array('_country' => $request->cookies->get(AppConstant::COOKIE_COUNTRY), '_locale' => $request->cookies->get(AppConstant::COOKIE_LOCALE))));
        }

        if($this->get('session')->get('optcode_counter','') === '')
        {
            $this->get('session')->set('optcode_counter', 10);
            $optcode_counter = 10;
        }
        else
        {
            $optcode_counter = (integer)$this->get('session')->get('optcode_counter');
        }
        // end added by sohail


        $activityLog = $this->get('app.activity_log');
        /***************/
        $commFunct = new FunctionsController();
        $commFunct->setContainer($this->container);
        if ($commFunct->checkSessionCookies($request) == false) {
            return $this->redirect($this->generateUrl('landingpage'));
        }
        /***************/
        echo $this->get('session')->get('otp');
        if ($this->get('session')->get('otp') == "" || $this->get('session')->get('otp') == null) {
            return $this->redirect($this->generateUrl('landingpage'));
        }
        $error = array('success' => true);
        $form = $this->createForm(EnterOtpType::class);
        $form->handleRequest($request);
        $restClient = $this->get('app.rest_client')->IsAdmin(true);
        $smsService = $this->get('app.sms_service');
        //echo $this->get('session')->get('otp');
        $scenerio = $this->get('session')->get('scenerio');

        // added by sohail
        if($optcode_counter == 0){
            $this->get('session')->set('optcode_counter', '');
            $this->get('session')->set('otp','');
            return $this->redirect($this->generateUrl('login', array('_country' => $request->cookies->get(AppConstant::COOKIE_COUNTRY), '_locale' => $request->cookies->get(AppConstant::COOKIE_LOCALE))));
        }

        //print_r($this->get('session')->get('new_customer'));
        // end code added by sohail


        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            switch ($scenerio) {
                case AppConstant::IKT_REG_SCENERIO_2:
                    $em = $this->getDoctrine()->getManager();
                    if ($form->getData()['otp'] != $this->get('session')->get('otp')) {
                        // added by sohail
                        $optcode_counter--;
                        $form->get('otp')->addError(new FormError($this->get('translator')->trans('Please enter correct verification code')));
                    } else {

                        //try catch added by sohail
                        try
                        {
                            $user = new User();
                            $user->setEmail($this->get('session')->get('email'));
                            $user->setIktCardNo($this->get('session')->get('iktCardNo'));
                            $user->setRegDate(time());
                            $user->setPassword($this->get('session')->get('pass'));
                            $user->setStatus('1');
                            $user->setActivationSource(User::ACTIVATION_SOURCE_CALL_CENTER);
                            $user->setCountry($request->get('_country'));
                            $em->persist($user);
                            $em->flush();
                            $request->getSession()
                                ->getFlashBag()
                                ->add('ikt_success', $this->get('translator')->trans('Dear customer, your account has been created you can login now'));
                            if ($request->getLocale() == 'ar') {
                                $message = $this->get('translator')->trans('اهلاً بك في موقع اكتساب. اسم المستخدم الخاص بك:' . $this->get('session')->get('email') . ' و كلمة المرور :' . $this->get('session')->get('password_unmd5'));
                            } else {
                                $message = $this->get('translator')->trans('Welcome to iktissab website your Username: ' . $this->get('session')->get('email') . ' and Password: ' . $this->get('session')->get('password_unmd5'));
                            }
                            $sms_sended = $smsService->sendSms($this->get('session')->get('mobile'), $message, $request->get('_country'));
                            if($sms_sended == true) {
                                // sohail code if send sms is successfull then create the
                                // same account in offline db for accessing services.
                                $form_data = array(
                                    'email'     => $this->get('session')->get('email'),
                                    'password'  => $this->get('session')->get('pass'),
                                    'country'   => $request->get('_country'),
                                    'status'    => 1,
                                    'ActivationSource' => 'W',
                                    'C_id'      => $this->get('session')->get('iktCardNo')
                                );

                                $this->createTempUser($request, $form_data);

                                $ikt_sec2_data = $this->get('session')->get('iktUserDataACC');
                                $activityLog->logEvent(AppConstant::ACTIVITY_EXISTING_CARD_REGISTRATION_SUCCESS,
                                    $this->get('session')->get('iktCardNo'),
                                    array('message' => $message, 'session' => json_encode($this->get('session')->get('iktUserDataACC')))
                                , null, null);
                                $message = \Swift_Message::newInstance();
                                $message->addTo($this->get('session')->get('email'), $this->get('session')->get('email'))

                                    ->addFrom($this->getParameter('mailer_user'))
                                    ->setSubject(AppConstant::EMAIL_SUBJECT)
                                    ->setBody(
                                        $this->renderView(':email-templates/customers:new-account-creation.html.twig', ['customer' => $ikt_sec2_data['cname'], 'email' => $this->get('session')->get('email'),
                                            'password' => $this->get('session')->get('password_unmd5')
                                        ]),
                                        'text/html'
                                    );
                                $this->get('mailer')->send($message);
                                $this->container->get('mailer')->send($message);

                                return $this->redirectToRoute('activation_thanks', array('_locale' => $request->getLocale(), '_country' => $request->get('_country')));
                            }
                            else {
                                $error['success'] = false;
                                $error['message'] = $this->get('translator')->trans('SMS not sent.Please try again');
                            }
                        }
                        catch(\Exception $e)
                        {
                            $activityLog->logEvent(AppConstant::ACTIVITY_NEW_CARD_REGISTRATION_ERROR, 1,
                                array('iktissab_card_no' =>  $this->get('session')->get('name'), 'message' => $e->getMessage(),
                                    'session' => json_encode($this->get('session')->get('iktUserDataACC'))), null , null);
                            $error['success'] = false;
                            $error['message'] = $e->getMessage();
                        }
                    }
                    break;
                case AppConstant::IKT_REG_SCENERIO_1;


                    if ($form->getData()['otp'] != $this->get('session')->get('otp'))
                    {
                        // added by sohail
                        $optcode_counter--;
                        $form->get('otp')->addError(new FormError($this->get('translator')->trans('Please enter correct verification code')));
                    }
                    else
                    {
                        $newCustomer = $this->get('session')->get('new_customer');
                        try
                        {
                            $this->checckBeforeAdd($newCustomer['C_id']);
                            $this->checkIqamaLocal($newCustomer['ID_no']);
                            $this->add();
                            if($request->getLocale() == 'ar')
                            {
                                $message = $this->get('translator')->trans('اهلاً بك في موقع اكتساب. اسم المستخدم الخاص بك:' . $this->get('session')->get('email').' و كلمة المرور :'.$this->get('session')->get('password_unmd5'));
                            }
                            else
                            {
                                $message = $this->get('translator')->trans('Welcome to iktissab website your Username:' . $this->get('session')->get('email') . ' and Password: '.$this->get('session')->get('password_unmd5') );
                            }
                            $sms_sended = $smsService->sendSms($this->get('session')->get('mobile'), $message, $request->get('_country'));
                            if($sms_sended == true) {
                                $message = \Swift_Message::newInstance();
                                $message->addTo($newCustomer['email'], $newCustomer['cname'])
                                ->addFrom($this->getParameter('mailer_user'))
                                ->setSubject(AppConstant::EMAIL_SUBJECT)
                                ->setBody(
                                    $this->renderView(':email-templates/customers:new-account-creation.html.twig', ['customer' => $newCustomer['cname'], 'email' => $this->get('session')->get('email'), 'password' => $this->get('session')->get('password_unmd5')]),
                                    'text/html'
                                );

                                $this->get('mailer')->send($message);


                                $request->getSession()
                                    ->getFlashBag()
                                    ->add('ikt_success', $this->get('translator')->trans('Dear customer, your account information has been sent to us successfully. the Iktissab Team will send you the security code and the activation number to your registered mobile number, also we have sent you an email containing a link to confirm & activate your Iktissab Account'));
                                $message_log = 'Dear customer, your account information has been sent to us successfully. the Iktissab Team will send you the security code and the activation number to your registered mobile number, also we have sent you an email containing a link to confirm & activate your Iktissab Account';
                                $activityLog->logEvent(AppConstant::ACTIVITY_NEW_CARD_REGISTRATION_SUCCESS, $newCustomer['C_id'],
                                    array('message' => $message_log, 'session' => $newCustomer), null, null);
                                return $this->redirectToRoute('activation_thanks', array('_locale' => $request->getLocale(), '_country' => $request->get('_country')));
                            }
                            else
                            {
                                $error['success'] = false;
                                $error['message'] = $this->get('translator')->trans('SMS not sent.Please try again');
                            }
                        } catch (Exception $e) {
                            $activityLog->logEvent(AppConstant::ACTIVITY_NEW_CARD_REGISTRATION_ERROR, 1,
                                array('iktissab_card_no' => $newCustomer['C_id'], 'message' => $e->getMessage(), 'session' => $newCustomer), null, null);
                            $error['success'] = false;
                            $error['message'] = $e->getMessage();
                        }
                    }
                    break;
            }
        }
        // added by sohail
        $ikt_card_no    = $this->get('session')->get('iktCardNo');//$this->get('session')->get('iktCardNo');
        $ikt_reg_mobile = substr($this->get('session')->get('mobile'),4,9);//$this->get('session')->get('mobile');
        // added by sohail
        $this->get('session')->set('optcode_counter', $optcode_counter);

        return $this->render('/activation/enter_otp.twig',
            array(
                'error' => $error,
                'form'  => $form->createView(),
                'ikt_card_no' => $ikt_card_no,
                'ikt_reg_mobile' => $ikt_reg_mobile
            )
        );
    }

    function checckBeforeAdd($iktCardNo)
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $restClient = $this->get('app.rest_client')->IsAdmin(true);
        $url = $request->getLocale() . '/api/' . $iktCardNo . '/card_status.json';
        $data = $restClient->restGet(AppConstant::WEBAPI_URL . $url, array('Country-Id' => strtoupper($request->get('_country'))));
        if ($data['data']['cust_status'] == 'NEW' || $data['data']['cust_status'] == 'Distributed') {
            // proceed
        } else {
            Throw New Exception($this->get('translator')->trans('Error in adding new card'));
        }
        $this->checkInStagging($iktCardNo);
    }

    function checkInStagging($iktCardNo)
    {
        // $request = Request::createFromGlobals();
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $restClient = $this->get('app.rest_client')->IsAdmin(true);
        $url = $request->getLocale() . '/api/' . $iktCardNo . '/is_in_stagging.json';
        $data = $restClient->restGet(AppConstant::WEBAPI_URL . $url, array('Country-Id' => strtoupper($request->get('_country'))));
        // print_r($data);exit;
        // $data['status'] == true indicates that
        // from the webservice return response is 200

        // ifelse added by sohail
        if($data['status'] == 1) {
            if ($data['success'] == true) // this card is in stagging so dont add again
            {
                Throw New Exception($this->get('translator')->trans($data['message']));
            }

        } else {
            Throw New Exception($this->get('translator')->trans('An invalid exception occurred'));
        }
        
    }

    function add()
    {
        $error = array('status' => false, 'message' => '');
        $request = $this->container->get('request_stack')->getCurrentRequest();
        // $em = $this->getDoctrine()->getEntityManager();
        $em = $this->getDoctrine()->getManager();
        $newCustomer = $this->get('session')->get('new_customer');
        $url = $request->getLocale() . '/api/add_new_user.json';
        $restClient = $this->get('app.rest_client')->IsAdmin(true);
        $cData = json_encode($newCustomer);
        try {
            $this->checkOnline(array('iktCardNo' => $newCustomer['C_id'], 'email' => $newCustomer['email']));
            $saveCustomer = $restClient->restPost(AppConstant::WEBAPI_URL . $url, $cData, array('Country-Id' => strtoupper($request->get('_country'))));
            // var_dump($cData);
            // var_dump($url);
            // var_dump($saveCustomer);
            // die('----');

            /*if ($saveCustomer!= true) {
                Throw New Exception($this->get('translator')->trans($saveCustomer['message']));
            }*/

            if($saveCustomer['status'] == 0){
                Throw New Exception($this->get('translator')->trans($saveCustomer['message']));
                // incase the validation is bypassed in the form
                //Throw New Exception($this->get('translator')->trans('Unable to process your request.Please try later'));
            }
            if ($saveCustomer['success'] != true) {
                Throw New Exception($this->get('translator')->trans($saveCustomer['message']));
            }

            $user = new User();
            $user->setEmail($newCustomer['email']);
            $user->setIktCardNo($newCustomer['C_id']);
            $user->setRegDate(time());
            $user->setStatus(0);
            $user->setCountry($request->get('_country'));
            $user->setPassword($this->get('session')->get('pass'));
            $user->setActivationSource(User::ACTIVATION_SOURCE_WEB);
            $em->persist($user);
            $em->flush();

        } catch (Exception $e) {
            Throw New Exception($e->getMessage());

        }
    }

    /**
     * @Route("/{_country}/{_locale}/activation/thanks", name="activation_thanks")
     */
    function activationThanksAction(Request $request)
    {
        /***************/
        $commFunct = new FunctionsController();
        if ($commFunct->checkSessionCookies($request) == false) {
            return $this->redirect($this->generateUrl('landingpage'));
        }
        /***************/
        $success = $request->getSession()->getFlashBag()->get('ikt_success');
        // code added by sohail
        $ikt_card_no    = $this->get('session')->get('iktCardNo');
        $session = $request->getSession();
        $session->invalidate();
        // code added by sohail
        return $this->render('/activation/thanks.twig', [
            'success'     => $success,
            'ikt_card_no' => $ikt_card_no,

        ]);
    }

    /**
     * @Route("/checkiqama")
     */
    function checkiqamaAction()
    {                       // function to validate iqama this code will be implemented in the FormType as callback validation
        $this->checkIqamaLocal('2374777710');
        $iqama = '2309121604';
        $iqama = '1001744588';
        $evenSum = 0;
        $oddSum = 0;
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
        echo "entire sum is" . $entireSum;
        echo $entireSum % 10;
        if (($entireSum % 10) == 0) {
            echo "Iqama is valid";
        } else {
            echo "invalid iqama";
        }


        die('---');
    }


    /**
     * @Route("/{_country}/{_locale}/testsms", name="test_sms")
     */
    function testsmsAction()
    {


        // $mail = $this->get('mailer')->send($message);

        // test via sms as service
        // dump($mail);
        // dump($message);
//        $smsService = $this->get('app.sms_service');
//        $smsService->sendSms("569858396", 7777, 'sa');
        //return new Response();
        //die('----');
        $message = $this->get('translator')->trans("Please insert this tem");

        // this method will be used from sms service now
        $restClient = $this->get('app.rest_client');
        $payload = "mobile=Othaim&password=0557554443&numbers=966583847092&sender=Iktissab&msg=".$message."&timeSend=0&dateSend=0&applicationType=68&domainName=othaimmarkets.com&msgId=3813&deleteKey=101115&lang=3";
        $url = 'http://www.mobily.ws/api/msgSend.php?' . $payload;
        echo "<br /> payload == " . $payload;
        $sms = $restClient->restGet($url, array());
        //var_dump($sms);
    }

    /**
     * @Route("/{_country}/{_locale}/testsms2", name="test_sms2")
     */
    public function testsms2Action()
    {
        $smsService = $this->get('app.sms_service');
        $receiver = '583847092';
        $message = $this->get('translator')->trans("Please insert this temporary code %s , to continue with Iktissab Card registration.", ["%s"=>'454545']);
        $country = 'sa';

        if ($country == 'eg')
        {
            $mobilyUser = $this->params['mobily_user_eg'];
            $mobilyPass = $this->params['mobily_pass_eg'];
            // for EG we store the
            $countryPrefix = '';  //AppConstant::IKT_EG_PREFIX;
            $mobilySender = $this->params['mobily_sender_eg'];
        }
        else
        {
            $mobilyUser = $this->params['mobily_user'];
            $mobilyPass = $this->params['mobily_pass'];
            $countryPrefix = AppConstant::IKT_SA_PREFIX;
            $mobilySender = $this->params['mobily_sender'];
            // format number
            if(strlen($receiver) == 10)
                $receiver = substr($receiver,1 ,strlen($receiver)-1);
        }
        $msgID  = rand(1, 9999);
        $delKey = rand(1, 9999);
        //$messageFormatted = urlencode(iconv("UTF-8", "windows-1256", $message));
        $messageFormatted = $message;

        $payload = "mobile=" . $mobilyUser . "&password=" . $mobilyPass . "&numbers=" . $countryPrefix . $receiver . "&sender=" . $mobilySender . "&msg=" . $messageFormatted . "&timeSend=0&dateSend=0&applicationType=" . $this->params['mobily_app_type'] . "&domainName=" . $this->params['mobily_app_type'] . "&msgId=" . $msgID . "&deleteKey=" . $delKey . "&lang=3";
        $url = 'http://www.mobily.ws/api/msgSend.php?' . $payload;
        //echo $url;
        $sms = $this->restClient->restGet($url, array());
//        $sms = 1;
        // var_dump($sms);
//        die('---');
        if ($sms == '1') {
            return true;
        }

    }


    /**
     * @param Request $request
     * @Route("/{_country}/{_locale}/testi", name="testi")
     *
     */
    public function testAction(Request $request)
    {
        // print_r($conn);
        $country_id  = $request->get('_country');
        $form_data   =  array(
            'email'     => 'sa.aspire55@gmail.com',
            'password'  => '31528198109743225ff9d0cf04d1fdd1',
            'country'   => $request->get('_country') ,
            'status'    => 1,
            'ActivationSource' => 'w',
            'C_id'      => '98239094'
        );
        $this->createTempUser($request , $form_data);

    }

    /**
     * @param Request $request
     * @Route("/{_country}/{_locale}/testi2", name="testi2")
     *
     */
    public function testi2Action(Request $request)
    {
        $smsService = $this->get('app.sms_service');
        //$message = $this->get('translator')->trans('Creation Date');

        $message = $this->get('translator')->trans('Username');
        $acrivityLog = $this->get('app.activity_log');
        //send sms code
        echo $smsService->sendSms('0583847092', $message, 'sa');
        die('----');
    }






    /**
     * @param Request $request
     *
     */
    public function createTempUser(Request $request , $form_param)
    {
        try
        {
            $country_id  = $request->get('_country');
            $restClient  = $this->get('app.rest_client')->IsAdmin(true);
            //print_r($form_param);
            /************************/
            $url  = $request->getLocale() . '/api/create_offlineuser.json';
            $form_data   =  array(
                    'email'     => $form_param['email'],
                    'password'  => $form_param['password'],
                    'country'   => $country_id,
                    'status'    => 1,
                    'ActivationSource' => 'W',
                    'C_id'      => $form_param['C_id']
            );
            $postData = json_encode($form_data);
            // print_r($postData); exit;
            $data = $restClient->restPostForm(AppConstant::WEBAPI_URL . $url, $postData, array('Country-Id' => $country_id));
            // print_r($data);exit;
            if($data['status'] == 1)
            {
                if ($data['status'] == true)
                {
                    return true;
                }
            }
            else
            {
                // HERE IF THE USER IN NOT ADDED IN THE OFFLINE DATABASE
                // WE EITHER UPDATE THE USER IN THE TEMP TABLE OR INSERT IN TO THE TEMP TABLE
                // FOR KEEPING THE LOG.
                // AS IF THE USER IS NOT CREATED IN THE OFFLINE DATABASE THEN USER WILL NOT
                // BE ABLE TO LOGGED IN TO THE WEBSITE TO AVIAL THE SURVICES
                $data = array($form_param['C_id'] , $form_param['email'] , $form_param['password']);
                $data_serilize = serialize($data);
                $em = $this->getDoctrine()->getManager("default");
                $conn = $em->getConnection();
                $queryBuilder = $conn->createQueryBuilder();
                $stm_new = $conn->prepare('SELECT * FROM temp_user WHERE ikt_card_no = ?   ');
                //here $new_value is the new iktissab card id
                $stm_new->bindValue(1, $form_param['C_id']);
                $stm_new->execute();
                $newdata = $stm_new->fetch();
                $data_values = '';
                if ($newdata)
                {
                    $data_values = array(
                          0,
                        $form_param['C_id'],
                        $data_serilize,
                        $country_id,
                        $form_param['C_id']
                    );
                    $stm = $conn->executeUpdate('UPDATE temp_user SET  
                                                            status      = ?,
                                                            ikt_card_no = ? ,
                                                            data        = ?,
                                                            country     = ? 
                    WHERE ikt_card_no = ?  ', $data_values);
                }
                else
                {
                    $stm = $queryBuilder
                        ->insert('temp_user')
                        ->values(
                            array
                            (
                                'ikt_card_no'   => '?',
                                'field'         => '?',
                                'data'          => '?',
                                'country'       => '?',
                                'status'        => '?',
                            )
                        )
                        ->setParameter(0, $form_param['C_id'])
                        ->setParameter(1, 'scenari2_user_not_created')
                        ->setParameter(2, $data_serilize)
                        ->setParameter(3, $country_id)
                        ->setParameter(4, 2);
                    $stm->execute();
                }
                return false;
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




    public function validateIqama($iqama)
    {

            $iqama = $iqama;
            $evenSum = 0;
            $oddSum = 0;
            $entireSum = 0;
            for ($i = 0; $i < strlen($iqama); $i++) {

                $temp = '';
                if ($i % 2) { // odd number
                    $oddSum = $oddSum + $iqama[$i];
                } else {

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
            }
            else
            {
                Throw new Exception($this->get('translator')->trans('Invalid Iqama Id/SSN Numbersa'), 1);
            }
    }

    public function validateIqamaNationality($iqama , $nat_no)
    {
        if (substr($iqama, 0,1)  == 1 && $nat_no == 1) {
            return true;
        }
        else if (substr($iqama, 0,1)  != 1 && $nat_no != 1) {
            return true;
        }
        else
        {
            Throw new Exception($this->get('translator')->trans('Invalid Iqama Id/SSN Numbersa'), 1);
        }
    }



    /**
     * @param Request $request
     * @Route("/{_country}/{_locale}/v2", name="v2")
     *
     */
    function checkiqama2Action()
    {                       // function to validate iqama this code will be implemented in the FormType as callback validation
        $iqama = '2407200837';
        $evenSum = 0;
        $oddSum = 0;
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
        echo "entire sum is" . $entireSum;
        echo $entireSum % 10;
        if (($entireSum % 10) == 0) {
            echo "Iqama is valid";
        } else {
            echo "invalid iqama";
        }


        die('---');
    }









}