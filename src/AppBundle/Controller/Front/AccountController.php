<?php
/**
 * Created by PhpStorm.
 * User: abdulali
 * Date: 12/4/16
 * Time: 9:47 AM
 */

namespace AppBundle\Controller\Front;

use AppBundle\AppConstant;
use AppBundle\Controller\Common\FunctionsController;
use AppBundle\Entity\Repository\UserRepository;
use AppBundle\Entity\User;
use AppBundle\Form\ForgotEmailType;



use AppBundle\Form\IktUpdateType;
use AppBundle\Form\MobileType;
use AppBundle\Form\SendPwdType;
use AppBundle\Form\UpdateEmailType;
use AppBundle\Form\UpdateFullnameType;
use AppBundle\Form\UpdatePasswordType;
use AppBundle\Form\MissingCardType;
use AppBundle\HijriGregorianConvert;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use AppBundle\Form\IqamassnType;
use AppBundle\Form\EnterOtpType;
use Symfony\Component\HttpFoundation\RedirectResponse;



class AccountController extends Controller
{

    /**
     * @Route("/{_country}/{_locale}/account/home", name="account_home")
     */

    public function myAccountAction(Request $request)
    {
        $response = new Response();
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('login', array('_country' => $request->cookies->get(AppConstant::COOKIE_COUNTRY), '_locale' => $request->cookies->get(AppConstant::COOKIE_LOCALE)));
        }
        $this->getUser()->getIktCardNo();
        $commFunct = new FunctionsController();
        $commFunct->setContainer($this->container);
        $activityLog = $this->get('app.activity_log');
        $iktUserData = $this->get('session')->get('iktUserData');
        // print_r($iktUserData);
        try {
                // when everything is fine message is empty
                $message = '';
                /****************/

                if ($commFunct->checkSessionCookies($request) == false) {
                    return $this->redirect($this->generateUrl('landingpage'));
                }

                $userLang = '';
                $locale = $request->getLocale();

                if ($request->query->get('lang')) {
                    $userLang = trim($request->query->get('lang'));
                }
                if ($userLang != '' && $userLang != null) {
                    if ($userLang == $locale) {
                        $commFunct->changeLanguage($request, $userLang);
                        $locale = $request->getLocale();
                    } else {
                        if ($request->cookies->get(AppConstant::COOKIE_LOCALE)) {
                            return $this->redirect($this->generateUrl('account_home', array('_country' => $request->cookies->get(AppConstant::COOKIE_COUNTRY), '_locale' => $request->cookies->get(AppConstant::COOKIE_LOCALE))));
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
                            return $this->redirect($this->generateUrl('account_home', array('_country' => $request->cookies->get(AppConstant::COOKIE_COUNTRY), '_locale' => $request->cookies->get(AppConstant::COOKIE_LOCALE))));
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
                /****************/
                if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
                    if ($commFunct->checkSessionCookies($request) == false) {
                        return $this->redirect($this->generateUrl('landingpage'));
                    }
                }
                else
                {
                    $restClient = $this->get('app.rest_client')->IsAdmin(true);
                    if (!$this->get('session')->get('iktUserData')) {
                        $url = $request->getLocale() . '/api/' . $this->getUser()->getIktCardNo() . '/userinfo.json';
                        // echo AppConstant::WEBAPI_URL.$url;
                        $data = $restClient->restGetForm(AppConstant::WEBAPI_URL . $url, array('Country-Id' => strtoupper($request->get('_country'))));
                        // print_r($data);
                        if($data['status'] == 1)
                        {
                            // response is ok from services
                            if ($data['success'] == "true") {
                                // data is also present from services
                                $this->get('session')->set('iktUserData', $data['user']);
                            }
                            else {
                                // no data is presenet on the services
                                $message = $data['message'];
                            }
                        }
                        else {
                            // exception occrured from the services side
                            $data    = '';
                            $message = $this->get('translator')->trans('An invalid exception occurred');
                        }
                    }
                    // delete it
                    $data_array = '';
                    $iktUserData = $this->get('session')->get('iktUserData');
                    // print_r($iktUserData);
                    $this->getUser()->getIktCardNo();
                    $url_trans_count  = $request->getLocale() . '/api/' . $this->getUser()->getIktCardNo() . '/customer_transaction_count.json';
                    $data_trans_count = $restClient->restGetForm(AppConstant::WEBAPI_URL . $url_trans_count, array('Country-Id' => strtoupper($request->get('_country'))));
                    // print_r($data_trans_count);
                    if($data_trans_count['status'] == 1) {
                        if ($data_trans_count != "" && $data_trans_count != null) {
                            if ($data_trans_count['success'] == true && $data_trans_count['status'] == 1) {
                                // echo AppConstant::WEBAPI_URL.$url;
                                $url_trans_count = "";
                                $url_trans_count = $request->getLocale() . '/api/' . $this->getUser()->getIktCardNo() . '/customermonthtlysales.json';
                                $data_transaction = $restClient->restGetForm(AppConstant::WEBAPI_URL . $url_trans_count, array('Country-Id' => strtoupper($request->get('_country'))));
                                // var_dump($data_transaction);
                                if ($data_transaction['status'] == 1) {
                                    if ($data_transaction['success'] == true) {
                                        $i = 0;
                                        $year = date('Y');
                                        $month = date('m');
                                        $year_later = mktime(0, 0, 0, $month, 30, $year - 1);
                                        // $year_later = mktime(0, 0, 0, $month, 30, $year);-1
                                        $data_array = array();
                                        if ($data_transaction != "" && $data_transaction != null) {
                                            foreach ($data_transaction['data_user'] as $data_transactions) {

                                                // print_r($data_transactions);
                                                // $data_transactions['trans_date'];
                                                $date_current = mktime(0, 0, 0, $data_transactions['Mnth'], 30, $data_transactions['YR']);
                                                if ($date_current > $year_later) {
                                                    $data_array[$i]['c_id'] = $data_transactions['c_id'];
                                                    $data_array[$i]['YR'] = $data_transactions['YR'];
                                                    //$data_array[$i]['Mnth'] = $data_transactions['Mnth'];
                                                    $data_array[$i]['Mnth'] = $this->getMonth($data_transactions['Mnth'], $data_transactions['YR'], $request->cookies->get(AppConstant::COOKIE_LOCALE));
                                                    $data_array[$i]['Sales'] = $data_transactions['Sales'];
                                                    $i++;
                                                }
                                            }
                                        }
                                        else
                                        {
                                            $data_array = "";
                                            $message = $this->get('translator')->trans('Unable to process your request at this time.Please try later');
                                        }
                                    }
                                    else
                                    {
                                        $data_array = "";
                                        $message = $data_transaction['message'];
                                    }
                                }
                                else
                                {
                                    $data_array = "";
                                    $message = $this->get('translator')->trans('Unable to process your request at this time.Please try later');
                                }
                            }
                            else
                            {
                                $data_array = "";
                                $message = $data_trans_count['message'];
                            }
                        }
                        else
                        {
                            $data_array = "";
                            $message = $this->get('translator')->trans('Unable to process your request at this time.Please try later');
                        }
                    }
                    else
                    {
                        $data_array = "";
                        //-- mc-- 0040 $message = $data_trans_count['message'];
                        $message = $this->get('translator')->trans('Unable to process your request at this time.Please try later');
                    }
                     // var_dump($iktUserData);
                     // var_dump($data_array);
                    $count_num = $data_array[0]['c_id'];
                    if($data_array[0]['c_id'] != "" && $data_array[0]['c_id'] != null){
                        $count_num = 1;
                    }
                    else
                    {
                        $count_num = "";
                    }
                    // array_multisort($data_array,SORT_DESC,SORT_NUMERIC);
                    // $this->sksort($data_array, "YR");
                    // $this->sksort($data_array, "Mnth");
                    return $this->render('/account/home.html.twig',
                        array(
                            'iktData'      => $iktUserData,
                            'message'      => $message ,
                            'iktTransData' => $data_array,
                            'count'        => $count_num

                    ));
                }
        }
        catch(Exception $e)
        {
            $e->getMessage();
            $message = $this->get('translator')->trans('An invalid exception has occurred');
            return $this->render('/account/home.html.twig', array('iktData' => 0, 'iktTransData' => '', 'message' => $message ));
        }
        catch(AccessDeniedException $ed)
        {
            $ed->getMessage();
            $message =  $this->get('translator')->trans($ed->getMessage());
            return $this->render('/account/home.html.twig', array('iktData' => '', 'iktTransData' => '', 'message' => $message ));
        }
    }



    public function getMonth($m,$y,$lang){
        $request = new Request();
        $y = substr($y,2,4);
        if($lang == 'en') {
            $month_name = array(
                '1' => 'jan-' . $y, '2' => 'Feb-' . $y,
                '3' => 'march-' . $y, '4' => 'Apr-' . $y,
                '5' => 'May-' . $y, '6' => 'June-' . $y,
                '7' => 'July-' . $y, '8' => 'Aug-' . $y,
                '9' => 'Sept-' . $y, '10' => 'Oct-' . $y,
                '11' => 'Nov-' . $y, '12' => 'Dec-' . $y
            );
            $month = array(
                '1' => '1-' . $y, '2' => '2-' . $y,
                '3' => '3-' . $y, '4' => '4-' . $y,
                '5' => '5-' . $y, '6' => '6-' . $y,
                '7' => '7-' . $y, '8' => '8-' . $y,
                '9' => '9-' . $y, '10' => '10-' . $y,
                '11' => '11-' . $y, '12' => '12-' . $y
            );
        }
        else
        {
            $month_name = array(
                '1' => 'يناير-' . $y, '2' => 'فبراير-' . $y,
                '3' => 'مارس-' . $y, '4'  => 'أبريل-' . $y,
                '5' => 'مايو-' . $y, '6'  => 'يونيو-' . $y,
                '7' => 'يوليو-' . $y, '8' => 'أغسطس-' . $y,
                '9' => 'سبتمبر-' . $y, '10' => 'أكتوبر-' . $y,
                '11'=> 'نوفمبر-' . $y, '12' => 'ديسمبر-' . $y
            );
            $month = array(
                '1' => '1-' . $y, '2'  => '2-' . $y,
                '3' => '3-' . $y, '4'  => '4-' . $y,
                '5' => '5-' . $y, '6'  => '6-' . $y,
                '7' => '7-' . $y, '8'  => '8-' . $y,
                '9' => '9-' . $y, '10' => '10-'. $y,
                '11'=> '11-'. $y, '12' => '12-'. $y
            );
        }
        return $month[$m];
    }





    /**
     * @Route("/{_country}/{_locale}/account/info", name="front_account_info")
     */

    public function accountInfoAction(Request $request)
    {
        echo 'asdf';exit;
        $activityLog = $this->get('app.activity_log');
        $commFunct = new FunctionsController();
        $commFunct->setContainer($this->container);
        if ($commFunct->checkSessionCookies($request) == false) {
            return $this->redirect($this->generateUrl('landingpage'));
        }
        try {
            if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
                return $this->redirectToRoute('login', array('_country' => $request->cookies->get(AppConstant::COOKIE_COUNTRY), '_locale' => $request->cookies->get(AppConstant::COOKIE_LOCALE)));
            }
            $message = '';
            $restClient = $this->get('app.rest_client');
            if (!$this->get('session')->get('iktUserData')) {
                $url = $request->getLocale() . '/api/' . $this->getUser()->getIktCardNo() . '/userinfo.json';
                // echo AppConstant::WEBAPI_URL.$url;
                $data = $restClient->restGet(AppConstant::WEBAPI_URL . $url, array('Country-Id' => strtoupper($request->get('_country'))));
                if ($data['success'] == "true") {
                    $this->get('session')->set('iktUserData', $data['user']);
                }
            }
            $restClient = $this->get('app.rest_client')->IsAdmin(true);
            if (!$this->get('session')->get('iktUserData')) {
                $url = $request->getLocale() . '/api/' . $this->getUser()->getIktCardNo() . '/userinfo.json';
                // echo AppConstant::WEBAPI_URL.$url;
                $data = $restClient->restGetForm(AppConstant::WEBAPI_URL . $url, array('Country-Id' => strtoupper($request->get('_country'))));
                // print_r($data);
                if ($data['status'] == 1) {
                    // response is ok from services
                    if ($data['success'] == "true") {
                        // data is also present from services
                        $this->get('session')->set('iktUserData', $data['user']);
                    } else {
                        // no data is presenet on the services
                        $message = $data['message'];
                    }
                } else {
                    // exception occrured from the services side
                    $data = '';
                    $message = $this->get('translator')->trans('An invalid exception occurred');
                }
            }


            // print_r($data);
            // delete it



            $iktUserData = $this->get('session')->get('iktUserData');


            $url_trans_count = $request->getLocale() . '/api/' . $this->getUser()->getIktCardNo() . '/customer_transaction_count.json';
            $data_trans_count = $restClient->restGetForm(AppConstant::WEBAPI_URL . $url_trans_count, array('Country-Id' => strtoupper($request->get('_country'))));
            //print_r($data_trans_count);
            if ($data_trans_count['status'] == 1) {
                if ($data_trans_count != "" && $data_trans_count != null) {
                    if ($data_trans_count['success'] == true && $data_trans_count['status'] == 1) {
                        // echo AppConstant::WEBAPI_URL.$url;
                        $url_trans_count = $request->getLocale() . '/api/' . $this->getUser()->getIktCardNo() . '/customermonthtlysales.json';
                        $data_transaction = $restClient->restGetForm(AppConstant::WEBAPI_URL . $url_trans_count, array('Country-Id' => strtoupper($request->get('_country'))));
                        //var_dump($data_transaction);
                        if ($data_transaction['status'] == 1) {
                            if ($data_transaction['success'] == true) {
                                $i = 0;
                                $year = date('Y');
                                $month = date('m');
                                $year_later = mktime(0, 0, 0, $month, 30, $year - 1);
                                // $year_later = mktime(0, 0, 0, $month, 30, $year);-1
                                if ($data_transaction != "" && $data_transaction != null) {
                                    foreach ($data_transaction['data_user'] as $data_transactions) {
                                        $i++;
                                        //print_r($data_transactions);
                                        // $data_transactions['trans_date'];
                                        $date_current = mktime(0, 0, 0, $data_transactions['Mnth'], 30, $data_transactions['YR']);
                                        if ($date_current > $year_later) {
                                            $data_array[$i]['c_id'] = $data_transactions['c_id'];
                                            $data_array[$i]['YR'] = $data_transactions['YR'];
                                            //$data_array[$i]['Mnth'] = $data_transactions['Mnth'];
                                            $data_array[$i]['Mnth'] = $this->getMonth($data_transactions['Mnth'], $data_transactions['YR'], $request->cookies->get(AppConstant::COOKIE_LOCALE));
                                            $data_array[$i]['Sales'] = $data_transactions['Sales'];
                                        }
                                    }
                                } else {
                                    $data_array = "";
                                    $message = $this->get('translator')->trans('An invalid exception occurred');
                                }
                            } else {
                                $data_array = "";
                                $message = $data_transaction['message'];
                            }


                        } else {
                            $data_array = "";
                            $message = $this->get('translator')->trans('An invalid exception occurred');

                        }

                    } else {
                        $data_array = "";
                        $message = $data_trans_count['message'];
                    }
                } else {
                    $data_array = "";
                    $message = $this->get('translator')->trans('An invalid exception occurred');
                }
            } else {
                $data_array = "";
                $message = $data_trans_count['message'];
            }
            // var_dump($data_transaction);
            // var_dump($data_array);
            // array_multisort($data_array,SORT_DESC,SORT_NUMERIC);

            //$this->sksort($data_array, "YR");
            //$this->sksort($data_array, "Mnth");


            return $this->render('/account/accountinfo.html.twig', array('iktData' => $iktUserData,

                'iktTransData' => $data_array,
                'message' => $message

            ));
        }
        catch (\Exception $e){
            $message = $this->get('translator')->trans('An invalid exception occurred');
            $data_array = "";
            return $this->render('/account/accountinfo.html.twig', array('iktData' => $iktUserData,
                'iktTransData' => $data_array,
                'message' => $message

            ));
        }
        catch(AccessDeniedException $ed){
            $message =  $this->get('translator')->trans($ed->getMessage());
            $data_array = "";
            return $this->render('/account/accountinfo.html.twig', array('iktData' => $iktUserData,
                'iktTransData' => $data_array,
                'message' => $message
            ));
        }



    }


    /**
     * @Route("/{_country}/{_locale}/account/personalinfo", name="front_account_personalinfo")
     */

    public function personalInfoAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('login', array('_country' => $request->cookies->get(AppConstant::COOKIE_COUNTRY), '_locale' => $request->cookies->get(AppConstant::COOKIE_LOCALE)));
        }

        $activityLog = $this->get('app.activity_log');
        $commFunct   = new FunctionsController();
        $commFunct->setContainer($this->container);
        if ($commFunct->checkSessionCookies($request) == false) {
            return $this->redirect($this->generateUrl('landingpage'));
        }

        try
        {
            $restClient = $this->get('app.rest_client')->IsAdmin(true);
            //print_r($this->get('session')->get('iktUserData'));
            if (!$this->get('session')->get('iktUserData')) {
                $url = $request->getLocale() . '/api/' . $this->getUser()->getIktCardNo() . '/userinfo.json';
                // echo AppConstant::WEBAPI_URL.$url;
                $data = $restClient->restGet(AppConstant::WEBAPI_URL . $url, array('Country-Id' => strtoupper($request->get('_country'))));

                if($data['status'] == 1) {
                    if($data['success'] == "true") {
                        $this->get('session')->set('iktUserData', $data['user']);
                    }
                }
                else
                {
                    if($data['status'] == 0)
                    {
                        // we will not show data to user due to exception error
                        $iktUserData = '';
                        $message = $this->get('translator')->trans($data['message']);
                        $errorcl = 'alert-danger';
                        return $this->render('/account/personalinfo.html.twig',
                            array('iktData' => $iktUserData , 'message' => $message ,
                                'errorcl' => $errorcl));
                    }
                    else
                    {
                        $iktUserData =  '' ;
                        $message = $this->get('translator')->trans('An invalid exception occurred');
                        $message = $message;
                        $errorcl = 'alert-danger';
                        return $this->render('/account/personalinfo.html.twig', array('iktData' => $iktUserData , 'message' => $message ,
                            'errorcl' => $errorcl
                        ));
                    }
                }

            }
            $iktUserData = $this->get('session')->get('iktUserData');
            return $this->render('/account/personalinfo.html.twig',
                array('iktData' => $iktUserData)
            );
        }
        catch(\Exception $e)
        {
            $iktUserData =  $this->get('session')->get('iktUserData');
            // $activityLog->logEvent(AppConstant::ACOUNR_PERSONAL_INFO_ERROR, 0, array('iktissab_card_no' => '', 'message' => $e->getMessage(), 'session' => $iktUserData));
            $message = $this->get('translator')->trans('An invalid exception occurred');
            $errorcl = 'alert-danger';
            return $this->render('/account/personalinfo.html.twig', array('iktData' => $iktUserData , 'message' => $message ,
                'errorcl' => $errorcl
            ));
        }
        catch(AccessDeniedException $ed){
            $message =  $this->get('translator')->trans($ed->getMessage());
            // $activityLog->logEvent(AppConstant::ACOUNR_PERSONAL_INFO_ERROR, 0 , array('iktissab_card_no' => '', 'message' => $ed->getMessage(), 'session' => '' ));
            return $this->render('/account/home.html.twig', array('iktData' => '', 'iktTransData' => '', 'message' => $message ));
        }

    }




    /**
     * @Route("/{_country}/{_locale}/account/email", name="front_account_email")
     */
    public function accountEmailAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY'))
        {
            return $this->redirectToRoute('login',array('_country' => $request->cookies->get(AppConstant::COOKIE_COUNTRY), '_locale' => $request->cookies->get(AppConstant::COOKIE_LOCALE)));
        }

        $activityLog      = $this->get('app.activity_log');
        $commFunct = new FunctionsController();
        $commFunct->setContainer($this->container);
        try
        {
            $tokenStorage       = $this->get('security.token_storage');
            $restClient       = $this->get('app.rest_client')->IsAdmin(true);
            $locale_cookie    = $request->getLocale();
            $country_cookie   = $request->get('_country');
            $userLang         = trim($request->query->get('lang'));
            $Country_id       = strtoupper($this->getCountryId($request));
            $iktUserData      = $this->get('session')->get('iktUserData');
            $posted           = array();
            $iktCardNo        = $iktUserData['C_id'];
            $iktID_no         = $iktUserData['ID_no'];
            // $currentEmail  = $iktUserData['email'];
            $currentEmail = $tokenStorage->getToken()->getUser()->getUsername();

            $user_email_online  = $tokenStorage->getToken()->getUser()->getUsername();
            $mobile           = $iktUserData['mobile'];
            $form = $this->createForm(UpdateEmailType::class, array() , array(
                    'extras'  => array('email'   => $currentEmail)));
            $data = $request->request->all();
            $form->handleRequest($request);
            //For logged in user infomation





            if($form->isSubmitted() && $form->isValid())
            {
                $token_loaded  = trim(strip_tags($data['update_email']['token']));
                if($commFunct->checkCsrfToken($token_loaded, 'account_upd_email' ))
                {
                    $errors = "";
                    $validate_data = array(strtolower($data['update_email']['newemail']['first']) ,
                        strtolower($data['update_email']['newemail']['second']) , $data['update_email']['currentemail'] , $data['update_email']['old_password']);
                    $errors = $commFunct->validateData($validate_data);
                    if($errors > 0)
                    {
                        $commFunct->setCsrfToken('account_upd_email');
                        $session = new Session();
                        $token   = $session->get('account_upd_email');
                        $message = $this->get('translator')->trans('Please provide valid data');
                        $errorcl = 'alert-danger';
                        return $this->render('account/email.html.twig',
                            array('form' => $form->createView(), 'token' => $token , 'message' => $message, 'errorcl' => $errorcl));
                    }

                    if (strtolower($data['update_email']['newemail']['first']) == strtolower($data['update_email']['currentemail'])) {
                        $commFunct->setCsrfToken('account_upd_email');
                        $session = new Session();
                        $token   = $session->get('account_upd_email');

                        $errorcl = 'alert-danger';
                        $message = $this->get('translator')->trans('The new email is already registered before , please enter a valid email');
                        $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_EMAIL_ALREADY_REGISTERED, $iktUserData['C_id'],
                             array('iktissab_card_no' => $iktUserData['C_id'], 'message' => $message, 'session' => $iktUserData),
                            strtolower($data['update_email']['currentemail']), strtolower($data['update_email']['newemail']['first'])
                            );
                        return $this->render('account/email.html.twig',
                            array('form'  => $form->createView(),
                                'token'   => $token,
                                'message' => $message, 'errorcl' => $errorcl)
                        );
                    }


                    // here we will add validation to the form
                    /************************/
                    // print_r($data);
                    $newemail = strip_tags(strtolower($data['update_email']['newemail']['first']));
                    /**************************/
                    $form_data[0] = array(
                        'C_id'       => $iktCardNo,
                        'field'      => 'email',
                        'new_value'  => strip_tags(strtolower($data['update_email']['newemail']['first'])),
                        'old_value'  => strtolower($currentEmail),
                        'comments'   => 'Update user account email'
                    );
                    // print_r($form_data[0]);exit;
                    $this->get('session')->set('new_value', strip_tags(strtolower($data['update_email']['newemail']['first'])));
                    // here we will check if the email is already registered on website or not
                    $email_val = $this->checkEmail(strip_tags(strtolower($data['update_email']['newemail']['first'])), $Country_id, $iktCardNo);

                    if (!empty($email_val) && $email_val != null)
                    {
                        // print_r($email_val);
                        // check email exist
                        if ($email_val['success'] == true) {
                            if ($email_val['result']['email'] == true) {
                                $commFunct->setCsrfToken('account_upd_email');
                                $session = new Session();
                                $token   = $session->get('account_upd_email');
                                $errorcl = 'alert-danger';
                                $message = $this->get('translator')->trans('The new email is already registered before , please enter a valid email');
                                $activityLog->logEvent( AppConstant::ACTIVITY_UPDATE_EMAIL_ALREADY_REGISTERED, $iktUserData['C_id'],
                                    array('iktissab_card_no' => $iktUserData['C_id'], 'message' => $message, 'session' => $iktUserData ,
                                        'postData' => $form_data[0] ),
                                    $form_data[0]['old_value'], $form_data[0]['new_value']
                                    );
                                return $this->render('account/email.html.twig',
                                    array(
                                        'form'    => $form->createView(),
                                        'token'   => $token,
                                        'message' => $message, 'errorcl' => $errorcl)
                                );
                            }
                        }
                        else
                        {
                            $iktCardNo = $iktUserData['C_id'];
                            $iktID_no  = $iktUserData['ID_no'];
                            $currentEmail = $iktUserData['email'];
                            // form posted data
                            $data = $request->request->all();
                            //$form_data = array('email' => strip_tags($data['update_email']['newemail']['first']) , 'c_id' => $iktCardNo );

                                // 1. GET CURRENT USER PASSWORD
                                // 2. MATCH WITH USER ENTER PASSWORD
                                // 3. WHEN PASSWORD IS MATCHED CHANGE EMAIL ADDRESS
                                $iktUserData = $this->get('session')->get('iktUserData');
                                $iktCardNo   = $iktUserData['C_id'];
                                $iktID_no    = $iktUserData['ID_no'];
                                $em = $this->getDoctrine()->getManager();
                                $userInfoLoggedIn = $em->getRepository('AppBundle:User')->find($iktCardNo);
                                $email = $userInfoLoggedIn->getEmail();
                                // print_r($userInfoLoggedIn);
                                $postData = $request->request->all();
                                // dump($postData);exit;
                                // var_dump($objUser);
                                // current is the logged in user password
                                $objUser = $this->getUser();
                                // print_r($objUser);exit;
                                $password_current = $userInfoLoggedIn->getPassword();
                                $old_password = md5(trim(strip_tags($postData['update_email']['old_password'])));
                                if ($form->isValid()) {
                                    if ($password_current != $old_password)
                                    {
                                        $commFunct->setCsrfToken('account_upd_email');
                                        $session = new Session();
                                        $token   = $session->get('account_upd_email');
                                        $message = $this->get('translator')->trans('Please enter correct password');
                                        $errorcl = 'alert-danger';
                                        return $this->render('account/email.html.twig',
                                            array('form' => $form->createView(),
                                                'token' => $token,
                                                'message' => $message, 'errorcl' => $errorcl));
                                    }
                                    else
                                    {
                                        $newemail = strip_tags(strtolower($data['update_email']['newemail']['first']));
                                        // here when the offline db email is update then udate online
                                        $email_changed = $this->accountSMSVerification($request, $newemail);
                                        // print_r($email_changed);
                                        if ($email_changed == true)
                                        {
                                            $tokenStorage->getToken()->getUser()->setUsername($newemail);
                                            $restClient = $this->get('app.rest_client')->IsAdmin(true);
                                            $url = $request->getLocale() . '/api/' . $this->getUser()->getIktCardNo() . '/userinfo.json';
                                            $data_user = $restClient->restGetForm(AppConstant::WEBAPI_URL . $url, array('Country-Id' => strtoupper($request->get('_country'))));
                                            if ($data_user['success'] == true) {
                                                $this->get('session')->set('iktUserData', $data_user['user']);
                                            }
                                            $message = $this->get('translator')->trans('Your email is updated. Next time, please use the ( %email% ) email address for signing to the website',array('%email%' => $this->get('session')->get("new_value")));
                                            $commFunct->setCsrfToken('account_upd_email');
                                            $session = new Session();
                                            $token = $session->get('account_upd_email');
                                            $messageLog = $this->get('translator')->trans('Email is updated');
                                            $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_EMAIL_SUCCESS, $iktUserData['C_id'],
                                                array('iktissab_card_no' => $iktUserData['C_id'], 'message' => $messageLog,
                                                    'session' => $iktUserData['C_id'] , 'postData' => $form_data[0]),
                                                $form_data[0]['old_value'], $form_data[0]['new_value']
                                                );
                                            $errorcl = 'alert-success';
                                            return $this->render('account/email.html.twig',
                                                array('form'  => $form->createView(),
                                                    'token'   => $token,
                                                    'message' => $message, 'errorcl' => $errorcl));
                                        }
                                        else
                                        {
                                            $commFunct->setCsrfToken('account_upd_email');
                                            $session = new Session();
                                            $token   = $session->get('account_upd_email');
                                            $messageLog = $this->get('translator')->trans('Unable to update record');
                                            $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_EMAIL_ERROR, $iktUserData['C_id'],
                                                array('iktissab_card_no' => $iktUserData['C_id'], 'message' => $messageLog, 'session' => $iktUserData['C_id'] , 'postData' => $form_data[0]   ),
                                                $form_data[0]['old_value'], $form_data[0]['new_value']
                                                );
                                            $message = $this->get('translator')->trans('Unable to update record1234');
                                            // update password in the offline database
                                            $errorcl = 'alert-danger';
                                            return $this->render('account/email.html.twig',
                                                array('form'  => $form->createView(),
                                                    'token'   => $token,
                                                    'message' => $message, 'errorcl' => $errorcl));
                                        }
                                    }
                                } else {
                                    $commFunct->setCsrfToken('account_upd_email');
                                    $session = new Session();
                                    $token = $session->get('account_upd_email');

                                    $message = $this->get('translator')->trans('Unable to update record');
                                    $errorcl = 'alert-danger';
                                    $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_EMAIL_ERROR, $iktUserData['C_id'],

                                        array('iktissab_card_no' => $iktUserData['C_id'], 'message' => $message, 'session' => $iktUserData['C_id']),
                                        $form_data[0]['old_value'], $form_data[0]['new_value']
                                        );
                                    return $this->render('account/email.html.twig',
                                        array('form' => $form->createView(), 'token' => $token, 'message' => $message, 'errorcl' => $errorcl));
                                }
                        }
                    }
                    else
                    {
                        $commFunct->setCsrfToken('account_upd_email');
                        $session = new Session();
                        $token   = $session->get('account_upd_email');
                        $message = $this->get('translator')->trans('Unable to process your request at this time.Please try later');
                        $errorcl = 'alert-danger';
                        $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_EMAIL_ERROR, $iktUserData['C_id'],
                            array('iktissab_card_no' => $iktUserData['C_id'], 'message' => $message, 'session' => $iktUserData),
                            $form_data[0]['old_value'], $form_data[0]['new_value']
                            );
                        return $this->render('account/email.html.twig',
                            array('form' => $form->createView(), 'token' => $token, 'message' => $message, 'errorcl' => $errorcl)
                        );
                    }
                }
                else
                {
                    $commFunct->setCsrfToken('account_upd_email');
                    $session = new Session();
                    $token   = $session->get('account_upd_email');
                    $message = $this->get('translator')->trans('Unable to process your request at this time.Please try later');
                    $errorcl = 'alert-danger';
                    return $this->render('account/email.html.twig',
                        array('form' => $form->createView(), 'token' => $token , 'message' => $message, 'errorcl' => $errorcl)
                    );
                }
            }
            else
            {
                $commFunct->setCsrfToken('account_upd_email');
                $session = new Session();
                $token   = $session->get('account_upd_email');
                $message = $this->get('translator')->trans('');
                $errorcl = 'alert-danger';
                return $this->render('account/email.html.twig',
                    array('form' => $form->createView(), 'token' => $token , 'message' => $message, 'errorcl' => $errorcl)
                );
            }
        }
        catch (\Exception $e)
        {
            // echo 'tested NO-I ERROR';
            $e->getMessage();
            $commFunct->setCsrfToken('account_upd_email');
            $session = new Session();
            $token   = $session->get('account_upd_email');
            $errorcl = 'alert-danger';
            return $this->render('account/email.html.twig',
                array('form'  => $form->createView(),
                    'message' => $this->get('translator')->trans('Unable to process your request at this time.Please try later') ,
                    'token'   => $token,
                    'errorcl' => $errorcl )
            );
        }
        catch (AccessDeniedException $ed)
        {
            // echo 'tested NO-I ERROR';
            $commFunct->setCsrfToken('account_upd_email');
            $session = new Session();
            $token   = $session->get('account_upd_email');
            $errorcl = 'alert-danger';
            return $this->render('account/email.html.twig',
                array('form'  => $form->createView(),
                    'token'   => $token,
                    'message' => $this->get('translator')->trans('Unable to process your request at this time.Please try later') , 'errorcl' => $errorcl )
            );
        }
    }


    /**
     *
     * @Route("/{_country}/{_locale}/account/smsverification", name="front_account_sms_verification")
     * 
     */
    public function accountSMSVerification(Request $request, $newemail)
    {
        $activityLog  = $this->get('app.activity_log');
        try {
                // $data_form['smsverify'];exit;
                // this method is used to change offline user account email
                $restClient   = $this->get('app.rest_client')->IsAdmin(true);
                $commFunct   = new FunctionsController();
                $commFunct->setContainer($this->container);
                $Country_id   = strtoupper($this->getCountryId($request));
                $iktUserData  = $this->get('session')->get('iktUserData');
                $iktCardNo    = $iktUserData['C_id'];
                $iktID_no     = $iktUserData['ID_no'];
                $currentEmail = $iktUserData['email'];
                $data_form    = "";
                $comments     = "";
                $data_form    = $request->request->all();

                $url          = $request->getLocale() . '/api/update_user_email.json';
                $comments     = '';
                $form_data = array(
                    'C_id'      => $iktCardNo,
                    'field'     => 'email',
                    'new_value' => $newemail,
                    'old_value' => $currentEmail,
                    'comments'  => $comments,
                    'source'    => "W",
                    'browser'   => $commFunct->getBrowserInfo()
                );
                $postData = json_encode($form_data);
                $data = array();
                $data = $restClient->restPostForm(AppConstant::WEBAPI_URL . $url, $postData, array('Country-Id' => strtoupper($request->get('_country'))));
                // print_r($data);exit;
                if ($data["success"] == true)
                {
                    // Due to proxy server implementation no need to update email in the online database.
                    // the $update_email_status from $this->updateEmailOffline is set to true.
                    // $update_email_status = $this->updateEmailOffline($request, $newemail);
                    $update_email_status = true;
                    if ($update_email_status == true)
                    {
                        // update local
                        $email_val = $this->updateEmail($this->get('session')->get('new_value'), $Country_id, $iktCardNo);
                    }
                    else
                    {
                        $email_val = 0;
                    }

                    if ($email_val == '1')
                    {
                       return true;
                    }
                    else
                    {
                        return false;
                    }
                }
                elseif ($data['success'] == false)
                {
                    // $this->get('session')->set('iktUserData', $data['user']);
                    if ($data['status'] == 1)
                    {
                        return false;
                    }
                    else
                    {
                       return false;
                    }
                }
                else
                {
                    return false;
                }
        }
        catch (\Exception $e )
        {
            return false;
        }
        catch (AccessDeniedException $ed)
        {
            return false;
        }
    }





    private function checkEmail($email,$Country_id, $C_id)
    {
        try
        {
            // $em = $this->getDoctrine()->getEntityManager();
            $em = $this->getDoctrine()->getManager();
            $conn = $em->getConnection();
            $queryBuilder = $conn->createQueryBuilder();
            $country_id   = strtolower($Country_id);
            $stm = $conn->prepare('SELECT * FROM   user  WHERE   email = ? AND ikt_card_no != ?   ');
            $stm->bindValue(1, $email);
            $stm->bindValue(2, $C_id);
            $stm->execute();
            $result = $stm->fetch();
            if($result) {
                $data_email = array('success' => true, 'result' => $result);
                return $data_email;
            }
            else
            {
                return  $data_email = array('success' => false , 'result' => '');
            }
        }
        catch (\Exception $e)
        {
           return  $data_email = array('success' => false , 'result' => $e->getMessage());
        }
    }

    
    private function updateEmail( $email , $Country_id, $C_id   )
    {
        try
        {
            $em = $this->getDoctrine()->getManager();
            $conn = $em->getConnection();
            $email = $email;
            $Country_id = $Country_id;
            $C_id = $C_id;

            if ($Country_id == 'EG') {
                $tbl_suffix = "_EG";
            }
            else
            {
                $tbl_suffix = "";
            }
            $user = $em->getRepository("AppBundle:User")->find($C_id);
            if ($user->getEmail() != "" && $user->getEmail() != null)
            {
                $date_now = date('Y-m-d H:i:s');
                $date_current = explode(' ', $date_now );
                $date_current_days   = explode('-', $date_current[0] );
                $date_current_hours  = explode(':' ,$date_current[1]);
                $current_time =  mktime($date_current_hours[0], $date_current_hours[1], $date_current_hours[2],
                    $date_current_days[1], $date_current_days[2] , $date_current_days[0]);
                $data_values = array($current_time,$email , $C_id );
                $stm = $conn->executeUpdate('UPDATE user SET reg_date = ? , email = ? WHERE ikt_card_no = ?   ', $data_values);
                if($stm == true)
                {
                    return true;
                }
                else
                {
                    return false;
                }
            }
            else
            {
                return false;
            }
        }
        catch (\Exception $e)
        {
            return false;
        }
    }



    /**
     * @Route("/{_country}/{_locale}/account/test")
     */
    public function accountTestAction(Request $request)
    {
        return $this->render('account/test.html.twig',
            array('iktData' => 'test')
        );
    }

    /**
     *
     * @Route("/{_country}/{_locale}/account/smsverificationmobile", name="front_account_sms_verificationmobile")
     *
     */
    public function smsverificationmobile(Request $request)
    {
        $restClient  = $this->get('app.rest_client')->IsAdmin(true);
        $iktUserData = $this->get('session')->get('iktUserData');
        $activityLog = $this->get('app.activity_log');
        $commFunct   = new FunctionsController();
        $commFunct->setContainer($this->container);
        $session = new Session();
        $country_id = $request->get('_country');
        if($country_id == 'eg')
        {
            $country_id = 'eg';
        }
        else
        {
            $country_id  = 'sa';
        }
        $account_upd_mobile_data = $session->get('account_upd_mobile_data');
        $otp = $session->get('smscodemodile');
        $url = $request->getLocale() . '/api/update_user_mobile.json';
        // print_r($account_upd_mobile_data);

        // mobile_webservice_format
        $mobile_format_webservice = $account_upd_mobile_data['mobile_webservice_format']; //$this->getMobileFormat($request , $data['mobile']['mobile']);


        $form_data = array(
            'C_id'      => $account_upd_mobile_data['iktissabNo'],
            'field'     => 'mobile',
            'old_value' => $account_upd_mobile_data['mobile'],
            'new_value' => $account_upd_mobile_data['mobile_webservice_format'],
            'comments'  => strip_tags($account_upd_mobile_data['comments'])
        );
        $postData = json_encode($form_data);
        $request->get('_country');
        try {
            $errors = "";
            $validate_data = array($account_upd_mobile_data['iktissabNo'], $account_upd_mobile_data['mobile'],
                $account_upd_mobile_data['mobile_webservice_format'], $account_upd_mobile_data['comments']);
            $errors = $commFunct->validateData($validate_data);
            if ($errors > 0) {
                $commFunct->setCsrfToken('account_upd_mobile_sms');
                $session = new Session();
                $token = $session->get('account_upd_mobile_sms');

                $message = $this->get('translator')->trans('Please provide valid data');
                $errorcl = 'alert-danger';
                return $this->render('account/sendsmsmobile.html.twig',
                    array('token' => $token, 'country' => $country_id, 'message' => $message, 'errorcl' => $errorcl)
                );
            }

            if ($commFunct->checkCsrfToken(strip_tags($account_upd_mobile_data['token']), '922992929292')) {

                $data = $restClient->restPostForm(AppConstant::WEBAPI_URL . $url, $postData, array('Country-Id' => strtoupper($request->get('_country'))));
                //print_r($data);exit;

                if (!empty($data)) {
                    if ($data['success'] == true) {
                        $commFunct->setCsrfToken('account_upd_mobile_sms');
                        $session = new Session();
                        $token = $session->get('account_upd_mobile_sms');

                        $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_MOBILE_SUCCESS, $iktUserData['C_id'], array('iktissab_card_no' => $account_upd_mobile_data['iktissabNo'], 'message' => $data['message'], 'session' => $iktUserData),"null","null");
                        $message = $this->get('translator')->trans('Unable to process your request at this time.Please try later');
                        $errorcl = 'alert-danger';
                        $activityLog->logEvent(AppConstant::ACTIVITY_EMAIL_UPDATE_SMS_ERROR, $iktUserData['C_id'], array('iktissab_card_no' => $iktUserData['C_id'], 'message' => $message, 'session' => $iktUserData),"null","null");
                        return $this->render('account/sendsmsmobile.html.twig',
                            array('message' => $message, 'errorcl' => $errorcl, 'code' => $otp , 'token' => $token )
                        );
                    }
                    if ($data['success'] == false)
                    {
                        if ($data['status'] == 1)
                        {
                            $commFunct->setCsrfToken('account_upd_mobile_sms');
                            $session = new Session();
                            $token = $session->get('account_upd_mobile_sms');

                            $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_MOBILE_ERROR, $iktUserData['C_id'], array('iktissab_card_no' => $iktUserData['C_id'], 'message' => $data['message'], 'session' => $iktUserData),"null","null");
                            $message = $this->get('translator')->trans('Unable to process your request at this time.Please try later');
                            $errorcl = 'alert-danger';
                            $activityLog->logEvent(AppConstant::ACTIVITY_EMAIL_UPDATE_SMS_ERROR, $iktUserData['C_id'], array('iktissab_card_no' => $iktUserData['C_id'], 'message' => $message, 'session' => $iktUserData),"null","null");
                            return $this->render('account/sendsmsmobile.html.twig',
                                array('message' => $message, 'errorcl' => $errorcl, 'code' => $otp)
                            );
                        }
                        else
                        {
                            $commFunct->setCsrfToken('account_upd_mobile_sms');
                            $session = new Session();
                            $token = $session->get('account_upd_mobile_sms');

                            $message = $this->get('translator')->trans('Unable to process your request at this time.Please try later');
                            $errorcl = 'alert-danger';
                            $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_MOBILE_ERROR, $iktUserData['C_id'], array('iktissab_card_no' => $iktUserData['C_id'], 'message' => $message, 'session' => $iktUserData),"null","null");
                            return $this->render('account/sendsmsmobile.html.twig',
                                array('message' => $message, 'errorcl' => $errorcl, 'code' => $otp)
                            );
                        }
                    }
                }
                else
                {
                    $commFunct->setCsrfToken('account_upd_mobile_sms');
                    $session = new Session();
                    $token = $session->get('account_upd_mobile_sms');

                    $message = $this->get('translator')->trans('Unable to process your request at this time.Please try later');
                    $errorcl = 'alert-danger';
                    $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_MOBILE_ERROR, $iktUserData['C_id'], array('iktissab_card_no' => $iktUserData['C_id'], 'message' => $message, 'session' => $iktUserData),"null","null");
                    return $this->render('account/sendsmsmobile.html.twig',
                        array('message' => $message, 'errorcl' => $errorcl, 'code' => $otp)
                    );
                }
            }
            else
            {
                $commFunct->setCsrfToken('account_upd_mobile_sms');
                $session = new Session();
                $token = $session->get('account_upd_mobile_sms');

                $message = $this->get('translator')->trans('Unable to process your request at this time.Please try later');
                $errorcl = 'alert-danger';
                return $this->render('account/sendsmsmobile.html.twig',
                    array( 'message' => $message, 'errorcl' => $errorcl , 'code' =>  $otp)
                );
            }
        } catch (\Exception $e) {

            $commFunct->setCsrfToken('account_upd_mobile_sms');
            $session = new Session();
            $token = $session->get('account_upd_mobile_sms');
            $message = $this->get('translator')->trans('Unable to process your request at this time.Please try later');
            $errorcl = 'alert-danger';
            return $this->render('account/sendsmsmobile.html.twig',
                array( 'message' => $message, 'errorcl' => $errorcl , 'code' =>  $otp)
            );
        }
    }

    /**
     * @Route("/{_country}/{_locale}/account/mobile" , name="front_account_mobile")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function userMobileAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY'))
        {
            return $this->redirectToRoute('login',array('_country' => $request->cookies->get(AppConstant::COOKIE_COUNTRY), '_locale' => $request->cookies->get(AppConstant::COOKIE_LOCALE)));
        }
        $activityLog = $this->get('app.activity_log');
        $restClient  = $this->get('app.rest_client')->IsAdmin(true);
        $iktUserData = $this->get('session')->get('iktUserData');
        $commFunct   = new FunctionsController();
        $commFunct->setContainer($this->container);
        // check user
        // var_dump($this->get('session'));
        $language   = $request->getLocale();
        // echo '===='.$this->get('session')->get('userSelectedCountry');
        // print_r($iktUserData);exit;
        $data = $request->request->all();
        $country_id = $request->get('_country');
        if($country_id == 'eg')
        {
            $country_id = 'eg';
        }
        else
        {
            $country_id  = 'sa';
        }
        // var_dump($iktUserData);
        $posted = array();
        $iktCardNo      = $iktUserData['C_id'];
        $iktID_no       = $iktUserData['ID_no'];
        // echo  '----> iktissab user '.$iktMobile      = $iktUserData['mobile'];
        $iktMobile      = $iktUserData['mobile'];
        $iktID_no = $iktUserData['ID_no'];
        // get current email of the user
        $currentEmail = $iktUserData['email']; // MobileType MobileType
        $form = $this->createForm(MobileType::class,  array() ,array(
            'extras'    => array('country'  => $country_id, 'iktID_no' => $iktID_no )));
        $form->handleRequest($request);
        $url = $request->getLocale() . '/api/update_user_mobile.json';
        try
        {
            if(!empty($data) && $data != null)
            {
                if($form->isValid())
                {
                    $errors = "";
                    $validate_data = array($data['mobile']['iqamaid_mobile'] , $data['mobile']['mobile'],
                        $data['mobile']['comment_mobile']);
                    $errors = $commFunct->validateData($validate_data);
                    if($errors > 0)
                    {
                        $commFunct->setCsrfToken('account_upd_mobile');
                        $session = new Session();
                        $token   = $session->get('account_upd_mobile');

                        $message = $this->get('translator')->trans('Please provide valid data');
                        $errorcl = 'alert-danger';
                        return $this->render('account/mobile.html.twig',
                            array('form' => $form->createView(), 'token' => $token, 'country' => $country_id, 'message' => $message, 'errorcl' => $errorcl)
                        );
                    }
                    if ($commFunct->checkCsrfToken(strip_tags($data['mobile']['token']), 'account_upd_mobile'))
                    {
                        /************************/
                        $val_iqamaid_mobile = filter_var($data['mobile']['iqamaid_mobile'], FILTER_SANITIZE_STRING);
                        $iqamaid_mobile = trim(strip_tags($val_iqamaid_mobile));
                        //echo '===> posted value '.$mobile = trim($data['mobile']['mobile']);
                        $val_mobile_sanitiz = filter_var($data['mobile']['mobile'],FILTER_SANITIZE_STRING);
                        $mobile = trim(strip_tags($val_mobile_sanitiz));
                        // this is sanitize below
                        $comment_mobile = trim(strip_tags($data['mobile']['comment_mobile']));
                        //print_r($posted);
                        /************************/
                        if ($country_id == 'sa') {
                            $extension = '0';
                            $commFunct->setCsrfToken('account_upd_mobile');
                            $session = new Session();
                            $token = $session->get('account_upd_mobile');
                            if ($iktID_no != $iqamaid_mobile) {
                                $message = $this->get('translator')->trans('Invalid Iqama Id/SSN or mobile number. Please enter correct Iqama Id/SSN' . $country_id);
                                $errorcl = 'alert-danger';
                                return $this->render('account/mobile.html.twig',
                                    array('form' => $form->createView(), 'token' => $token, 'country' => $country_id, 'message' => $message, 'errorcl' => $errorcl));
                            }
                            if ($mobile == $iktMobile) {
                                $errorcl = 'alert-danger';
                                $message = $this->get('translator')->trans('Your new mobile number and old mobile number must not be same');
                                return $this->render('account/mobile.html.twig', array('form' => $form->createView(), 'country' => $country_id, 'message' => $message, 'token' => $token, 'errorcl' => $errorcl));
                            }
                        }
                        if ($country_id == 'eg') {
                            $commFunct->setCsrfToken('account_upd_mobile');
                            $session = new Session();
                            $token = $session->get('account_upd_mobile');
                            $extension = strip_tags($data['mobile']['ext']);
                            if ($iktID_no != $iqamaid_mobile) {
                                $errorcl = 'alert-danger';
                                $message = $this->get('translator')->trans('Invalid Iqama Id/SSN Number' . $country_id);
                                return $this->render('account/mobile.html.twig', array('form' => $form->createView(), 'country' => $country_id, 'message' => $message, 'token' => $token, 'errorcl' => $errorcl));
                            }
                            $mobile_egyy = $extension.$mobile;
                            if ($mobile_egyy == $iktMobile) {
                                $errorcl = 'alert-danger';
                                $message = $this->get('translator')->trans('Your new mobile number and old mobile number must not be same');
                                return $this->render('account/mobile.html.twig', array('form' => $form->createView(), 'country' => $country_id, 'message' => $message, 'token' => $token, 'errorcl' => $errorcl));
                            }
                        }
                        /**************************/
                        // set mobile number to 10 digits for webservice which needs 10 digits but when used 966 on website form
                        // then we are forcing user to enter 9 digits without 0 for KSA.
                        $mobile_format_webservice = $extension . $mobile; //$this->getMobileFormat($request , $data['mobile']['mobile']);
                        $val_sanitize_comment_mobile = filter_var($data['mobile']['comment_mobile'], FILTER_SANITIZE_STRING);
                        $form_data = array(
                            'C_id'      => $iktCardNo,
                            'field'     => 'mobile',
                            'old_value' => $iktMobile,
                            'new_value' => $mobile_format_webservice,
                            'comments'  => strip_tags($val_sanitize_comment_mobile),
                            'source'    => "W",
                            'browser'   => $commFunct->getBrowserInfo(),
                        );
                        $postData = json_encode($form_data);
                        $request->get('_country');
                        try
                        {
                            $data = $restClient->restPostForm(AppConstant::WEBAPI_URL . $url, $postData, array('Country-Id' => strtoupper($request->get('_country'))));
                            //print_r($data);exit;
                            if (!empty($data))
                            {
                                if ($data['success'] == true)
                                {
                                    $commFunct->setCsrfToken('account_upd_mobile');
                                    $session = new Session();
                                    $token   = $session->get('account_upd_mobile');
                                    $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_MOBILE_SUCCESS, $iktUserData['C_id'],

                                        array('iktissab_card_no' => $iktUserData['C_id'],
                                        'message' => $data['message'], 'session' => $iktUserData , 'postedData' => $form_data  ),
                                        $form_data['old_value'], $form_data['new_value']
                                        );
                                    $message = $this->get('translator')->trans($data['message']);
                                    $errorcl = 'alert-success';
                                    return $this->render('account/mobile.html.twig',
                                        array('form' => $form->createView(), 'token' => $token, 'country' => $country_id, 'message' => $message, 'errorcl' => $errorcl)
                                    );
                                }
                                if ($data['success'] == false)
                                {
                                    if ($data['status'] == 1)
                                    {
                                        $commFunct->setCsrfToken('account_upd_mobile');
                                        $session = new Session();
                                        $token   = $session->get('account_upd_mobile');
                                        $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_MOBILE_PENDING, $iktUserData['C_id'],
                                            array('iktissab_card_no' => $iktUserData['C_id'],
                                            'message' => $data['message'], 'session' => $iktUserData , 'postedData' => $form_data ),
                                            $form_data['old_value'], $form_data['new_value']
                                            );
                                        $message = $this->get('translator')->trans($data['message']);
                                        $errorcl = 'alert-danger';
                                        return $this->render('account/mobile.html.twig',
                                            array('form' => $form->createView(), 'token' => $token, 'country' => $country_id, 'message' => $message, 'errorcl' => $errorcl)
                                        );
                                    }
                                    else
                                    {
                                        $commFunct->setCsrfToken('account_upd_mobile');
                                        $session = new Session();
                                        $token   = $session->get('account_upd_mobile');
                                        $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_MOBILE_ERROR, $iktUserData['C_id'],
                                            array('iktissab_card_no' => $iktUserData['C_id'],
                                            'message' => $data['message'], 'session' => $iktUserData , 'postedData' => $form_data ),
                                            $form_data['old_value'], $form_data['new_value']
                                            );
                                        $message = $this->get('translator')->trans($data['message']);
                                        $errorcl = 'alert-danger';
                                        return $this->render('account/mobile.html.twig',
                                            array('form' => $form->createView(), 'token' => $token, 'country' => $country_id, 'message' => $message, 'errorcl' => $errorcl)
                                        );
                                    }
                                }
                            }
                            else
                            {
                                $commFunct->setCsrfToken('account_upd_mobile');
                                $session = new Session();
                                $token   = $session->get('account_upd_mobile');
                                $message = $this->get('translator')->trans('Unable to update record');
                                $errorcl = 'alert-danger';
                                return $this->render('account/mobile.html.twig',
                                    array('form' => $form->createView(), 'token' => $token, 'country' => $country_id, 'message' => $message, 'errorcl' => $errorcl)
                                );
                            }
                        }
                        catch (\Exception $e)
                        {
                            $commFunct->setCsrfToken('account_upd_mobile');
                            $session = new Session();
                            $token   = $session->get('account_upd_mobile');
                            $message = $this->get('translator')->trans('An invalid exception occurred');
                            $errorcl = 'alert-danger';
                            return $this->render('account/mobile.html.twig',
                                array('form' => $form->createView(), 'country' => $country_id, 'message' => $message, 'token' => $token, 'errorcl' => $errorcl)
                            );
                        }
                    }
                    else
                    {
                        $commFunct->setCsrfToken('account_upd_mobile');
                        $session = new Session();
                        $token   = $session->get('account_upd_mobile');
                        $message = $this->get('translator')->trans('Unable to process your request at this time.Please try later');
                        $errorcl = 'alert-danger';
                        return $this->render('account/mobile.html.twig',
                            array('form' => $form->createView(), 'token' => $token, 'country' => $country_id, 'message' => $message, 'errorcl' => $errorcl)
                        );
                    }
                }
                else
                {
                    $commFunct->setCsrfToken('account_upd_mobile');
                    $session = new Session();
                    $token   = $session->get('account_upd_mobile');
                    $message = $this->get('translator')->trans('Unable to process your request at this time.Please try later');
                    $errorcl = 'alert-danger';
                    return $this->render('account/mobile.html.twig',
                        array('form' => $form->createView(), 'token' => $token, 'country' => $country_id, 'message' => $message, 'errorcl' => $errorcl)
                    );
                }
            }
            else
            {
                // this will load view for the first time when user click on the update mobile link
                // echo 'ads';
                $commFunct->setCsrfToken('account_upd_mobile');
                $session = new Session();
                $token   = $session->get('account_upd_mobile');
                $message = $this->get('translator')->trans('');
                $errorcl = 'alert-danger';
                return $this->render('account/mobile.html.twig',
                    array('form' => $form->createView(), 'token' => $token , 'country' => $country_id,  'message' => $message , 'errorcl'=> $errorcl )
                );
            }
        }
        catch (\Exception $e)
        {
            $commFunct->setCsrfToken('account_upd_mobile');
            $session = new Session();
            $token   = $session->get('account_upd_mobile');
            $message = $this->get('translator')->trans('An invalid exception occurred');
            $errorcl = 'alert-danger';
            return $this->render('account/mobile.html.twig',
                array('form'  => $form->createView(),'country' => $country_id,
                    'token'   => $token ,
                    'message' => $message,'errorcl' => $errorcl)
            );
        }
        catch (AccessDeniedException $ed)
        {
            $commFunct->setCsrfToken('account_upd_mobile');
            $session = new Session();
            $token   = $session->get('account_upd_mobile');
            $message = $this->get('translator')->trans($ed->getMessage());
            $errorcl = 'alert-danger';
            return $this->render('account/mobile.html.twig',
                array('form' => $form->createView(),'country' => $country_id,'message' => $message,'errorcl' => $errorcl, 'token' => $token  )
            );
        }
    }


    /**
     * @Route("/{_country}/{_locale}/account/fullnametest" , name="front_account_fullname_test" )
     * @return Response null
     */
    public function fullNametestAction(Request $request)
    {
        $response = new Response();
        $data     = $request->request->all();
        $var      = filter_var( $_POST['posted_data'] , FILTER_SANITIZE_STRING);
        return new Response($var);
    }

    /**
     * @Route("/{_country}/{_locale}/account/fullname" , name="front_account_fullname" )
     */
    public function fullNameAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('login',array('_country' => $request->cookies->get(AppConstant::COOKIE_COUNTRY), '_locale' => $request->cookies->get(AppConstant::COOKIE_LOCALE)));
        }
        $activityLog = $this->get('app.activity_log');
        $commFunct   = new FunctionsController();
        $commFunct->setContainer($this->container);
        
        $country_id  = $request->get('_country');
        $this->getUser()->getIktCardNo();
        //$restClient  = $this->get('app.rest_client')->IsAuthorized(true);
        $restClient  = $this->get('app.rest_client')->IsAdmin(true);
        $iktUserData = $this->get('session')->get('iktUserData');
        $iktCardNo   = $iktUserData['C_id'];
        $iktID_no    = $iktUserData['ID_no'];
        $ikt_cname   = trim($iktUserData['cname']);
        $form = $this->createForm(UpdateFullnameType::class, array() ,array(
            'extras' => array(
                'country'  => $country_id,
                'iktID_no' => $iktID_no
            )));
        try
        {
            $form->handleRequest($request);
            $data   = $request->request->all();
            // print_r($data['update_fullname']['fullname']);exit;
            if(!empty($data))
            {
                if($form->isValid())
                {
                    $errors = "";
                    $validate_data = array($data['update_fullname']['fullname_registered_iqamaid'],trim($data['update_fullname']['fullname']));
                    //echo '<br>';
                    $errors = $commFunct->validateSpecialCharacters($validate_data);
                    if($errors > 0)
                    {
                        $commFunct->setCsrfToken('account_upd_name');
                        $session = new Session();
                        $token   = $session->get('account_upd_name');
                        $message = $this->get('translator')->trans('Please provide valid data for name');
                        $errorcl = 'alert-danger';
                        return $this->render('account/fullname.html.twig',
                            array('form' => $form->createView(), 'message' => $message, 'token' => $token ,  'errorcl' => $errorcl)
                        );
                    }

                    $errorsComments = "";
                    $validate_data_Comm = array($data['update_fullname']['fullname']);
                    $errorsCommentsf = $commFunct->validateDataName($validate_data_Comm);
                    if($errorsCommentsf > 0)
                    {
                        $commFunct->setCsrfToken('account_upd_name');
                        $session = new Session();
                        $token   = $session->get('account_upd_name');

                        $message = $this->get('translator')->trans('Please provide valid data for name');
                        $errorcl = 'alert-danger';
                        return $this->render('account/fullname.html.twig',
                            array('form' => $form->createView(), 'message' => $message, 'token' => $token ,  'errorcl' => $errorcl)
                        );
                    }

                    $errorsComments = "" ;
                    $validate_data_Comm = array($data['update_fullname']['comment_fullname']);
                    $errorsComments = $commFunct->validateData($validate_data_Comm);
                    if($errorsComments > 0)
                    {
                        $commFunct->setCsrfToken('account_upd_name');
                        $session = new Session();
                        $token   = $session->get('account_upd_name');

                        $message = $this->get('translator')->trans('Please provide valid data');
                        $errorcl = 'alert-danger';
                        return $this->render('account/fullname.html.twig',
                            array('form' => $form->createView(), 'message' => $message, 'token' => $token ,  'errorcl' => $errorcl)
                        );
                    }
                    if ($commFunct->checkCsrfToken($form->get('token')->getData(), 'account_upd_name'))
                    {
                        // here we will add validation to the form
                        if (strip_tags($data['update_fullname']['fullname']) != "") {
                            $fullname_arr = explode(' ', strip_tags(trim($data['update_fullname']['fullname'])));
                            if (count($fullname_arr) < 2) {
                                $commFunct->setCsrfToken('account_upd_name');
                                $session = new Session();
                                $token = $session->get('account_upd_name');

                                $message = $this->get('translator')->trans('Name must be in two parts');
                                $errorcl = 'alert-danger';
                                return $this->render('account/fullname.html.twig', array('form' => $form->createView(),
                                    'message' => $message, 'errorcl' => $errorcl, 'token' => $token));
                            }
                        }
                        /***********************/
                        // here we will add validation to the form
                        if (strip_tags(trim($data['update_fullname']['fullname'])) == $ikt_cname ) {
                                $commFunct->setCsrfToken('account_upd_name');
                                $session = new Session();
                                $token   = $session->get('account_upd_name');
                                $message = $this->get('translator')->trans('Name cannot be same as current name');
                                $errorcl = 'alert-danger';
                                return $this->render('account/fullname.html.twig', array('form' => $form->createView(),
                                    'message' => $message, 'errorcl' => $errorcl, 'token' => $token));

                        }

                        /************************/
                        $val_fullname         = filter_var($data['update_fullname']['fullname'], FILTER_SANITIZE_STRING);
                        $val_comment_fullname = filter_var($data['update_fullname']['comment_fullname'], FILTER_SANITIZE_STRING);

                        $url = $request->getLocale() . '/api/update_user_name.json';
                        $form_data      =  array(
                            'C_id'      => $iktCardNo,
                            'field'     => 'cname',
                            'old_value' => $ikt_cname,
                            'new_value' => strip_tags(trim($val_fullname)),
                            'comments'  => strip_tags(trim($val_comment_fullname)),
                            'source'    => 'W',
                            'browser'   => $commFunct->getBrowserInfo(),
                        );
                        // version is not necessary from the website.

                        $postData = json_encode($form_data);
                        //print_r($postData);exit;
                        $request->get('_country');
                        $data = $restClient->restPostForm(AppConstant::WEBAPI_URL . $url, $postData, array('Country-Id' => strtoupper($request->get('_country'))));
                        // print_r($data);exit;
                        if ($data != "" && $data != null) {
                            $commFunct->setCsrfToken('account_upd_name');
                            $session = new Session();
                            $token   = $session->get('account_upd_name');
                            if ($data['success'] == true)
                            {
                                $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_FULLNAME_SUCCESS, $iktUserData['C_id'],
                                    $form_data['old_value'],$form_data['new_value'], array('iktissab_card_no' => $iktUserData['C_id'],
                                        'message' => $data['message'], 'session' => $iktUserData),$form_data['old_value'],$form_data['new_value'] );
                                $errorcl = 'alert-success';
                                return $this->render('account/fullname.html.twig', array('form' => $form->createView(), 'token' => $token, 'message' => $this->get('translator')->trans($data['message']), 'errorcl' => $errorcl));
                            }

                            if ($data['success'] == false)
                            {
                                if ($data['status'] == 1)
                                {
                                    $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_FULLNAME_PENDING, $iktUserData['C_id'],
                                        array('iktissab_card_no' => $iktUserData['C_id'], 'message' => $data['message'],
                                            'session' => $iktUserData , 'postedData' => $form_data),$form_data['old_value'],$form_data['new_value']);
                                    $errorcl = 'alert-danger';
                                    return $this->render('account/fullname.html.twig', array('form' => $form->createView(), 'token' => $token, 'message' => $this->get('translator')->trans($data['message']), 'errorcl' => $errorcl));
                                }
                                else
                                {
                                    $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_FULLNAME_ERROR, $iktUserData['C_id'], array('iktissab_card_no' => $iktUserData['C_id'], 'message' => $data['message'], 'session' => $iktUserData , 'postedData' => $form_data),$form_data['old_value'],$form_data['new_value']);
                                    $errorcl = 'alert-danger';
                                    return $this->render('account/fullname.html.twig', array('form' => $form->createView(), 'token' => $token, 'message' => $this->get('translator')->trans($data['message']), 'errorcl' => $errorcl));
                                }
                            }
                        }
                        else
                        {
                            $commFunct->setCsrfToken('account_upd_name');
                            $session = new Session();
                            $token = $session->get('account_upd_name');
                            $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_FULLNAME_ERROR,

                                array('iktissab_card_no' => $iktUserData['C_id'],
                                    'message' => $this->get('translator')->trans('Unable to update name'),
                                    'session' => $iktUserData, 'postedData' => $form_data),
                                $iktUserData['C_id'],$form_data['old_value'],$form_data['new_value']
                                );
                            $errorcl = 'alert-danger';
                            $message = $this->get('translator')->trans('Unable to update name');
                            return $this->render('account/fullname.html.twig', array('form' => $form->createView(), 'token' => $token, 'message' => $message, 'errorcl' => $errorcl));
                        }
                    }
                    else
                    {
                        $commFunct->setCsrfToken('account_upd_name');
                        $session = new Session();
                        $token = $session->get('account_upd_name');
                        $message = $this->get('translator')->trans('Unable to process your request at this time.Please try later');
                        $errorcl = 'alert-danger';
                        return $this->render('account/fullname.html.twig',
                            array('form' => $form->createView(), 'message' => $message, 'token' => $token, 'errorcl' => $errorcl )
                        );
                    }
                }
                else
                {
                    $commFunct->setCsrfToken('account_upd_name');
                    $session = new Session();
                    $token   = $session->get('account_upd_name');
                    $message = $this->get('translator')->trans('Unable to process your request at this time.Please try later');
                    $errorcl = 'alert-danger';
                    return $this->render('account/fullname.html.twig',
                        array('form' => $form->createView(), 'message' => $message, 'token' => $token ,  'errorcl' => $errorcl)
                    );
                }
            }
            else
            {
                $commFunct->setCsrfToken('account_upd_name');
                $session = new Session();
                $token   = $session->get('account_upd_name');
                $message = $this->get('translator')->trans('');
                $errorcl = 'alert-success';
                return $this->render('account/fullname.html.twig',
                    array('form' => $form->createView(), 'message' => $message, 'token' => $token ,
                        'errorcl' => $errorcl )
                );
            }
        }
        catch (\Exception $e)
        {
             $commFunct->setCsrfToken('account_upd_name');
             $session = new Session();
             $token   = $session->get('account_upd_name');
             // removed custom messages
             $message = $this->get('translator')->trans('Unable to update name');
             // $message = $this->get('translator')->trans($e->getMessage());
             $errorcl = 'alert-danger';
             return $this->render('account/fullname.html.twig',
                array( 'form' => $form->createView() ,  'message' => $message , 'token' => $token , 'errorcl' => $errorcl ));
        }
        catch (AccessDeniedException $ed)
        {
            $commFunct->setCsrfToken('account_upd_name');
            $session = new Session();
            $token   = $session->get('account_upd_name');
            $message = $this->get('translator')->trans($ed->getMessage());
            $errorcl = 'alert-danger';
            return $this->render('account/fullname.html.twig',
                array('form' => $form->createView(),'message' => $message, 'token' => $token, 'errorcl' => $errorcl)
            );
        }
    }




    /**
     * @Route("/{_country}/{_locale}/account/iqamassn" , name="front_account_iqamassn")
     */

    public function iqamaSNNAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY'))
        {
            return $this->redirectToRoute('login',array('_country' => $request->cookies->get(AppConstant::COOKIE_COUNTRY), '_locale' => $request->cookies->get(AppConstant::COOKIE_LOCALE)));
        }
        $activityLog = $this->get('app.activity_log');
        $commFunct   = new FunctionsController();
        $commFunct->setContainer($this->container);
        $country_id  = $request->get('_country');
        $language    = $request->getLocale();
        $restClient  = $this->get('app.rest_client')->IsAdmin(true);
        $iktUserData = $this->get('session')->get('iktUserData');
        //print_r($iktUserData);
        $iktCardNo   = $iktUserData['C_id'];
        $iktID_no    = $iktUserData['ID_no'];
        $user_country_id = $iktUserData['country_id'];

        $url         = $request->getLocale() . '/api/update_user_ssn.json';
        $form        = $this->createForm(IqamassnType::class, array() ,array(
                'extras'   => array(
                'country'  => $country_id,
                'iktID_no' => $iktID_no
            )));
        $form->handleRequest($request);
        AppConstant::WEBAPI_URL . $url;

        $data   = $request->request->all();
        try
        {
            if(!empty($data)) {
                // exit;
                if($form->isValid())
                {
                    $errors = "";
                    $validate_data = array($data['iqamassn']['iqamassn_registered'] , $data['iqamassn']['comment_iqamassn'] , $data['iqamassn']['iqamassn_new']['first']
                                        ,$data['iqamassn']['iqamassn_new']['second']);
                    $errors = $commFunct->validateData($validate_data);
                    if($errors > 0)
                    {
                        $commFunct->setCsrfToken('account_upd_iqamassn');
                        $session = new Session();
                        $token   = $session->get('account_upd_iqamassn');
                        $message = $this->get('translator')->trans('Please provide valid data');
                        $errorcl = 'alert-danger';
                        return $this->render('account/iqamassn.html.twig',
                            array('form'  => $form->createView(),
                                'token'   => $token,
                                'message' => $message, 'errorcl' => $errorcl));
                    }

                    if ($commFunct->checkCsrfToken(trim($data['iqamassn']['token']), 'account_upd_iqamassn'))
                    {
                        $iqamassn_registered = strip_tags($data['iqamassn']['iqamassn_registered']);
                        $iqamassn_new = strip_tags($data['iqamassn']['iqamassn_new']['first']);
                        $confirm_iqamassn_new = strip_tags($data['iqamassn']['iqamassn_new']['second']);
                        $comment_iqamassn = strip_tags($data['iqamassn']['comment_iqamassn']);
                        if ($iqamassn_new == $iqamassn_registered)
                        {
                            $commFunct->setCsrfToken('account_upd_iqamassn');
                            $session = new Session();
                            $token = $session->get('account_upd_iqamassn');
                            $message = $this->get('translator')->trans('New Iqama Id/SSN and old Iqama Id/SSN must not be same' . $country_id);
                            $errorcl = 'alert-danger';
                            return $this->render('account/iqamassn.html.twig',
                                array('form' => $form->createView(),
                                    'token' => $token,
                                    'message' => $message, 'errorcl' => $errorcl));
                        }
                        if ($iqamassn_new != $confirm_iqamassn_new)
                        {
                            $commFunct->setCsrfToken('account_upd_iqamassn');
                            $session = new Session();
                            $token = $session->get('account_upd_iqamassn');
                            $message = $this->get('translator')->trans('New Iqama Id/SSN and confirm new Iqama Id/SSN must be same' . $country_id);
                            $erorcl = 'alert-danger';
                            return $this->render('account/iqamassn.html.twig',
                                array('form' => $form->createView(), 'message' => $message,
                                    'token' => $token, 'errorcl' => $erorcl));
                        }

                        if ($country_id == 'sa') {
                            $validateIqama = $this->validateIqama($iqamassn_new);
                            $countery_id = $user_country_id;
                            $validateNation = $this->validateNationality($iqamassn_new, $countery_id);
                            if ($validateNation == false)
                            {
                                $commFunct->setCsrfToken('account_upd_iqamassn');
                                $session = new Session();
                                $token   = $session->get('account_upd_iqamassn');
                                $message = $this->get('translator')->trans('Invalid Iqama Id/SSN Numbersa');
                                $errorcl = 'alert-danger';
                                return $this->render('account/iqamassn.html.twig',
                                    array('form'  => $form->createView(),
                                        'token'   => $token,
                                        'message' => $message, 'errorcl' => $errorcl));
                            }
                            if ($validateIqama == false)
                            {
                                $commFunct->setCsrfToken('account_upd_iqamassn');
                                $session = new Session();
                                $token   = $session->get('account_upd_iqamassn');
                                $message = $this->get('translator')->trans('Invalid Iqama Id/SSN Numbersa');
                                $errorcl = 'alert-danger';
                                return $this->render('account/iqamassn.html.twig',
                                    array('form'  => $form->createView(), 'message' => $message,
                                        'token'   => $token,
                                        'errorcl' => $errorcl));
                            }
                        }
                        // set mobile number to 10 digits for webservice which needs 10 digits but when used 966 on website form
                        // then we are forcing user to enter 9 digits without 0 for KSA.
                        // mobile format for webservice
                        /*********************************/
                        $val_sanitize_iqamassn_new = filter_var($data['iqamassn']['iqamassn_new']['first'] , FILTER_SANITIZE_STRING);
                        $val_sanitize_comment_iqamassn = filter_var($data['iqamassn']['comment_iqamassn'] , FILTER_SANITIZE_STRING);
                        $form_data = array(
                            'C_id'      => $iktCardNo,
                            'field'     => 'iqamassn',
                            'old_value' => $iktID_no,
                            'new_value' => strip_tags($val_sanitize_iqamassn_new),
                            'comments'  => strip_tags($val_sanitize_comment_iqamassn),
                            'source'    => 'W',
                            'browser'   => $commFunct->getBrowserInfo()
                        );
                        $postData = json_encode($form_data);
                        $request->get('_country');
                        $data_rest = $restClient->restPostForm(AppConstant::WEBAPI_URL . $url, $postData, array('Country-Id' => strtoupper($request->get('_country'))));
                        if (!empty($data_rest))
                        {
                            if ($data_rest['success'] == true)
                            {
                                $commFunct->setCsrfToken('account_upd_iqamassn');
                                $session = new Session();
                                $token   = $session->get('account_upd_iqamassn');
                                // posted array is emty to clear the form after successful transction
                                $errorcl = 'alert-success';
                                $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_IQAMA_SUCCESS, $iktUserData['C_id'],
                                array('iktissab_card_no' => $iktUserData['C_id'],
                                        'message' => $data_rest['message'], 'session' => $iktUserData , 'postedData' => $form_data  ),
                                 $form_data['old_value'] , $form_data['new_value']);
                                $message = $this->get('translator')->trans($data_rest['message']);
                                // return $this->render('account/' . $country_file_ext . '/iqamassn.html.twig',
                                return $this->render('account/iqamassn.html.twig',
                                    array(
                                        'form'    => $form->createView(), 'message' => $message,
                                        'token'   => $token,
                                        'errorcl' => $errorcl)
                                );
                            }

                            if ($data_rest['success'] == false)
                            {
                                if ($data_rest['status'] == 1)
                                {
                                    $commFunct->setCsrfToken('account_upd_iqamassn');
                                    $session = new Session();
                                    $token = $session->get('account_upd_iqamassn');
                                    $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_IQAMA_PENDING, $iktUserData['C_id'],
                                            array('iktissab_card_no' => $iktUserData['C_id'],
                                            $form_data['old_value'] , $form_data['new_value'],
                                            'message' => $data_rest['message'], 'session' => $iktUserData , 'postedData' => $form_data ),
                                        $form_data['old_value'] , $form_data['new_value']);
                                    $message = $this->get('translator')->trans($data_rest['message']);
                                    $errorcl = 'alert-danger';
                                    return $this->render('account/iqamassn.html.twig',
                                        array('form'  => $form->createView(),
                                            'token'   => $token,
                                            'message' => $message, 'errorcl' => $errorcl)
                                    );
                                }
                                else
                                {
                                    $commFunct->setCsrfToken('account_upd_iqamassn');
                                    $session = new Session();
                                    $token = $session->get('account_upd_iqamassn');
                                    $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_IQAMA_ERROR, $iktUserData['C_id'],
                                    array(
                                            'iktissab_card_no' => $iktUserData['C_id'],
                                            'message'          => $data_rest['message'],
                                            'session'          => $iktUserData , 'postedData' => $form_data ),
                                             $form_data['old_value'] , $form_data['new_value']
                                    );
                                    $message = $this->get('translator')->trans($data_rest['message']);
                                    $errorcl = 'alert-danger';
                                    return $this->render('account/iqamassn.html.twig',
                                        array('form'  => $form->createView(), 'message' => $message,
                                            'token'   => $token,
                                            'errorcl' => $errorcl));
                                }
                            }
                        }
                        else
                        {
                            $commFunct->setCsrfToken('account_upd_iqamassn');
                            $session = new Session();
                            $token = $session->get('account_upd_iqamassn');
                            $message = $this->get('translator')->trans('Unable to update your information');
                            $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_IQAMA_ERROR, $iktUserData['C_id'],
                                array('iktissab_card_no' => $iktUserData['C_id'],
                                    'message' => $message, 'session' => $iktUserData),
                                $form_data['old_value'] , $form_data['new_value']
                            );
                            $errorcl = 'alert-danger';
                            return $this->render('account/iqamassn.html.twig',
                                array('form'  => $form->createView(), 'message' => $message,
                                    'token'   => $token,
                                    'errorcl' => $errorcl)
                            );
                        }
                    }
                    else
                    {
                        $commFunct->setCsrfToken('account_upd_iqamassn');
                        $session = new Session();
                        $token   = $session->get('account_upd_iqamassn');
                        $message = $this->get('translator')->trans('Unable to process your request at this time.Please try later');
                        $errorcl = 'alert-danger';
                        return $this->render('account/iqamassn.html.twig',
                            array('form'   => $form->createView(), 'message' => $message,
                                'token'    => $token,
                                'errorcl'  => $errorcl)
                        );
                    }
                }
                else
                {
                    $commFunct->setCsrfToken('account_upd_iqamassn');
                    $session = new Session();
                    $token = $session->get('account_upd_iqamassn');
                    $message = $this->get('translator')->trans('Unable to process your request at this time.Please try later');
                    $errorcl = 'alert-danger';
                    return $this->render('account/iqamassn.html.twig',
                        array('form'  => $form->createView(), 'message' => $message,
                            'token'   => $token,
                            'errorcl' => $errorcl)
                    );
                }
            }
            else
            {
                $commFunct->setCsrfToken('account_upd_iqamassn');
                $session = new Session();
                $token   = $session->get('account_upd_iqamassn');
                $message = $this->get('translator')->trans('');
                $errorcl = '';
                return $this->render('account/iqamassn.html.twig',
                    array('form'  => $form->createView(),'message' => $message,
                        'token'   => $token,
                        'errorcl' => $errorcl)
                );
            }
        }
        catch (\Exception $e)
        {
            $commFunct->setCsrfToken('account_upd_iqamassn');
            $session = new Session();
            $token   = $session->get('account_upd_iqamassn');
            $errorcl = 'alert-danger';
            return $this->render('account/iqamassn.html.twig',
                array('form'  => $form->createView(), 'iktData' => $iktUserData,
                    'token'   => $token,
                    'message' => $message, 'errorcl' => $errorcl)
            );
        }
        catch (AccessDeniedException $ed)
        {
            $commFunct->setCsrfToken('account_upd_iqamassn');
            $session = new Session();
            $token   = $session->get('account_upd_iqamassn');
            // echo $ed->getMessage();
            $message = $this->get('translator')->trans($ed->getMessage());
            $errorcl = 'alert-danger';
            return $this->render('account/iqamassn.html.twig',
                array('form'  => $form->createView(), 'iktData' => $iktUserData,
                    'message' => $message, 'errorcl' => $errorcl , 'token' => $token ,
                    
                ));
        }
    }



    /**
     * @Route("/{_country}/{_locale}/account/updatepassword" , name="front_account_updatepassword")
     * @param Request $request
     * @return Response
     */
    public function updatepasswordAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('login',array('_country' => $request->cookies->get(AppConstant::COOKIE_COUNTRY), '_locale' => $request->cookies->get(AppConstant::COOKIE_LOCALE)));
        }
        $activityLog  = $this->get('app.activity_log');
        $commFunct    = new FunctionsController();
        $commFunct->setContainer($this->container);
        $iktUserData  = $this->get('session')->get('iktUserData');
        $tokenStorage = $this->get('security.token_storage');
        $form = $this->createForm(UpdatePasswordType::class, array() ,array() );
        try
        {
            // exit;
            // get logged in user info
            // var_dump($this->getUser());
            // var_dump($iktUserData);
            $iktCardNo = $iktUserData['C_id'];
            $iktID_no  = $iktUserData['ID_no'];
            // form posted data
            /******/
            $em = $this->getDoctrine()->getManager();
            $userInfoLoggedIn = $em->getRepository('AppBundle:User')->find($iktCardNo);
            // $email = $userInfoLoggedIn->getEmail();
            $form->handleRequest($request);
            /******/
            // print_r($userInfoLoggedIn);
            $postData = $request->request->all();
            // print_r($postData);exit;
            if (!empty($postData))
            {
                /***********/
                // var_dump($objUser);
                // current is the logged in user password
                $objUser = $this->getUser();
                $password_current = $objUser->getPassword();
                $old_password = md5(trim($postData['update_password']['old_password']));
                $new_password = md5(trim($postData['update_password']['new_password']['first']));
                /***********/
                if($form->isValid())
                {
                    $errors = "";
                    $validate_data  = array($postData['update_password']['old_password'],$postData['update_password']['new_password']['first'],
                                            $postData['update_password']['new_password']['second']);
                    $errors         = $commFunct->validateDataPassword($validate_data);
                    if($errors > 0)
                    {
                        $commFunct->setCsrfToken('account_upd_password');
                        $session = new Session();
                        $token   = $session->get('account_upd_password');
                        $message = $this->get('translator')->trans('Please provide valid data');
                        $errorcl = 'alert-danger';
                        return $this->render('account/updatepassword.html.twig',
                            array('form1' => $form->createView(),'token' => $token,  'message' => $message , 'errorcl' => $errorcl));
                    }
                    if($commFunct->checkCsrfToken(strip_tags($postData['update_password']['token']) , 'account_upd_password' )) {
                        if ($password_current != $old_password) {
                            $commFunct->setCsrfToken('account_upd_password');
                            $session    = new Session();
                            $token      = $session->get('account_upd_password');
                            $message = $this->get('translator')->trans('Please enter correct old password');
                            $errorcl = 'alert-danger';
                            return $this->render('account/updatepassword.html.twig',
                                array('form1' => $form->createView(), 'token' => $token, 'message' => $message, 'errorcl' => $errorcl));
                        }
                        if ($new_password == $old_password) {
                            $commFunct->setCsrfToken('account_upd_password');
                            $session = new Session();
                            $token = $session->get('account_upd_password');
                            $message = $this->get('translator')->trans('Your new and old password must not be the same');
                            $errorcl = 'alert-danger';
                            return $this->render('account/updatepassword.html.twig',
                                array('form1' => $form->createView(), 'token' => $token, 'message' => $message, 'errorcl' => $errorcl));
                        }

                        $new_md5_password = md5(strip_tags($form->get('new_password')->getData()));
                        // here when the offline db password is update then udate online
                        // $password_changed = $this->updatePasswordOffline($request, $new_md5_password);
                        // Commentingout this code to update the offline due to proxy server implementation
                        // assuming the offline db is updated as there is no need of it now so returning TRUE

                        $password_changed = true;
                        if ($password_changed == true) {
                            $userInfoLoggedIn->setPassword($new_md5_password);
                            $em = $this->getDoctrine()->getManager();
                            $em->persist($userInfoLoggedIn);
                            $em->flush();
                            $tokenStorage->getToken()->getUser()->setPassword($new_md5_password);
                            $messageLog = $this->get('translator')->trans('Your password is updated successfully');
                            $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_PASSWORD_SUCCESS, $iktUserData['C_id'],
                                array('iktissab_card_no' => $iktUserData['C_id'], 'message' => $messageLog,
                                    'session' => $iktUserData , 'postedData' => array( $form->get('new_password')->getData()) ),
                                $old_password,$new_password
                                );
                            $message = $this->get('translator')->trans('Your password is updated successfully');
                            // update password in the offline database
                            $commFunct->setCsrfToken('account_upd_password');
                            $session = new Session();
                            $token = $session->get('account_upd_password');
                            $errorcl = 'alert-success';
                            return $this->render('account/updatepassword.html.twig',
                                array('form1' => $form->createView(), 'token' => $token, 'message' => $message, 'errorcl' => $errorcl));
                        } else {
                            $commFunct->setCsrfToken('account_upd_password');
                            $session = new Session();
                            $token = $session->get('account_upd_password');
                            $messageLog = $this->get('translator')->trans('Unable to update record');
                            $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_PASSWORD_ERROR, $iktUserData['C_id'],
                            array('iktissab_card_no' => $iktUserData['C_id'], 'message' => $messageLog, 'session' => $iktUserData),
                                $old_password,$new_password);
                            $message = $this->get('translator')->trans('Unable to update record');
                            // update password in the offline database
                            $errorcl = 'alert-danger';
                            return $this->render('account/updatepassword.html.twig',
                                array('form1' => $form->createView(), 'token' => $token, 'message' => $message, 'errorcl' => $errorcl));

                        }
                    }
                    else
                    {
                        $commFunct->setCsrfToken('account_upd_password');
                        $session = new Session();
                        $token   = $session->get('account_upd_password');
                        $message = $this->get('translator')->trans('Unable to process your request at this time.Please try later');
                        $errorcl = 'alert-danger';
                        $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_PASSWORD_ERROR, $iktUserData['C_id'],
                            array('iktissab_card_no' => $iktUserData['C_id'], 'message' => $message, 'session' => $iktUserData),
                            $old_password,$new_password);
                        return $this->render('account/updatepassword.html.twig',
                            array('form1' => $form->createView(),'token' => $token,  'message' => $message , 'errorcl' => $errorcl));
                    }
                }
                else
                {
                    $commFunct->setCsrfToken('account_upd_password');
                    $session = new Session();
                    $token   = $session->get('account_upd_password');
                    $message = $this->get('translator')->trans('Unable to update record');
                    $errorcl = 'alert-danger';
                    $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_PASSWORD_ERROR, $iktUserData['C_id'],

                        array('iktissab_card_no' => $iktUserData['C_id'], 'message' => $message, 'session' => $iktUserData),
                        $old_password,$new_password
                        );
                    return $this->render('account/updatepassword.html.twig',
                        array('form1' => $form->createView(),'token' => $token,  'message' => $message , 'errorcl' => $errorcl));
                }
            }
            else
            {
                $commFunct->setCsrfToken('account_upd_password');
                $session = new Session();
                $token   = $session->get('account_upd_password');
                $message = '';
                $errorcl = 'alert-danger';
                return $this->render('account/updatepassword.html.twig',
                    array('form1' => $form->createView(), 'token' => $token, 'message' => $message , 'errorcl' => $errorcl));
            }
        }
        catch (\Exception $e)
        {
            $commFunct->setCsrfToken('account_upd_password');
            $session = new Session();
            $token   = $session->get('account_upd_password');
            $errorcl = 'alert-danger';
            $message = $this->get('translator')->trans('An invalid exception occurred');
            return $this->render('account/updatepassword.html.twig',
                array('form1' => $form->createView(), 'token' => $token,  'message' => $message , 'errorcl' => $errorcl));
        }
    }

    


    /**
     * @Route("/{_country}/{_locale}/account/update" , name="account_update")
     * @param Request $request
     * @return Response
     */
    public function updateProfileAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('login',array('_country' => $request->cookies->get(AppConstant::COOKIE_COUNTRY), '_locale' => $request->cookies->get(AppConstant::COOKIE_LOCALE)));
        }

        $commFunct = new FunctionsController();
        $commFunct->setContainer($this->container);
        try
        {
            $user = $this->get('session')->get('iktUserData');
            $restClient = $this->get('app.rest_client')->IsAdmin(true);
            $url = $request->getLocale() . '/api/cities_areas_and_jobs.json';
            $cities_jobs_area = $restClient->restGet(AppConstant::WEBAPI_URL . $url, array('Country-Id' => strtoupper($request->get('_country'))));
            if(!empty($cities_jobs_area) && $cities_jobs_area !="")
            {
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
                    if (!isset($value['name'])) {
                        continue;
                    }
                    $areasArranged[trim($value['name'])] = trim($value['name']);
                }
                $areasArranged['-1'] = '-1';
                $areas = $this->json($cities_jobs_area['areas']);
            }
            else
            {
                $citiesArranged = null;
                $jobsArranged   = null;
                $areasArranged  = null;
            }
            //echo "stored-bd ".$user['birthdate'];
            $birthdate = explode('/', $user['birthdate']);
            if ($birthdate[2] > 1850) {
                $dateType = 'g';
                $date     = \DateTime::createFromFormat("d/m/Y", $user['birthdate']);
                $dob      = $date->format(AppConstant::DATE_FORMAT);
                $dob_old  = $date->format(AppConstant::DATE_FORMAT_DOB);
                $dob_h    = '';
            } else {
                $dateType = 'h';
                $dob      = '';
                $date     = \DateTime::createFromFormat("d/m/Y", $user['birthdate']);
                $dob_h    = $date->format(AppConstant::DATE_FORMAT);
                $dob_old  = $date->format(AppConstant::DATE_FORMAT_DOB);
            }
            $dataArr = array(
                'date_type'       => $dateType,
                'job_no'          => $user['job_no'],
                'maritial_status' => $user['marital_status_en'][0],
                'language'        => $user['lang'],
                'city_no'         => $user['city_no'],
                'pur_group'       => $user['pur_grp'],
                'dob'             => new \DateTime($dob),
                'dob_h'           => new \DateTime($dob_h),
            );
            $form = $this->createForm(IktUpdateType::class, $dataArr, array(
                    'additional'  => array(
                        'areas'   => $areasArranged,
                        'cities'  => $citiesArranged,
                        'jobs'    => $jobsArranged,
                    )
                )
            );
            $reference_year = array('gyear' => 2017, 'hyear' => 1438);
            $current_year   = date('Y');
            $islamicYear    = ($current_year - $reference_year['gyear']) + $reference_year['hyear'];
            $form->handleRequest($request);
            $data  = $request->request->all();
            // print_r($data);
            $pData = $form->getData();
            // print_r($pData);
            if ($data)
            {
                if ($commFunct->checkCsrfToken($form->get('token')->getData(), 'account_upd_profile'))
                {
                    if ($pData['date_type'] == 'g')
                    {
                        $dateBirth = $pData['dob']->format('Y-m-d');
                    }
                    else
                    {
                        $dateBirth = $pData['dob_h']->format('Y-m-d');
                    }
                    // echo $dateBirth;exit;
                    if($data['ikt_update']['area_no'] == '-1'){
                        $ikt_update_area_text = strip_tags($data['ikt_update']['area_text']);
                    }
                    else
                    {
                        $ikt_update_area_text = strip_tags($data['ikt_update']['area_no']);
                    }


                    $profileFields = array(
                        "birthdate"     => array(
                            'old_value' => $dob_old,
                            'new_value' => $dateBirth
                        ),
                        "Marital_status"=> array(
                            'old_value' => $user['marital_status_en'][0],
                            'new_value' => $pData['maritial_status']
                        ),
                        "job_no"        => array(
                            'old_value' => $user['job_no'],
                            'new_value' => $pData['job_no']),
                        "city_no"       => array(
                            'old_value' => $user['city_no'],
                            'new_value' => $pData['city_no']),
                        "area"          => array(
                            'old_value' => $user['area'],
                            'new_value' => $ikt_update_area_text),
                        "lang"          => array(
                            'old_value' => $user['lang'],
                            'new_value' => $pData['language']),
                        "pur_grp"       => array(
                            'old_value' => $user['pur_grp'],
                            'new_value' => ($pData['pur_group'])),
                    );
                    // filtering data
                    // print_r($profileFields);
                    $errors_profile = "";
                    $validate_data = array(
                        $profileFields['birthdate']['new_value'] ,
                        $profileFields['Marital_status']['new_value'] ,
                        $profileFields['job_no']['new_value'] ,
                        $profileFields['city_no']['new_value'] ,
                        $profileFields['lang']['new_value'] ,
                        $profileFields['pur_grp']['new_value']
                    );
                    $errors_profile = $commFunct->validateData($validate_data);
                    if($errors_profile > 0)
                    {
                        $commFunct->setCsrfToken('account_upd_profile');
                        $session = new Session();
                        $token   = $session->get('account_upd_profile');
                        $message = $this->get('translator')->trans('Please provide valid data');
                        return $this->render('/account/update.html.twig',
                            array
                            (
                                'form'        => $form->createView(),
                                'areas'       => $areas,
                                'errorcl'     => 'alert-danger',
                                'islamicyear' => $islamicYear,
                                'message'     => $message,
                                'token'       => $token,
                                'date_type'   => $dateType,
                            )
                        );
                    }
                    // now pass only those fields which are changed and are not empty
                    $count = 0;
                    foreach ($profileFields as $key => $val) {
                        if ($val['new_value']  != '' && ($val['new_value'] != $val['old_value'])) {
                            $form_data[$count] = array(
                                'C_id'         => $user['C_id'],
                                'field'        => $key,
                                'new_value'    => strip_tags($val['new_value']),
                                'old_value'    => strip_tags($val['old_value']),
                                'comments'     => 'Update registered user details for field ' . $key,
                                'source'       => "W",
                                'browser'      => $commFunct->getBrowserInfo()
                            );
                            $count++;
                        }
                    }

                    if(!empty($form_data))
                    {
                        $activityLog = $this->get('app.activity_log');
                        $postData = json_encode($form_data);
                        $url = $request->getLocale() . '/api/update_user_detail.json';
                        $data = $restClient->restPostForm(AppConstant::WEBAPI_URL . $url, $postData, array('Country-Id' => strtoupper($request->get('_country'))));
                        // print_r($postData);
                        // print_r($data);exit;
                        if ($data['success'] == true)
                        {
                            // we need to update the session data to reflect the
                            // changes after profile update
                            $restClient = $this->get('app.rest_client')->IsAdmin(true);
                            $url = $request->getLocale() . '/api/' . $this->getUser()->getIktCardNo() . '/userinfo.json';
                            // echo AppConstant::WEBAPI_URL.$url;
                            $data_user = $restClient->restGetForm(AppConstant::WEBAPI_URL . $url, array('Country-Id' => strtoupper($request->get('_country'))));
                            //print_r($data);
                            if ($data_user['status'] == 1) {
                                // response is ok from services
                                if ($data_user['success'] == true) {
                                    // data is also present from services
                                    $this->get('session')->set('iktUserData', $data_user['user']);
                                }
                            }
                            // print_r($form_data);
                            $var_counter = 0;
                            foreach($form_data as $val)
                            {
                                $val_data = $val['field'];
                                if($val_data == 'lang')
                                {
                                    $val_data = 'Language';
                                    $lang_array = array('Select Language' => '', 'Arabic' => 'A', 'English' => 'E');
                                    $val['new_value'] = array_search($val['new_value'], $lang_array);
                                    $val['old_value'] = array_search($val['old_value'], $lang_array);
                                }
                                else if($val_data == "Marital_status")
                                {
                                    $val_data = 'Marital status';
                                    $marital_array = array('Single' => AppConstant::SINGLE, 'Married' => AppConstant::MARRIED,
                                        'Widow' => AppConstant::WIDOW, 'Divorce' => AppConstant::DIVORCE);
                                    $val['new_value'] = array_search($val['new_value'], $marital_array);
                                    $val['old_value'] = array_search($val['old_value'], $marital_array);
                                }
                                else if($val_data == 'pur_grp')
                                {
                                    $val_data  = 'Purchase Group';
                                    $pur_array = array( 'Husband'  => AppConstant::HUSBAND,
                                                        'Wife'     => AppConstant::WIFE, 'Children'  => AppConstant::CHILDREN,
                                                        'Relative' => AppConstant::RELATIVE, 'Applicant' => AppConstant::APPLICANT,
                                                        'Servent'  => AppConstant::SERVENT);
                                    $val['new_value'] = array_search($val['new_value'], $pur_array);
                                    $val['old_value'] = array_search($val['old_value'], $pur_array);
                                }
                                else if($val_data == 'job_no')
                                {
                                    $val_data = 'Job Title';
                                    $val['old_value'] = array_search($user['job_no'], $jobsArranged);
                                    $val['new_value'] = array_search($profileFields['job_no']['new_value'], $jobsArranged);
                                }
                                else if($val_data == 'city_no')
                                {
                                    $val_data = 'City Name';
                                    $val['old_value'] = array_search($user['city_no'], $citiesArranged);
                                    $val['new_value'] = array_search($profileFields['city_no']['new_value'], $citiesArranged);
                                }
                                else
                                {
                                    $val_data = $val['field'];
                                }
                                $activityLog->logEvent($val_data, $user['C_id'],
                                    $form_data[$var_counter],
                                    $val['new_value'] , $val['old_value']
                                    );
                                $var_counter++;
                            }
                            $message = $this->get('translator')->trans('Profile has been updated');
                            $errorcl = 'alert-success';
                            $commFunct->setCsrfToken('account_upd_profile');
                            $session = new Session();
                            $token   = $session->get('account_upd_profile');
                            return $this->render('/account/update.html.twig',
                                array(
                                    'form'        => $form->createView(),
                                    'areas'       => $areas,
                                    'errorcl'     => $errorcl,
                                    'islamicyear' => $islamicYear,
                                    'message'     => $message,
                                    'token'       => $token,
                                    'date_type'   => $dateType,
                                )
                            );
                        }
                        else
                        {
                            $message = $this->get('translator')->trans('Unable to update record');
                            $errorcl = 'alert-danger';
                            $commFunct->setCsrfToken('account_upd_profile');
                            $session = new Session();
                            $token   = $session->get('account_upd_profile');
                            return $this->render('/account/update.html.twig',
                                array(
                                    'form'          => $form->createView(),
                                    'areas'         => $areas,
                                    'errorcl'       => $errorcl,
                                    'islamicyear'   => $islamicYear,
                                    'message'       => $message,
                                    'token'         => $token,
                                    'date_type'     => $dateType,
                                )
                            );
                        }
                    }
                    else
                    {
                        $message = $this->get('translator')->trans('Nothing to update');
                        $errorcl = 'alert-danger';
                        $commFunct->setCsrfToken('account_upd_profile');
                        $session = new Session();
                        $token = $session->get('account_upd_profile');
                        return $this->render('/account/update.html.twig',
                            array(
                                'form'          => $form->createView(),
                                'areas'         => $areas,
                                'errorcl'       => $errorcl,
                                'islamicyear'   => $islamicYear,
                                'message'       => $message,
                                'token'         => $token,
                                'date_type'     => $dateType,
                            )
                        );
                    }
                }
                else
                {
                    $commFunct->setCsrfToken('account_upd_profile');
                    $session = new Session();
                    $token   = $session->get('account_upd_profile');
                    $message = $this->get('translator')->trans('Unable to process your request at this time.Please try later');
                    return $this->render('/account/update.html.twig',
                        array
                        (
                            'form'        => $form->createView(),
                            'areas'       => $areas,
                            'errorcl'     => 'alert-danger',
                            'islamicyear' => $islamicYear,
                            'message'     => $message,
                            'token'       => $token,
                            'date_type'   => $dateType,
                        )
                    );
                }
            }
            else
            {
                $commFunct->setCsrfToken('account_upd_profile');
                $session = new Session();
                $token   = $session->get('account_upd_profile');
                $message = $this->get('translator')->trans('');
                return $this->render('/account/update.html.twig',
                        array
                        (
                            'form'        => $form->createView(),
                            'areas'       => $areas,
                            'errorcl'     => 'alert-danger',
                            'islamicyear' => $islamicYear,
                            'message'     => $message,
                            'token'       => $token,
                            'date_type'   => $dateType,
                        )
                );
            }
        }
        catch(\Exception $e)
        {
            $commFunct->setCsrfToken('account_upd_profile');
            $session = new Session();
            $token   = $session->get('account_upd_profile');
            // echo $e->getMessage();
            $message = $this->get('translator')->trans('An invalid exception occurred');
            return $this->render('account/error.html.twig',
                array
                (
                    'message' => $message,
                    'errorcl' => 'alert-danger',
                    'token'   => $token,
                )
            );
        }
        catch(AccessDeniedException $ed)
        {
            $message = $this->get('translator')->trans($ed->getMessage());
            $commFunct->setCsrfToken('account_upd_profile');
            $session = new Session();
            $token   = $session->get('account_upd_profile');
            return $this->render('account/error.html.twig',
                array
                (
                    'message' => $message,
                    'errorcl' => 'alert-danger',
                    'token'   => $token,
                )
            );
        }
    }

    /**
     * @Route("/{_country}/{_locale}/account/test1234")
     * @param Request $request
     * @return Response
     */

    public function test1234Action(Request $request){

        $message = \Swift_Message::newInstance()
            ->addTo('sa.aspire@gmail.com')
            ->addFrom($this->container->getParameter('mailer_user'))
            ->setSubject(AppConstant::EMAIL_SUBJECT);
        $message->setBody(
            $this->container->get('templating')->render(':email-templates/forgot_password:forgot_password.html.twig', [
                'email' => 'sa.aspire@gmail.com',
                'link' => 'asdf'
            ]),
            'text/html'
        );
        $this->get('mailer')->send($message);
        $message = \Swift_Message::newInstance();
        $message->addTo('sa.aspire@gmail.com')
            ->addFrom($this->getParameter('mailer_user'))
            ->setSubject(AppConstant::EMAIL_SUBJECT)
            ->setBody(
                $this->renderView(':email-templates/customers:new-account-creation.html.twig', ['customer' => 'adadsf', 'email' => $this->get('session')->get('email'), 'password' => $this->get('session')->get('password_unmd5')]),
                'text/html'
            );
        $this->get('mailer')->send($message);
        echo '==>'.$message = $this->get('translator')->trans('lang-E');
        return $this->render('account/error.html.twig',
            array
            (
                'message' => $message,
                'errorcl' => 'alert-danger'
            )
        );
    }


    /**
     * @Route("/{_country}/{_locale}/account/salem")
     * @param Request $request
     * @return Response
     */
    public function salemAction(Request $request){
        $form = $this->createFormBuilder(null, ['attr'=>['novalidate'=>'novalidate']])
            ->add('iqamaid', TextType::class, ['label'=>"Iqama/SSN Number", 'attr'=>['disabled'=>'disabled']])
            ->add('new_iktissab_id', RepeatedType::class, [
                'type' => TextType::class,
                'invalid_message' => 'Iktissab fields must match',
                'required' => true,
                'first_options' => array('label' => 'New Iktissab number'),
                'second_options' => array('label' => 'Confirm Iktissab Number'),
                'constraints' => array(
                    new NotBlank(array('message' => 'This field is required')),
                    new Regex(array('pattern'=>'/^[0-9]{8}$/', 'message'=> 'Invalid Iktissab number')))
            ])
            ->add('comment', TextareaType::class, ['label'=>'Comments', 'constraints'=>[new NotBlank()]])
            ->add('submit', SubmitType::class)
            ->getForm();
        $postData = $request->request->all();
        if(!empty($postData)){
            $form->submit($postData['form'], ['method'=>'POST'], false);
            if($form->isValid()){
                echo "Valid";
                die();
            }
        }


        return $this->render('account/salem.html.twig',
            array('form1'=>$form->createView())
        );
    }

    /**
     * @Route("/{_country}/{_locale}/account/missingcard" , name="front_account_missingcard")
     * @param Request $request
     * @return Response
     */
    public function missingCardAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('login',array('_country' => $request->cookies->get(AppConstant::COOKIE_COUNTRY), '_locale' => $request->cookies->get(AppConstant::COOKIE_LOCALE)));
        }
        $activityLog = $this->get('app.activity_log');
        $commFunct = new FunctionsController();
        $commFunct->setContainer($this->container);
        $restClient  = $this->get('app.rest_client')->IsAdmin(true);
        $iktUserData = $this->get('session')->get('iktUserData');
        $country_id  = $this->getCountryId($request);
        //  var_dump($iktUserData);
        $posted = array();
        $iktCardNo    = $iktUserData['C_id'];
        $iktID_no     = $iktUserData['ID_no'];
        $iktMobile_no = $iktUserData['mobile'];
        $language     = $request->getLocale();
        //  echo '===='.$this->get('session')->get('userSelectedCountry');
        $form = $this->createForm(MissingCardType::class, array() ,
                array('extras' => array('iktID_no'  => $iktID_no, 'country' => $country_id)
            ));
        $form->handleRequest($request);
        try
        {
            $data = $request->request->all();
            $url  = $request->getLocale() . '/api/update_lost_card.json';
            if(!empty($data))
            {
                /************************/
                if($form->isValid())
                {
                    $errors = "";
                    $validate_data = array($data['missing_card']['new_iktissab_id']['first'] , $data['missing_card']['comment_missingcard'],
                    $data['missing_card']['new_iktissab_id']['second']);
                    $errors = $commFunct->validateData($validate_data);
                    if($errors > 0)
                    {
                        $commFunct->setCsrfToken('account_upd_missingcard');
                        $session = new Session();
                        $token   = $session->get('account_upd_missingcard');
                        $message = $this->get('translator')->trans('Please provide valid data');
                        $error   = 'alert-danger';
                        return $this->render('account/missingcard.html.twig',
                            array('form'    => $form->createView(),
                                'token'     => $token,
                                'message'   => $message, 'errorcl' => $error)
                        );
                    }
                    if ($commFunct->checkCsrfToken(strip_tags($data['missing_card']['token']), 'account_upd_missingcard'))
                    {
                        if ($data['missing_card']['new_iktissab_id']['first'] == $iktCardNo)
                        {
                            $commFunct->setCsrfToken('account_upd_missingcard');
                            $session = new Session();
                            $token = $session->get('account_upd_missingcard');
                            $message = $this->get('translator')->trans('New Iktissab id and old Iktissab id must not be same');
                            $error = 'alert-danger';
                            return $this->render('account/missingcard.html.twig',
                                array('form'  => $form->createView(),
                                    'token'   => $token,
                                    'message' => $message, 'errorcl' => $error)
                            );
                        }
                        /************************/
                        $val_sanitize_new_iktissab_id = filter_var($data['missing_card']['new_iktissab_id']['first'] , FILTER_SANITIZE_STRING );
                        $val_sanitize_comment_missingcard = filter_var($data['missing_card']['comment_missingcard'] , FILTER_SANITIZE_STRING );

                        $form_data = array (
                            'ID_no'     => $iktID_no,
                            'field'     => 'lostcard',
                            'old_value' => $iktCardNo,
                            'new_value' => strip_tags($val_sanitize_new_iktissab_id),
                            'source'    => "W",
                            'comments'  => strip_tags($val_sanitize_comment_missingcard),
                            'mobile'    => $iktMobile_no,
                            'browser'   => $commFunct->getBrowserInfo(),
                        );

                        $postData = json_encode($form_data);
                        $request->get('_country');
                        $data = "";
                        $data = $restClient->restPostForm(AppConstant::WEBAPI_URL . $url, $postData, array('Country-Id' => strtoupper($request->get('_country'))));
                        // dump($data); exit; // return data fromt the webservice
                        if (!empty($data))
                        {
                            if ($data['success'] == true)
                            {
                                $commFunct->setCsrfToken('account_upd_missingcard');
                                $session = new Session();
                                $token = $session->get('account_upd_missingcard');
                                // posted array is emty to clear the form after successful transction
                                $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_MISSINGCARD_SUCCESS, $iktUserData['C_id'],

                                    array('iktissab_card_no' => $iktUserData['C_id'], 'message' => $data['message'],
                                        'session' => $iktUserData , 'postData' => $form_data  ),
                                    $form_data['old_value'], $form_data['new_value']
                                    );
                                $message = $this->get('translator')->trans($data['message']);
                                $error   = 'alert-success';
                                return $this->render('account/missingcard.html.twig',
                                    array('form' => $form->createView(), 'token' => $token, 'message' => $message, 'errorcl' => $error)
                                );
                            }
                            elseif ($data['success'] == false)
                            {
                                if ($data['status'] == 1)
                                {
                                    $commFunct->setCsrfToken('account_upd_missingcard');
                                    $session = new Session();
                                    $token = $session->get('account_upd_missingcard');
                                    $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_MISSINGCARD_PENDING, $iktUserData['C_id'],

                                        array('iktissab_card_no' => $iktUserData['C_id'], 'message' => $data['message'], 'session' => $iktUserData ,
                                            'postData' => $form_data),
                                        $form_data['old_value'], $form_data['new_value']
                                        );
                                    $message = $this->get('translator')->trans($data['message']);
                                    $error = 'alert-danger';
                                    return $this->render('account/missingcard.html.twig',
                                        array('form' => $form->createView(), 'token' => $token, 'message' => $message, 'errorcl' => $error)
                                    );
                                }
                                else
                                {
                                    $commFunct->setCsrfToken('account_upd_missingcard');
                                    $session = new Session();
                                    $token   = $session->get('account_upd_missingcard');
                                    // Here INVALID_DATA  will come with status 0
                                    $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_MISSINGCARD_ERROR, $iktUserData['C_id'],
                                        array('iktissab_card_no' => $iktUserData['C_id'], 'message' => $data['message'], 'session' => $iktUserData ,
                                            'postData' => $form_data),
                                        $form_data['old_value'], $form_data['new_value']
                                        );
                                    $message = $this->get('translator')->trans($data['message']);
                                    $error = 'alert-danger';
                                    return $this->render('account/missingcard.html.twig',
                                        array('form' => $form->createView(), 'token' => $token, 'message' => $message, 'errorcl' => $error)
                                    );
                                }
                            }
                            else
                            {
                                $commFunct->setCsrfToken('account_upd_missingcard');
                                $session = new Session();
                                $token = $session->get('account_upd_missingcard');
                                $message = $this->get('translator')->trans('');
                                $error = '';
                                return $this->render('account/missingcard.html.twig',
                                    array('form' => $form->createView(), 'token' => $token, 'message' => $message, 'errorcl' => $error));
                            }
                        }
                        else
                        {
                            $commFunct->setCsrfToken('account_upd_missingcard');
                            $session = new Session();
                            $token   = $session->get('account_upd_missingcard');
                            $message = $this->get('translator')->trans('Unable to update record');
                            $error = 'alert-danger';
                            return $this->render('account/missingcard.html.twig',
                                array('form' => $form->createView(), 'token' => $token, 'message' => $message, 'errorcl' => $error)
                            );
                        }
                    }
                    else
                    {
                        $commFunct->setCsrfToken('account_upd_missingcard');
                        $session = new Session();
                        $token = $session->get('account_upd_missingcard');
                        $message = $this->get('translator')->trans('Unable to process your request at this time.Please try later');
                        $error = 'alert-danger';
                        return $this->render('account/missingcard.html.twig',
                            array('form' => $form->createView(), 'token' => $token, 'message' => $message, 'errorcl' => $error)
                        );
                    }
                }
                else
                {
                    $commFunct->setCsrfToken('account_upd_missingcard');
                    $session = new Session();
                    $token = $session->get('account_upd_missingcard');
                    $message = $this->get('translator')->trans('Unable to process your request at this time.Please try later');
                    $error = 'alert-danger';
                    return $this->render('account/missingcard.html.twig',
                        array('form' => $form->createView(), 'token' => $token, 'message' => $message, 'errorcl' => $error)
                    );
                }
            }
            else
            {
                $commFunct->setCsrfToken('account_upd_missingcard');
                $session = new Session();
                $token   = $session->get('account_upd_missingcard');
                $message = $this->get('translator')->trans('');
                $error = '';
                return $this->render('account/missingcard.html.twig',
                    array('form'  => $form->createView(),
                        'token'   => $token ,
                        'message' => $message, 'errorcl' => $error)
                );
            }
        }
        catch(\Exception $e)
        {
            $commFunct->setCsrfToken('account_upd_missingcard');
            $session   = new Session();
            $token     = $session->get('account_upd_missingcard');
            $message   = $e->getMessage();
            $message   = $this->get('translator')->trans('Unable to process your request at this time.Please try later') ;
            $error     = 'alert-danger';
            return $this->render('account/missingcard.html.twig',
                array( 'form'   => $form->createView(),
                    'token'     => $token ,
                    'message'   => $message, 'errorcl' => $error)
            );
        }
        catch(AccessDeniedException $ed)
        {
            $commFunct->setCsrfToken('account_upd_missingcard');
            $session = new Session();
            $token   = $session->get('account_upd_missingcard');
            $message = $ed->getMessage();
            $message = $this->get('translator')->trans('Unable to process your request at this time.Please try later');
            $errorcl = 'alert-danger';
            return $this->render('account/missingcard.html.twig',
                array('form' => $form->createView(), 'token' => $token , 'message' => $message, 'errorcl' => $errorcl)
            );
        }
    }


    /**
     * @Route("/{_country}/{_locale}/account/new")
     */

    public function newAction(Request $request)
    {
        $data = array('name' => 'Abdul basit',
            'age' => '44',
            'address' => 'office colony ',
            'houseno' => ''
        );
        $restClient = $this->get('app.rest_client');
        $url = "http://www.othaimmarkets.com/webservices/api/v2";

        $postData = "{\"service\":\"IktissabPromotions\"} ";
        $data = $restClient->restPostForm($url,
            $postData, array('input-content-type' => "text/json" , 'output-content-type' => "text/json" ,
                'language' => 'en'
            ));
        //var_dump($data);
        $products = json_decode($data);
        //var_dump(json_decode($data));
        //echo "<br>";
        //var_dump($products->products[0]);

        $pro = $products->products;

        // var_dump($pro);
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
        var_dump($listing);
        //echo $this->createJson($data);
        return new Response();
    }

    private function createJson($data)
    {
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);
        //echo "i am in the new action";
        $data_decode_json = $data;
        $jsonContent = $serializer->serialize($data_decode_json, 'json');
        echo  $jsonContent;
    }

    private function getCountryId(Request $request)
    {
        return  $request->get('_country');
    }

    /**
     * @Route("/account/dateofbirthconverter/{datetoconvert}/{conversionto}")
     * @param Request $request
     * @return Response
     */

    public function dateOfBirhtConverterAction(Request $request,$datetoconvert , $conversionto)
    {
        try {
            $DateConv       = new HijriGregorianConvert();
            $conversionto   = $request->get('conversionto');
            if ($datetoconvert) {
                if ($conversionto == 'Hijri') {
                    /********/
                    $format = "YYYY-MM-DD";
                    $date = $datetoconvert; //"2017/03/12";
                    $result = $DateConv->GregorianToHijri($date, $format);
                    $result = explode("-", $result);
                    $result = $result[2] . '-' . $result[1] . '-' . $result[0];
                    return New Response($result);
                } else {
                    $format = "YYYY-MM-DD";
                    $date = $datetoconvert; // "1400/03/22";
                    $result = $DateConv->HijriToGregorian($date, $format);
                    $result = explode("-", $result);
                    $result = $result[2] . '-' . $result[1] . '-' . $result[0];
                    return New Response($result);
                }
            }
        }
        catch (\Exception $e){
            return new Response($e->getMessage());
        }
    }


    /**
     * @Route("/{_country}/{_locale}/account/transactions/{page}", name="ikt_transactions")
     */
    public function transactionsAction(Request $request, $page)
    {

        $activityLog = $this->get('app.activity_log');
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY'))
        {
            return $this->redirectToRoute('login',array('_country' => $request->cookies->get(AppConstant::COOKIE_COUNTRY), '_locale' => $request->cookies->get(AppConstant::COOKIE_LOCALE)));
        }
        try
        {
            if ($request->query->get('draw'))
            {
                $page = $request->query->get('draw');
            }
            $restClient = $this->get('app.rest_client')->IsAdmin(true);
            // fetch trans count and save in session for future
            if (!$this->get('session')->get('trans_count')) {
                $url = $request->getLocale() . '/api/' . $this->getUser()->getIktCardNo() . '/customer_transaction_count.json';
                $countData = $restClient->restGet(AppConstant::WEBAPI_URL . $url, array('Country-Id' => strtoupper($request->get('_country'))));
                if ($countData['success'] == true) {
                    $this->get('session')->set('trans_count', $countData['transaction_count']);
                }
            }
            $url = $request->getLocale() . '/api/' . $this->getUser()->getIktCardNo() . '/customer_trans_bypage/' . $page . '.json';
            // echo AppConstant::WEBAPI_URL.$url;
            $data = $restClient->restGet(AppConstant::WEBAPI_URL . $url, array('Country-Id' => strtoupper($request->get('_country'))));
            if (!empty($data))
            {
                if ($data['success'] == true) {
                    // format data before displaying in datatables
                    $count = 0;
                    foreach ($data['data'] as $key => $value) {
                        $trans[$count] = array(
                            ($request->getLocale() == 'en') ? $value['bran_en'] : $value['bran_ar'],
                            $value['inv_no'],
                            number_format($value['inv_amt'], 2),
                            $value['trans_date'],
                            number_format($value['credit'], 2),
                            number_format($value['debt'], 2),
                            number_format($value['expired'], 2),
                        );
                        $count++;
                    }
                    $resp['draw'] = $page;
                    $resp['recordsTotal'] = $this->get('session')->get('trans_count');;
                    $resp['recordsFiltered'] = $this->get('session')->get('trans_count');;
                    $resp['data'] = $trans;
                    return new Response(json_encode($resp));
                }
                else
                {
                    $resp = '';
                    return new Response(json_encode($resp));
                }

            } else {
                $resp = '';
                return new Response(json_encode($resp));
            }
            //$resp = '';
            //return new Response(json_encode($resp));
        }
        catch(\Exception $e){
            $resp = '';
            return new Response(json_encode($resp));
        }
    }


   






    /**
     * @Route("/{_country}/{_locale}/forgot/email", name="forgot_email")
     * Request $request
     */
    public function forgotEmailAction(Request $request)
    {
        $activityLog = $this->get('app.activity_log');

        /***********/
        $commFunct = new FunctionsController();
        $commFunct->setContainer($this->container);

        if ($commFunct->checkSessionCookies($request) == false) {
            return $this->redirect($this->generateUrl('landingpage'));
        }
        /***********/
        $response = new Response();
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

        /***********/

        if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            // do not show this form to user when user is logged in
            return $this->redirectToRoute('homepage',array('_country' => $request->cookies->get(AppConstant::COOKIE_COUNTRY), '_locale' => $request->cookies->get(AppConstant::COOKIE_LOCALE)));
        }
        $error = array('success' => true, 'message' => '');
        $restClient = $this->get('app.rest_client')->IsAdmin(true);
        $smsService = $this->get('app.sms_service');
        $form = $this->createForm(ForgotEmailType::class, array(), array(
                'additional' => array(
                    'locale' => $request->getLocale(),
                    'country' => $request->get('_country'),
                )
            )
        );
        $form->handleRequest($request);
        $pData = $form->getData();
        $data = $request->request->all();
        try
        {
            if ($form->isSubmitted())
            {
                if($form->isValid()) {
                    $errors = "";
                    $validate_data = array($data['forgot_email']['iqama'] , $data['forgot_email']['iktCardNo'],
                        $data['forgot_email']['captchaCode'] );
                    $errors = $commFunct->validateData($validate_data);
                    if($errors > 0)
                    {
                        $filename      = $commFunct->saveTextAsImage();
                        $response->setContent($filename['filename']);
                        $captcha_image = $filename['image_captcha'];

                        $commFunct->setCsrfToken('front_forgot_email');
                        $session = new Session();
                        $token = $session->get('front_forgot_email');
                        $message = $this->get('translator')->trans('Please provide valid data');

                        $error['success'] = false;
                        $error['message'] = $message;
                        return $this->render('/account/forgot_email.twig',
                            array(
                                'form'  => $form->createView(),
                                'error' => $error,
                                'data'  => $captcha_image,
                                'token' => $token,
                            )
                        );
                    }



                    try
                    {
                        if($commFunct->checkCsrfToken($form->get('token')->getData(), 'front_forgot_email' )) 
                        {
                            $captchaCode = trim(strtoupper(strip_tags($this->get('session')->get('_CAPTCHA'))));
                            // echo '<br>';
                            $captchaCodeSubmitted = trim(strtoupper(strip_tags($form->get('captchaCode')->getData())));
                            $filename = $commFunct->saveTextAsImage();
                            $response->setContent($filename['filename']);
                            $captcha_image = $filename['image_captcha'];
                            if ($captchaCodeSubmitted != $captchaCode) {
                                $commFunct->setCsrfToken('front_forgot_email');
                                $session = new Session();
                                $token = $session->get('front_forgot_email');

                                $error_cl = 'alert-danger';
                                $error['success'] = false;
                                $message = "";
                                $error['message'] = $this->get('translator')->trans('Invalid captcha code');
                                return $this->render('/account/forgot_email.twig',
                                    array
                                    (
                                        'form' => $form->createView(),
                                        'error' => $error,
                                        'data' => $captcha_image,
                                        'token' => $token,
                                    )
                                );
                            }


                            if ($request->get('_country') == 'sa') {
                                $validate_Iqama = $this->validateIqama(strip_tags($data['forgot_email']['iqama']));
                                if ($validate_Iqama == false) {
                                    $commFunct->setCsrfToken('front_forgot_email');
                                    $session = new Session();
                                    $token = $session->get('front_forgot_email');
                                    $message = "";
                                    $error['success'] = false;
                                    $error['message'] = $this->get('translator')->trans('Invalid Iqama Id/SSN Number' . $request->get('_country'));
                                    return $this->render('/account/forgot_email.twig',
                                        array(
                                            'form'  => $form->createView(),
                                            'error' => $error,
                                            'data'  => $captcha_image,
                                            'token' => $token,
                                        )
                                    );
                                }
                            }

                            $accountEmail = $this->iktExist($pData['iktCardNo']);

                            $url = $request->getLocale() . '/api/' . $pData['iktCardNo'] . '/userdata.json';
                            // echo AppConstant::WEBAPI_URL.$url;
                            $data = $restClient->restGet(AppConstant::WEBAPI_URL . $url, array('Country-Id' => strtoupper($request->get('_country'))));
                            // print_r($data);exit;
                            if (!empty($data)) {
                                if ($data['success'] == true) {
                                    // match the iqama numbers ( from form and other from the local data)
                                    if ($pData['iqama'] != $data['user'][0]) {
                                        $error['success'] = false;
                                        $error['message'] = $this->get('translator')->trans('Invalid Iqama Id/SSN Number' . $request->get('_country'));
                                    } else {
                                        $message = $this->get('translator')->trans("Your account registration email is %s", ["%s" => $accountEmail]);
                                        $acrivityLog = $this->get('app.activity_log');
                                        // send sms code
                                        $status_sms = $smsService->sendSms($data['user'][1], $message, $request->get('_country'));
                                        if ($status_sms == 1)
                                        {
                                            $acrivityLog->logEvent(AppConstant::ACTIVITY_FORGOT_EMAIL_SMS, $pData['iktCardNo'],
                                            array('message' => $message, 'session' => $data['user']),  "null" , "null");
                                            $error['success'] = true;
                                            $error['message'] = $this->get('translator')->trans('You will recieve sms on your mobile number **** %s', ["%s" => substr($data['user'][1], 8, 12)]);
                                        }
                                        else
                                        {
                                            $message = $this->get('translator')->trans('Unable to process your request at this time.Please try later');
                                            $acrivityLog->logEvent(AppConstant::ACTIVITY_FORGOT_EMAIL_SMS_FAILED, $pData['iktCardNo'],

                                                array('message' => $message, 'session' => $data['user']), "null" , "null");
                                            $error['success'] = false;
                                            $error['message'] = $this->get('translator')->trans('Unable to process your request at this time.Please try later');
                                        }
                                    }
                                } else {
                                    $error['success'] = false;
                                    $error['message'] = $this->get('translator')->trans('Please enter valid Iktissab id and iqama/SSNeg');
                                }
                            } else {
                                $error['success'] = false;
                                $error['message'] = $this->get('translator')->trans('Unable to update record');
                            }
                        }
                        else
                        {
                            $this->get('security.token_storage')->setToken(null);
                            $response = new RedirectResponse($this->generateUrl('landingpage'));
                            return $response;
                        }

                    } catch (\Exception $e) {
                        $error['success'] = false;
                        $error['message'] = $e->getMessage();
                    }
                    $filename = $commFunct->saveTextAsImage();
                    $response->setContent($filename['filename']);
                    $captcha_image = $filename['image_captcha'];
                    $commFunct->setCsrfToken('front_forgot_email');
                    $session = new Session();
                    $token = $session->get('front_forgot_email');
                    return $this->render('/account/forgot_email.twig',
                        array
                        (
                            'form'  => $form->createView(),
                            'error' => $error,
                            'data'  => $captcha_image,
                            'token' => $token
                        )
                    );
                }
                else
                {
                    $filename      = $commFunct->saveTextAsImage();
                    $response->setContent($filename['filename']);
                    $captcha_image = $filename['image_captcha'];
                    $commFunct->setCsrfToken('front_forgot_email');
                    $session = new Session();
                    $token   = $session->get('front_forgot_email');
                    $message = $this->get('translator')->trans('Unable to process your request at this time.Please try later');
                    $error['success'] = false;
                    $error['message'] = $message;
                    return $this->render('/account/forgot_email.twig',
                        array(
                            'form'  => $form->createView(),
                            'error' => $error,
                            'data'  => $captcha_image,
                            'token' => $token,
                        )
                    );
                }
            }
            else
            {
                $commFunct->setCsrfToken('front_forgot_email');
                $session = new Session();
                $token   = $session->get('front_forgot_email');

                $filename      = $commFunct->saveTextAsImage();
                $response->setContent($filename['filename']);
                $captcha_image = $filename['image_captcha'];
                $message = "";
                return $this->render('/account/forgot_email.twig',
                    array(
                        'form'  => $form->createView(),
                        'error' => $error,
                        'data'  => $captcha_image,
                        'token' => $token ,
                    )
                );
            }
        }
        catch(\Exception $e)
        {
            $e->getMessage();
            $commFunct->setCsrfToken('front_forgot_email');
            $session = new Session();
            $token   = $session->get('front_forgot_email');

            $message = $this->get('translator')->trans('An invalid exception occurred');
            $error['success'] = false;
            $error['message'] = $message;
            return $this->render('/account/forgot_email.twig',
                array(
                    'form'    => $form->createView(),
                    'error'   => $error,
                    'data'    => $captcha_image , 'token' => $token
                )
            );
        }
        catch (AccessDeniedException $ed)
        {
            $message = $this->get('translator')->trans($ed->getMessage());
            $error['success'] = false;
            $error['message'] = $message;
            $commFunct->setCsrfToken('front_forgot_email');
            $session = new Session();
            $token   = $session->get('front_forgot_email');
            return $this->render('/account/forgot_email.twig',
                array(
                    'form'    => $form->createView(),
                    'error'   => $error,
                    'data'    => $captcha_image,
                    'token'   => $token
                )
            );
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
     * @param Request $request
     * @param string $password
     * @Route("/{_country}/{_locale}/change/passwordoffline", name="change_passwordoffline")
     *
     */
    
    public function updatePasswordOffline(Request $request , $password)
    {
        try
        {
            if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY'))
            {
                return $this->redirectToRoute('login',array('_country' => $request->cookies->get(AppConstant::COOKIE_COUNTRY), '_locale' => $request->cookies->get(AppConstant::COOKIE_LOCALE)));
            }
            $password = $password;

            $country_id  = $request->get('_country');
            $restClient  = $this->get('app.rest_client')->IsAdmin(true);
            $iktUserData = $this->get('session')->get('iktUserData');
            $iktCardNo   = $iktUserData['C_id'];
            if(!empty($password))
            {
                /************************/
                $url = $request->getLocale() . '/api/change_password.json';
                $form_data   =   array(
                    'secret' =>  $password
                );
                $postData = json_encode($form_data);
                $request->get('_country');
                $data     = $restClient->restPostForm(AppConstant::WEBAPI_URL . $url, $postData, array('Country-Id' => $request->get('_country')));
                // print_r($data);exit;
                if($data['status'] == 1)
                {
                    if($data['success'] == 1)
                    {
                        return true;
                    }
                    else
                    {
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
    }

    /**
     * @param Request $request
     * @param string $password
     * @Route("/{_country}/{_locale}/change/emailoffline", name="change_emailoffline")
     *
     */
    public function updateEmailOffline(Request $request , $email)
    {
        try
        {
            $email = $email;
            if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY'))
            {
                return $this->redirectToRoute('login',array('_country' => $request->cookies->get(AppConstant::COOKIE_COUNTRY), '_locale' => $request->cookies->get(AppConstant::COOKIE_LOCALE)));
            }

            $country_id  = $request->get('_country');
            $this->getUser()->getIktCardNo();
            $restClient  = $this->get('app.rest_client')->IsAdmin(true);
            $iktUserData = $this->get('session')->get('iktUserData');
            $iktCardNo   = $iktUserData['C_id'];
            if(!empty($email))
            {
                /************************/
                $url = $request->getLocale() . '/api/change_email.json';
                $form_data   =   array(
                    'email' =>  $email
                );
                $postData = json_encode($form_data);
                $request->get('_country');
                $data = $restClient->restPostForm(AppConstant::WEBAPI_URL . $url, $postData, array('Country-Id' => $request->get('_country')));
                if($data['status'] == 1)
                {
                    if($data['success'] == true)
                    {
                        return true;
                    }
                    else
                    {
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



    /**
     * @Route("/{_country}/{_locale}/setCountryLogin", name="setCountryLogin")
     * @param Request $request
     * @param $langauge
     */
    public function setCountryLogin(Request $request , $ccid)
    {
        $response = new Response();
        $tokenStorage = $this->get('security.token_storage');
        $userCountry  =  $ccid;
        $userCountry  =  trim($request->query->get('ccid'));
        $commFunct = new FunctionsController();

        $cookieLocale = $request->cookies->get(AppConstant::COOKIE_LOCALE);
        $commFunct->changeCountry($request, $userCountry);
        if($this->get('session')->get('iktUserData'))
        {
            return $this->redirect($this->generateUrl('account_logout', array('_country' => $userCountry, '_locale' => $cookieLocale)));
        }
        else
        {
            return $this->redirectToRoute('account_home', array('_country' => $userCountry, '_locale' => $cookieLocale));
        }
    }

    public function validateNationality($iqama , $nat_no)
    {
        if (substr($iqama, 0,1)  == 1 && $nat_no == 1) {
            return true;
        }
        else if (substr($iqama, 0,1)  != 1 && $nat_no != 1) {
            return true;
        }
        else
        {
            return false;
        }
    }










}