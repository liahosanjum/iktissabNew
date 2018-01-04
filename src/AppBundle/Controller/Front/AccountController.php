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
use Circle\RestClientBundle\Exceptions\CurlException;
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


class AccountController extends Controller
{

    /**
     * @Route("/{_country}/{_locale}/account/home", name="account_home")
     */

    public function myAccountAction(Request $request)
    {
        $activityLog      = $this->get('app.activity_log');
        try {

                $response = new Response();
                if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
                    return $this->redirectToRoute('homepage', array('_country' => $request->cookies->get(AppConstant::COOKIE_COUNTRY), '_locale' => $request->cookies->get(AppConstant::COOKIE_LOCALE)));
                }
                // when everything is fine message is empty
                $message = '';
                /****************/
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

                // todo:
                if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
                    $commFunct->checkSessionCookies($request);
                    if ($commFunct->checkSessionCookies($request) == false) {
                        return $this->redirect($this->generateUrl('landingpage'));
                    }

                } else
                {
                    $restClient = $this->get('app.rest_client')->IsAuthorized(true);
                    if (!$this->get('session')->get('iktUserData')) {
                        $url = $request->getLocale() . '/api/' . $this->getUser()->getIktCardNo() . '/userinfo.json';
                        // echo AppConstant::WEBAPI_URL.$url;
                        $data = $restClient->restGetForm(AppConstant::WEBAPI_URL . $url, array('Country-Id' => strtoupper($request->get('_country'))));
                        //print_r($data);
                        if($data['status'] == 1) {
                            // response is ok from services
                            if ($data['success'] == "true") {
                                // data is also present from services
                                $this->get('session')->set('iktUserData', $data['user']);
                            }
                            else{
                                // no data is presenet on the services
                                $message = $data['message'];
                            }
                        }
                        else{
                            // exception occrured from the services side
                            $data = '';
                            $message = $this->get('translator')->trans('An invalid exception occurred');
                        }
                    }


                    // print_r($data);
                    // delete it

                    $data_array = '';
                    $iktUserData = $this->get('session')->get('iktUserData');



                    //exit;
                    //print_r($iktUserData['C_id']);
                    $url_trans_count = $request->getLocale() . '/api/' . $this->getUser()->getIktCardNo() . '/customer_transaction_count.json';
                    $data_trans_count = $restClient->restGetForm(AppConstant::WEBAPI_URL . $url_trans_count, array('Country-Id' => strtoupper($request->get('_country'))));
                    //print_r($data_trans_count);
                    if($data_trans_count['status'] == 1) {
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
                                                    $data_array = array();
                                                    $data_array[$i]['c_id'] = $data_transactions['c_id'];
                                                    $data_array[$i]['YR'] = $data_transactions['YR'];
                                                    //$data_array[$i]['Mnth'] = $data_transactions['Mnth'];
                                                    $data_array[$i]['Mnth'] = $this->getMonth($data_transactions['Mnth'], $data_transactions['YR'], $request->cookies->get(AppConstant::COOKIE_LOCALE));
                                                    $data_array[$i]['Sales'] = $data_transactions['Sales'];
                                                }
                                            }
                                        } else {
                                            $data_array = "";
                                            $message = $this->get('translator')->trans('Unable to process your request at this time.Please try later');
                                        }
                                    } else {
                                        $data_array = "";
                                        $message = $data_transaction['message'];
                                    }


                                } else {
                                    $data_array = "";
                                    $message = $this->get('translator')->trans('Unable to process your request at this time.Please try later');

                                }

                            } else {
                                $data_array = "";
                                $message = $data_trans_count['message'];
                            }
                        } else {
                            $data_array = "";
                            $message = $this->get('translator')->trans('Unable to process your request at this time.Please try later');
                        }
                    }
                    else
                    {
                        $data_array = "";
                        $message = $data_trans_count['message'];
                    }
                        // var_dump($data_transaction);
                    // var_dump($data_array);
                    // array_multisort($data_array,SORT_DESC,SORT_NUMERIC);

                    //$this->sksort($data_array, "YR");
                    //$this->sksort($data_array, "Mnth");

                   
                    return $this->render('/account/home.html.twig', array('iktData' => $iktUserData,

                        'iktTransData' => $data_array,
                    'message' => $message

                    ));
                }
        }
        catch(Exception $e){
            
            $activityLog->logEvent(AppConstant::ACTIVITY_ACCOUNT_HOME_ERROR, $iktUserData['C_id'] , array('iktissab_card_no' => $iktUserData['C_id'], 'message' => $e->getMessage(), 'session' => $iktUserData));
            $message = $this->get('translator')->trans('An invalid exception has occurred');
            return $this->render('/account/home.html.twig', array('iktData' => 0, 'iktTransData' => '', 'message' => $message ));
        }
        catch(AccessDeniedException $ed){
            $ed->getMessage();
            $message =  $this->get('translator')->trans($ed->getMessage());
            $activityLog->logEvent(AppConstant::ACTIVITY_ACCOUNT_HOME_ERROR, 0 , array('iktissab_card_no' => '', 'message' => $message, 'session' => '' ));
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
                '3' => 'مارس-' . $y, '4' => 'أبريل-' . $y,
                '5' => 'مايو-' . $y, '6' => 'يونيو-' . $y,
                '7' => 'يوليو-' . $y, '8' => 'أغسطس-' . $y,
                '9' => 'سبتمبر-' . $y, '10' => 'أكتوبر-' . $y,
                '11' => 'نوفمبر-' . $y, '12' => 'ديسمبر-' . $y
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



        return $month[$m];
        
    }





    /**
     * @Route("/{_country}/{_locale}/account/info", name="front_account_info")
     */

    public function accountInfoAction(Request $request)
    {
        $activityLog = $this->get('app.activity_log');
        $commFunct = new FunctionsController();
        if ($commFunct->checkSessionCookies($request) == false) {
            return $this->redirect($this->generateUrl('landingpage'));
        }
        try {
            if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
                return $this->redirectToRoute('homepage', array('_country' => $request->cookies->get(AppConstant::COOKIE_COUNTRY), '_locale' => $request->cookies->get(AppConstant::COOKIE_LOCALE)));
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
            $restClient = $this->get('app.rest_client')->IsAuthorized(true);
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
        catch (Exception $e){
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
        $activityLog = $this->get('app.activity_log');
        $commFunct = new FunctionsController();
        if ($commFunct->checkSessionCookies($request) == false) {
            return $this->redirect($this->generateUrl('landingpage'));
        }

        try
        {

            if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
                return $this->redirectToRoute('homepage', array('_country' => $request->cookies->get(AppConstant::COOKIE_COUNTRY), '_locale' => $request->cookies->get(AppConstant::COOKIE_LOCALE)));
            }
            $restClient = $this->get('app.rest_client')->IsAuthorized(true);
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
        catch(Exception $e)
        {
            $iktUserData =  $this->get('session')->get('iktUserData');
            $activityLog->logEvent(AppConstant::ACOUNR_PERSONAL_INFO_ERROR, 0, array('iktissab_card_no' => '', 'message' => $e->getMessage(), 'session' => $iktUserData));
            $message = $this->get('translator')->trans('An invalid exception occurred');
            $errorcl = 'alert-danger';
            return $this->render('/account/personalinfo.html.twig', array('iktData' => $iktUserData , 'message' => $message ,
                'errorcl' => $errorcl
                ));
        }
        catch(AccessDeniedException $ed){
            $message =  $this->get('translator')->trans($ed->getMessage());
            $activityLog->logEvent(AppConstant::ACOUNR_PERSONAL_INFO_ERROR, 0 , array('iktissab_card_no' => '', 'message' => $ed->getMessage(), 'session' => '' ));
            return $this->render('/account/home.html.twig', array('iktData' => '', 'iktTransData' => '', 'message' => $message ));
        }

    }




    /**
     * @Route("/{_country}/{_locale}/account/email", name="front_account_email")
     */
    public function accountEmailAction(Request $request)
    {
        $activityLog      = $this->get('app.activity_log');
        try
        {
            if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY'))
            {
                return $this->redirectToRoute('homepage',array('_country' => $request->cookies->get(AppConstant::COOKIE_COUNTRY), '_locale' => $request->cookies->get(AppConstant::COOKIE_LOCALE)));
            }
            $restClient       = $this->get('app.rest_client')->IsAuthorized(true);
            $commFunct = new FunctionsController();
            $commFunct->setContainer($this->container);
            $locale_cookie    = $request->getLocale();
            $country_cookie   = $request->get('_country');
            $userLang         = trim($request->query->get('lang'));

           

            $Country_id       = strtoupper($this->getCountryId($request));
            $iktUserData      = $this->get('session')->get('iktUserData');
            $posted           = array();
            $iktCardNo        = $iktUserData['C_id'];
            $iktID_no         = $iktUserData['ID_no'];
            $currentEmail     = $iktUserData['email'];
            $mobile           = $iktUserData['mobile'];
            $form = $this->createForm(UpdateEmailType::class, array() , array(
                    'extras'  => array('email'   => $currentEmail)));
            $data = $request->request->all();
            //For logged in user infomation
            $tokenStorage       = $this->get('security.token_storage');
            $user_email_online  = $tokenStorage->getToken()->getUser()->getUsername();
            if(!empty($data))
            {
                // here we will add validation to the form
                /************************/
                // print_r($data);
                $newemail = $data['update_email']['newemail']['first'];
                /**************************/
                $form_data[0]   =    array(
                    'C_id'      =>   $iktCardNo,
                    'field'     =>   'email',
                    'new_value' =>   $data['update_email']['newemail']['first'],
                    'old_value' =>   $currentEmail,
                    'comments'  =>   'Update user account email'
                );
                // print_r($form_data[0]);exit;
                $this->get('session')->set('new_value', $data['update_email']['newemail']['first']);
                // here we will check if the email is already registered on website or not
                $email_val = $this->checkEmail($data['update_email']['newemail']['first'],$Country_id , $iktCardNo);

                if(!empty($email_val) && $email_val != null )
                {
                    // print_r($email_val);
                    // check email exist
                    if ($email_val['success'] == true)
                    {

                        if ($email_val['result']['email'] == true)
                        {
                            $errorcl = 'alert-danger';
                            $message = $this->get('translator')->trans('The new email is already registered before , please enter a valid email');
                            $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_EMAIL_ERROR, $iktUserData['C_id'], array('iktissab_card_no' => $iktUserData['C_id'], 'message' => $message, 'session' => $iktUserData));
                            return $this->render('account/email.html.twig',
                                array('form' => $form->createView(), 'message' => $message, 'errorcl' => $errorcl)
                            );
                        }
                    }
                    else
                    {
                        $iktCardNo      = $iktUserData['C_id'];
                        $iktID_no       = $iktUserData['ID_no'];
                        $currentEmail   = $iktUserData['email'];
                        // form posted data
                        $data           = $request->request->all();
                        $form_data      = array('email' => $data['update_email']['newemail']['first']);
                        $postData       = json_encode($form_data);
                        // print_r($data);
                        $url = $request->getLocale() . '/api/checkmail.json';
                        $chk_email = $restClient->restPostForm(AppConstant::WEBAPI_URL . $url, $postData, array('Country-Id' => strtoupper($request->get('_country'))));
                        //print_r($chk_email);
                        if ($chk_email['success'] == true) {
                            $message = $this->get('translator')->trans('The new email is already registered before , please enter a valid email');
                            $errorcl = 'alert-danger';
                            // $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_EMAIL_ERROR, $iktUserData['C_id'], array('iktissab_card_no' => $iktUserData['C_id'], 'message' => $message, 'session' => $iktUserData));
                            return $this->render('account/email.html.twig',
                                array('form' => $form->createView(), 'message' => $message, 'errorcl' => $errorcl)
                            );
                        }
                        elseif ($chk_email['success'] == false && $chk_email['status'] == 0)
                        {
                            $message = $chk_email['message'];
                            $errorcl = 'alert-danger';
                            // $activityLog->logEvent(AppConstant::ACTIVITY_EMAIL_UPDATE_SMS_ERROR, $iktUserData['C_id'], array('iktissab_card_no' => $iktUserData['C_id'], 'message' => $message, 'session' => $iktUserData));
                            return $this->render('account/email.html.twig',
                                array('form' => $form->createView(), 'message' => $message, 'errorcl' => $errorcl)
                            );
                        }
                        else
                        {
                            // 1. GET CURRENT USER PASSWORD
                            // 2. MATCH WITH USER ENTER PASSWORD
                            // 3. WHEN PASSWORD IS MATCHED CHANGE EMAIL ADDRESS
                            $iktUserData      = $this->get('session')->get('iktUserData');
                            $iktCardNo        = $iktUserData['C_id'];
                            $iktID_no         = $iktUserData['ID_no'];
                            $em               = $this->getDoctrine()->getManager();
                            $userInfoLoggedIn = $em->getRepository('AppBundle:User')->find($iktCardNo);
                            $email            = $userInfoLoggedIn->getEmail();
                            $form->handleRequest($request);
                            // print_r($userInfoLoggedIn);
                            $postData = $request->request->all();
                            // dump($postData);exit;
                            // var_dump($objUser);
                            // current is the logged in user password
                            $objUser = $this->getUser();
                            // print_r($objUser);exit;
                            $password_current   = $userInfoLoggedIn->getPassword();
                            $old_password = md5(trim($postData['update_email']['old_password']));
                            if($form->isValid())
                            {
                                if ($password_current != $old_password) {
                                    $message = $this->get('translator')->trans('Please enter correct password');
                                    $errorcl = 'alert-danger';
                                    return $this->render('account/email.html.twig',
                                        array('form' => $form->createView(), 'message' => $message , 'errorcl' => $errorcl));
                                }
                                else
                                {
                                    $newemail = $data['update_email']['newemail']['first'];
                                    // here when the offline db password is update then udate online
                                    $email_changed = $this->accountSMSVerification($request , $newemail);
                                    //print_r($email_changed);
                                    // exit;
                                    if($email_changed == true)
                                    {
                                        // $userInfoLoggedIn->setEmail($newemail);
                                        // $em = $this->getDoctrine()->getManager();
                                        // $em->persist($userInfoLoggedIn);
                                        // $em->flush();
                                        // onlly update token here
                                        $tokenStorage->getToken()->getUser()->setUsername($newemail);
                                        $restClient = $this->get('app.rest_client')->IsAuthorized(true);
                                        $url = $request->getLocale() . '/api/' . $this->getUser()->getIktCardNo() . '/userinfo.json';
                                        // echo AppConstant::WEBAPI_URL.$url;
                                        // update loggedin session info to reflect the
                                        // profile updates for email
                                        $data_user = $restClient->restGetForm(AppConstant::WEBAPI_URL . $url, array('Country-Id' => strtoupper($request->get('_country'))));
                                        if($data_user['success'] == true)
                                        {
                                            $this->get('session')->set('iktUserData', $data_user['user']);
                                        }
                                            if ($request->getLocale() == 'ar') {
                                            $first = 'تم تحديث بريدكم الإلكتروني. نأمل استخدام البريد';
                                            $last = 'عند الدخول إلى الموقع مرة أخرى';
                                            $message = $first . '( ' . $this->get('session')->get("new_value") . ' )' . $last;
                                            // todo:enable it
                                            // $message = $this->get('translator')->trans('Your email is updated. Next time, please use the ( ' . $this->get('session')->get("new_value") . ') address for signing to the website  ');
                                        } else {
                                            $message = $this->get('translator')->trans('Your email is updated. Next time, please use the ( ' . $this->get('session')->get("new_value") . ') address for signing to the website  ');
                                        }

                                        $errorcl = 'alert-success';
                                        return $this->render('account/email.html.twig',
                                            array('form' => $form->createView(), 'message' => $message, 'errorcl' => $errorcl));
                                    }
                                    else
                                    {
                                        // $messageLog = $this->get('translator')->trans('Unable to update record');
                                        // $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_EMAIL_ERROR, $iktUserData['C_id'], array('iktissab_card_no' => $iktUserData['C_id'], 'message' => $messageLog, 'session' => $iktUserData));
                                        $message = $this->get('translator')->trans('Unable to update record');
                                        // update password in the offline database
                                        $errorcl = 'alert-danger';
                                        return $this->render('account/email.html.twig',
                                            array('form' => $form->createView(), 'message' => $message, 'errorcl' => $errorcl));
                                    }
                                }
                            }
                            else
                            {
                                $message = $this->get('translator')->trans('Unable to update record');
                                $errorcl = 'alert-danger';
                                // $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_PASSWORD_ERROR, $iktUserData['C_id'], array('iktissab_card_no' => $iktUserData['C_id'], 'message' => $message, 'session' => $iktUserData));
                                return $this->render('account/email.html.twig',
                                    array('form' => $form->createView(), 'message' => $message , 'errorcl' => $errorcl));
                            }
                            /***********/
                            // OLD CODE FOR REPLACING THE USERS SMS VERIFICATION WITH THE USER PASSWORD VERIFICATION
                            /*
                            $otp = rand(111111, 999999);
                            $this->get('session')->set('smscode', $otp);
                            $message = $this->get('translator')->trans("Please enter the verification code you received on your mobile number ******"). substr($mobile, 6, 10);
                            $smsmessage = $this->get('translator')->trans("Verification code:") . $otp . $this->get('translator')->trans("Changing account email");
                            $smsService = $this->get('app.sms_service');
                            $MsgID = rand(1, 99999);
                            $msg = $message;
                            $smsResponse = $smsService->sendSmsEmail($mobile, $smsmessage, $request->get('_country'));
                            if ($smsResponse == 1) {
                                $message_sms = $this->get('translator')->trans('SMS sent successfully');
                                $activityLog->logEvent(AppConstant::ACTIVITY_EMAIL_UPDATE_SMS_SUCCESS, $iktUserData['C_id'], array('iktissab_card_no' => $iktUserData['C_id'], 'message' => $message_sms, 'session' => $iktUserData));
                                $errorcl = 'alert-success';


                                return $this->render('account/sendsms.html.twig',
                                    array('form' => $form->createView(), 'message' => $message, 'errorcl' => $errorcl)
                                );
                            } else {
                                $message = $this->get('translator')->trans('Unable to process your request at this time.Please try later');
                                $errorcl = 'alert-danger';
                                $activityLog->logEvent(AppConstant::ACTIVITY_EMAIL_UPDATE_SMS_ERROR, $iktUserData['C_id'], array('iktissab_card_no' => $iktUserData['C_id'], 'message' => $message, 'session' => $iktUserData));
                                return $this->render('account/email.html.twig',
                                    array('form' => $form->createView(), 'message' => $message, 'errorcl' => $errorcl)
                                );
                            }*/

                        }
                    }
                }
                else
                {
                    $message = $this->get('translator')->trans('Unable to process your request at this time.Please try later');
                    $errorcl = 'alert-danger';
                    // $activityLog->logEvent(AppConstant::ACTIVITY_EMAIL_UPDATE_SMS_ERROR, $iktUserData['C_id'], array('iktissab_card_no' => $iktUserData['C_id'], 'message' => $message, 'session' => $iktUserData));
                    return $this->render('account/email.html.twig',
                        array('form' => $form->createView(), 'message' => $message, 'errorcl' => $errorcl)
                    );
                }
            }
            else
            {
                $message = $this->get('translator')->trans('');
                $errorcl = 'alert-danger';
                return $this->render('account/email.html.twig',
                    array('form' => $form->createView(), 'message' => $message, 'errorcl' => $errorcl)
                );
            }
        }
        catch (\Exception $e)
        {
            // echo 'tested NO-I ERROR';
            $errorcl = 'alert-danger';
            $activityLog->logEvent(AppConstant::ACTIVITY_EMAIL_UPDATE_SMS_ERROR, 0 , array('iktissab_card_no' => 0, 'message' => $e->getMessage(), 'session' => $iktUserData));
            return $this->render('account/email.html.twig',
                array('form' => $form->createView(),  'message' => $this->get('translator')->trans('Unable to process your request at this time.Please try later') , 'errorcl' => $errorcl )
            );
        }
        catch (AccessDeniedException $ed)
        {
            // echo 'tested NO-I ERROR';
            $errorcl = 'alert-danger';
            $activityLog->logEvent(AppConstant::ACTIVITY_EMAIL_UPDATE_SMS_ERROR, 0 , array('iktissab_card_no' => 0, 'message' => $ed->getMessage(), 'session' => $iktUserData));
            return $this->render('account/email.html.twig',
                array('form' => $form->createView(),  'message' => $this->get('translator')->trans('Unable to process your request at this time.Please try later') , 'errorcl' => $errorcl )
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
                if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
                    return $this->redirectToRoute('homepage', array('_country' => $request->cookies->get(AppConstant::COOKIE_COUNTRY), '_locale' => $request->cookies->get(AppConstant::COOKIE_LOCALE)));
                }
                // $data_form['smsverify'];exit;
                $restClient   = $this->get('app.rest_client')->IsAuthorized(true);
                $Country_id   = strtoupper($this->getCountryId($request));
                $iktUserData  = $this->get('session')->get('iktUserData');
                //current logged in user iktissab id
                $iktCardNo    = $iktUserData['C_id'];
                $iktID_no     = $iktUserData['ID_no'];
                // get current email of the user
                $currentEmail = $iktUserData['email'];
                // form posted data
                $data_form    = "";
                $data_form    = $request->request->all();
                $url          = $request->getLocale() . '/api/update_user_email.json';
                $comments     = '';
                $form_data = array(
                    'C_id'      => $iktCardNo,
                    'field'     => 'email',
                    'new_value' => $newemail,
                    'old_value' => $currentEmail,
                    'comments'  => $comments
                );
                $postData = json_encode($form_data);


                $data = array();
                $data = $restClient->restPostForm(AppConstant::WEBAPI_URL . $url, $postData, array('Country-Id' => strtoupper($request->get('_country'))));


                if ($data["success"] == true)
                {
                    // $this->get('session')->set('iktUserData', $data['user']);
                    $update_email_status = $this->updateEmailOffline($request, $newemail);
                    if ($update_email_status == true)
                    {
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
     * @Route("/{_country}/{_locale}/account/mobile" , name="front_account_mobile")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function userMobileAction(Request $request)
    {
        $activityLog = $this->get('app.activity_log');
        try
        {
            // check user
            // var_dump($this->get('session'));

            $country_id = $request->get('_country');
            $language   = $request->getLocale();
            // echo '===='.$this->get('session')->get('userSelectedCountry');
            $restClient = $this->get('app.rest_client')->IsAuthorized(true);
            $iktUserData = $this->get('session')->get('iktUserData');
            // print_r($iktUserData);exit;
            $data = $request->request->all();
            if($country_id == 'eg')
            {
                $country_id = 'eg';
            }
            else{
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
            $url = $request->getLocale() . '/api/update_user_mobile.json';
            if(!empty($data) && $data != null)
            {
                /************************/
                $iqamaid_mobile = trim($data['mobile']['iqamaid_mobile']);
                //echo '===> posted value '.$mobile = trim($data['mobile']['mobile']);
                $mobile = trim($data['mobile']['mobile']);
                $comment_mobile = trim($data['mobile']['comment_mobile']);
                //print_r($posted);
                /************************/
                if($country_id == 'sa')
                {
                    //
                    $extension = '0';
                    if($iktID_no != $iqamaid_mobile)
                    {
                        $message = $this->get('translator')->trans('Invalid Iqama Id/SSN or mobile number. Please enter correct Iqama Id/SSN'.$country_id);
                        $errorcl = 'alert-danger';
                        return $this->render('account/mobile.html.twig',
                            array('form' => $form->createView(), 'country' => $country_id,   'message' => $message , 'errorcl' => $errorcl));
                    }
                    if($mobile == $iktMobile) {
                        $errorcl = 'alert-danger';
                        $message = $this->get('translator')->trans('Your new mobile number and old mobile number must not be same');
                        return $this->render('account/mobile.html.twig', array('form' => $form->createView(), 'country' => $country_id,   'message' => $message , 'errorcl' => $errorcl));
                    }
                }
                if($country_id == 'eg')
                {
                    $extension = $data['mobile']['ext'];
                    if ($iktID_no != $iqamaid_mobile) {
                        $errorcl = 'alert-danger';
                        $message = $this->get('translator')->trans('Invalid Iqama Id/SSN Number'.$country_id);
                        return $this->render('account/mobile.html.twig', array('form' => $form->createView() ,'country' => $country_id,  'message' => $message , 'errorcl' => $errorcl));
                    }
                }
                /**************************/
                // set mobile number to 10 digits for webservice which needs 10 digits but when used 966 on website form
                // then we are forcing user to enter 9 digits without 0 for KSA.

                $mobile_format_webservice = $extension.$mobile; //$this->getMobileFormat($request , $data['mobile']['mobile']);

                $form_data      =   array(
                    'C_id'      =>  $iktCardNo,
                    'field'     =>  'mobile',
                    'old_value' =>  $iktMobile,
                    'new_value' =>  $mobile_format_webservice,
                    'comments'  =>  $data['mobile']['comment_mobile']
                );
                $postData = json_encode($form_data);
                $request->get('_country');
                try {
                    $data = $restClient->restPostForm(AppConstant::WEBAPI_URL . $url, $postData, array('Country-Id' => strtoupper($request->get('_country'))));
                    if (!empty($data)) {
                        if ($data['success'] == true) {
                            $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_MOBILE_SUCCESS, $iktUserData['C_id'], array('iktissab_card_no' => $iktUserData['C_id'], 'message' => $data['message'], 'session' => $iktUserData));
                            $message = $this->get('translator')->trans($data['message']);
                            $errorcl = 'alert-success';
                            return $this->render('account/mobile.html.twig',
                                array('form' => $form->createView(), 'country' => $country_id, 'message' => $message, 'errorcl' => $errorcl)
                            );
                        }
                        if ($data['success'] == false) {
                            if ($data['status'] == 1) {
                                $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_MOBILE_ERROR, $iktUserData['C_id'], array('iktissab_card_no' => $iktUserData['C_id'], 'message' => $data['message'], 'session' => $iktUserData));
                                $message = $this->get('translator')->trans($data['message']);
                                $errorcl = 'alert-danger';
                                return $this->render('account/mobile.html.twig',
                                    array('form' => $form->createView(), 'country' => $country_id, 'message' => $message, 'errorcl' => $errorcl)
                                );
                            } else {
                                $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_MOBILE_ERROR, $iktUserData['C_id'], array('iktissab_card_no' => $iktUserData['C_id'], 'message' => $data['message'], 'session' => $iktUserData));
                                $message = $this->get('translator')->trans($data['message']);
                                $errorcl = 'alert-danger';
                                return $this->render('account/mobile.html.twig',
                                    array('form' => $form->createView(), 'country' => $country_id, 'message' => $message, 'errorcl' => $errorcl)
                                );
                            }
                        }
                    } else {
                        $message = $this->get('translator')->trans('Unable to update record');
                        //$activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_MOBILE_ERROR, $iktUserData['C_id'], array('iktissab_card_no' => $iktUserData['C_id'], 'message' => $message, 'session' => $iktUserData));
                        $errorcl = 'alert-danger';
                        return $this->render('account/mobile.html.twig',
                            array('form' => $form->createView(), 'country' => $country_id, 'message' => $message, 'errorcl' => $errorcl)
                        );
                    }
                }
                catch (\Exception $e)
                {
                    $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_MOBILE_ERROR, $iktUserData['C_id'] , array('iktissab_card_no' => $iktUserData['C_id'], 'message' => $e->getMessage(), 'session' => $iktUserData));
                    $message = $this->get('translator')->trans('An invalid exception occurred');
                    $errorcl = 'alert-danger';
                    return $this->render('account/mobile.html.twig',
                        array('form' => $form->createView(),'country' => $country_id,'message' => $message,'errorcl' => $errorcl)
                    );
                }
            }
            else
            {
                // this will load view for the first time when user click on the update mobile link
                // echo 'ads';
                $message = $this->get('translator')->trans('');
                $errorcl = 'alert-danger';
                return $this->render('account/mobile.html.twig',
                    array('form' => $form->createView(),'country' => $country_id,  'message' => $message , 'errorcl'=> $errorcl )
                );
            }
        }
        catch (\Exception $e)
        {
            $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_MOBILE_ERROR, 0 , array('iktissab_card_no' => 0, 'message' => $e->getMessage(), 'session' => $iktUserData));
            $message = $this->get('translator')->trans('An invalid exception occurred');
            $errorcl = 'alert-danger';
            return $this->render('account/mobile.html.twig',
                array('form' => $form->createView(),'country' => $country_id,'message' => $message,'errorcl' => $errorcl)
            );
        }
        catch (AccessDeniedException $ed)
        {
            $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_MOBILE_ERROR, 0 , array('iktissab_card_no' => 0, 'message' => $ed->getMessage(), 'session' => ''));
            $message = $this->get('translator')->trans($ed->getMessage());
            $errorcl = 'alert-danger';
            return $this->render('account/mobile.html.twig',
                array('form' => $form->createView(),'country' => $country_id,'message' => $message,'errorcl' => $errorcl)
            );
        }
    }




    /**
     * @Route("/{_country}/{_locale}/account/fullname" , name="front_account_fullname" )
     */
    public function fullNameAction(Request $request)
    {
        $activityLog = $this->get('app.activity_log');
        try
        {
            if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
                return $this->redirectToRoute('homepage',array('_country' => $request->cookies->get(AppConstant::COOKIE_COUNTRY), '_locale' => $request->cookies->get(AppConstant::COOKIE_LOCALE)));
            }

            $country_id  = $request->get('_country');
            $this->getUser()->getIktCardNo();
            $restClient  = $this->get('app.rest_client')->IsAuthorized(true);
            $iktUserData = $this->get('session')->get('iktUserData');
            $iktCardNo   = $iktUserData['C_id'];
            $iktID_no    = $iktUserData['ID_no'];
            $ikt_cname   = $iktUserData['cname'];
            $form = $this->createForm(UpdateFullnameType::class, array() ,array(
                'extras' => array(
                    'country' => $country_id,
                    'iktID_no' => $iktID_no
            )));
            $form->handleRequest($request);
            $data   = $request->request->all();
            // print_r($data['update_fullname']['fullname']);exit;

            if(!empty($data))
            {
                // here we will add validation to the form
                if($data['update_fullname']['fullname'] != "")
                {
                    $fullname_arr = explode(' ', $data['update_fullname']['fullname']);
                    if(count($fullname_arr) < 2)
                    {
                        $message = $this->get('translator')->trans('Name must be in two parts');
                        $errorcl = 'alert-danger';
                        return $this->render('account/fullname.html.twig', array('form' => $form->createView(),
                            'message' => $message , 'errorcl' => $errorcl ));
                    }
                }
                /************************/
                $url = $request->getLocale() . '/api/update_user_name.json';
                $form_data      =   array(
                    'C_id'      =>  $iktCardNo,
                    'field'     =>  'cname',
                    'old_value' =>  $ikt_cname,
                    'new_value' =>  $data['update_fullname']['fullname'],
                    'comments'  =>  $data['update_fullname']['comment_fullname']
                );

                $postData = json_encode($form_data);
                //print_r($postData);exit;
                $request->get('_country');
                $data = $restClient->restPostForm(AppConstant::WEBAPI_URL . $url, $postData, array('Country-Id' => strtoupper($request->get('_country'))));
                // print_r($data);exit;

                // $data['success'] = false;
                // $data['status']  = 0;
                if($data !="" && $data != null)
                {
                    if ($data['success'] == true) {
                        $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_FULLNAME_SUCCESS, $iktUserData['C_id'], array('iktissab_card_no' => $iktUserData['C_id'], 'message' => $data['message'], 'session' => $iktUserData));
                        $errorcl = 'alert-success';
                        return $this->render('account/fullname.html.twig', array('form' => $form->createView(), 'message' => $this->get('translator')->trans($data['message']), 'errorcl' => $errorcl));
                    }

                    if ($data['success'] == false) {
                        if ($data['status'] == 1) {
                            $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_FULLNAME_ERROR, $iktUserData['C_id'], array('iktissab_card_no' => $iktUserData['C_id'], 'message' => $data['message'], 'session' => $iktUserData));
                            $errorcl = 'alert-danger';
                            return $this->render('account/fullname.html.twig', array('form' => $form->createView(), 'message' => $this->get('translator')->trans($data['message']), 'errorcl' => $errorcl));
                        } else {
                            $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_FULLNAME_ERROR, $iktUserData['C_id'], array('iktissab_card_no' => $iktUserData['C_id'], 'message' => $data['message'], 'session' => $iktUserData));
                            $errorcl = 'alert-danger';
                            return $this->render('account/fullname.html.twig', array('form' => $form->createView(), 'message' => $this->get('translator')->trans($data['message']), 'errorcl' => $errorcl));
                        }
                    }
                }
                else
                {
                    $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_FULLNAME_ERROR, $iktUserData['C_id'], array('iktissab_card_no' => $iktUserData['C_id'], 'message' =>$this->get('translator')->trans('Unable to update name'), 'session' => $iktUserData));
                    $errorcl = 'alert-danger';
                    $message = $this->get('translator')->trans('Unable to update name');
                    return $this->render('account/fullname.html.twig', array('form' => $form->createView(), 'message' => $message, 'errorcl' => $errorcl));
                }
            }
            else
            {
                $message = $this->get('translator')->trans('');
                $errorcl = 'alert-success';
                return $this->render('account/fullname.html.twig',
                    array('form' => $form->createView(), 'message' => $message, 'errorcl' => $errorcl)
                );
            }
        }
        catch (\Exception $e)
        {
             $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_FULLNAME_ERROR, 0 , array('iktissab_card_no' => 0, 'message' => $e->getMessage(), 'session' => $iktUserData));
             // removed custom messages
             // $message = $this->get('translator')->trans('Unable to update name');
             $message = $this->get('translator')->trans($e->getMessage());
             $errorcl = 'alert-danger';
             return $this->render('account/fullname.html.twig',
                array( 'form' => $form->createView() ,  'message' => $message , 'errorcl' => $errorcl ));
        }
        catch (AccessDeniedException $ed)
        {
            $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_FULLNAME_ERROR, 0, array('iktissab_card_no' => 0, 'message' => $ed->getMessage(), 'session' => $iktUserData));
            $message = $this->get('translator')->trans($ed->getMessage());
            $errorcl = 'alert-danger';
            return $this->render('account/fullname.html.twig',
                array('form' => $form->createView(),'message' => $message,'errorcl' => $errorcl)
            );
        }
    }




    /**
     * @Route("/{_country}/{_locale}/account/iqamassn" , name="front_account_iqamassn")
     */

    public function iqamaSNNAction(Request $request)
    {
        $activityLog = $this->get('app.activity_log');
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('homepage',array('_country' => $request->cookies->get(AppConstant::COOKIE_COUNTRY), '_locale' => $request->cookies->get(AppConstant::COOKIE_LOCALE)));
        }

        try
        {
            $country_id  = $request->get('_country');
            $language    = $request->getLocale();
            $restClient  = $this->get('app.rest_client')->IsAuthorized(true);
            
            $iktUserData = $this->get('session')->get('iktUserData');
            // print_r($iktUserData);
            $iktCardNo   = $iktUserData['C_id'];
            $iktID_no    = $iktUserData['ID_no'];
            $url  = $request->getLocale() . '/api/update_user_ssn.json';
            $form = $this->createForm(IqamassnType::class, array() ,array(
                'extras' => array(
                    'country'  => $country_id,
                    'iktID_no' => $iktID_no
            )));
            $data   = $request->request->all();
            if(!empty($data))
            {
                $iqamassn_registered = $data['iqamassn']['iqamassn_registered'];
                $iqamassn_new    = $data['iqamassn']['iqamassn_new']['first'];
                $confirm_iqamassn_new = $data['iqamassn']['iqamassn_new']['second'];
                $comment_iqamassn     = $data['iqamassn']['comment_iqamassn'];

                if ($iqamassn_new == $iqamassn_registered) {
                    $message = $this->get('translator')->trans('New Iqama Id/SSN and old Iqama Id/SSN must not be same'.$country_id);
                    $errorcl = 'alert-danger';
                    return $this->render('account/iqamassn.html.twig',
                        array('form' => $form->createView(),'message' => $message , 'errorcl' => $errorcl));
                }

                if ($iqamassn_new != $confirm_iqamassn_new) {
                    $message = $this->get('translator')->trans('New Iqama Id/SSN and confirm new Iqama Id/SSN must be same'.$country_id);
                    $errorcl = 'alert-danger';
                    return $this->render('account/iqamassn.html.twig',
                        array('form' => $form->createView(),'message' => $message,'errorcl' => $errorcl));
                }
                if($country_id == 'sa') {
                    // todo: uncomment checking iqama validation
                    $validateIqama = $this->validateIqama($iqamassn_new);
                    // todo: get country id in get info webservice from qadri
                    // here we need to get country_id from webservice user info
                    // currently iam providing hardcoded country_id = 2
                    $countery_id = 2;
                    $validateNation = $this->validateNationality($iqamassn_new, $countery_id);
                    if ($validateNation == false) {
                        $message = $this->get('translator')->trans('Invalid Iqama Id/SSN Numbersa');
                        $errorcl = 'alert-danger';
                        return $this->render('account/iqamassn.html.twig',
                            array('form' => $form->createView(), 'message' => $message, 'errorcl' => $errorcl));
                    }

                    // $validateIqama = true; // <-- todo: remove this line after testing
                     if ($validateIqama == false) {
                        $message = $this->get('translator')->trans('Invalid Iqama Id/SSN Numbersa');
                        $errorcl = 'alert-danger';
                        return $this->render('account/iqamassn.html.twig',
                            array('form' => $form->createView(), 'message' => $message, 'errorcl' => $errorcl));
                    }
                }
                // set mobile number to 10 digits for webservice which needs 10 digits but when used 966 on website form
                // then we are forcing user to enter 9 digits without 0 for KSA.
                // mobile format for webservice
                $form_data      = array(
                    'C_id'      => $iktCardNo,
                    'field'     => 'iqamassn',
                    'old_value' => $iktID_no,
                    'new_value' => $data['iqamassn']['iqamassn_new']['first'],
                    'comments'  => $data['iqamassn']['comment_iqamassn']
                );

                $postData = json_encode($form_data);
                $request->get('_country');
                $data = $restClient->restPostForm(AppConstant::WEBAPI_URL . $url, $postData, array('Country-Id' => strtoupper($request->get('_country'))));
                if(!empty($data)) {
                    if ($data['success'] == true ) {
                        // posted array is emty to clear the form after successful transction
                        $errorcl = 'alert-success';
                        $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_IQAMA_SUCCESS, $iktUserData['C_id'], array('iktissab_card_no' => $iktUserData['C_id'], 'message' => $data['message'], 'session' => $iktUserData));
                        $message = $this->get('translator')->trans($data['message']);
                        // return $this->render('account/' . $country_file_ext . '/iqamassn.html.twig',
                        return $this->render('account/iqamassn.html.twig',
                            array('form' => $form->createView(), 'message' => $message, 'errorcl' => $errorcl)
                        );
                    }
                    if ($data['success'] == false) {
                        if ($data['status'] == 1) {
                            $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_IQAMA_ERROR, $iktUserData['C_id'], array('iktissab_card_no' => $iktUserData['C_id'], 'message' => $data['message'], 'session' => $iktUserData));
                            $message = $this->get('translator')->trans($data['message']);
                            $errorcl = 'alert-danger';
                            return $this->render('account/iqamassn.html.twig',
                                array('form' => $form->createView(), 'message' => $message, 'errorcl' => $errorcl)
                            );
                        } else {
                            $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_IQAMA_ERROR, $iktUserData['C_id'], array('iktissab_card_no' => $iktUserData['C_id'], 'message' => $data['message'], 'session' => $iktUserData));
                            $message = $this->get('translator')->trans($data['message']);
                            $errorcl = 'alert-danger';
                            return $this->render('account/iqamassn.html.twig',
                                array('form' => $form->createView(), 'message' => $message, 'errorcl' => $errorcl)
                            );
                        }
                    }
                }
                else
                {
                    $message = $this->get('translator')->trans('Unable to update your information');
                    $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_IQAMA_ERROR, $iktUserData['C_id'], array('iktissab_card_no' => $iktUserData['C_id'], 'message' => $message, 'session' => $iktUserData));
                    $errorcl = 'alert-danger';
                    return $this->render('account/iqamassn.html.twig',
                        array('form' => $form->createView(), 'message' => $message, 'errorcl' => $errorcl)
                    );
                }
            }
            else
            {
                $message = $this->get('translator')->trans('');
                $errorcl = '';
                return $this->render('account/iqamassn.html.twig',
                    array('form' => $form->createView(),'message' => $message, 'errorcl' => $errorcl)
                );
            }
        }
        catch (\Exception $e)
        {
            $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_IQAMA_ERROR, 0 , array('iktissab_card_no' => 0, 'message' => $e->getMessage(), 'session' => $iktUserData));
            $message =  $this->get('translator')->trans('An invalid exception occurred');
            $errorcl = 'alert-danger';
            return $this->render('account/iqamassn.html.twig',
                array('form' => $form->createView(), 'iktData' => $iktUserData,   'message' => $message, 'errorcl' => $errorcl)
            );
        }
        catch (AccessDeniedException $ed)
        {
            $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_IQAMA_ERROR, 0 , array('iktissab_card_no' => 0, 'message' => $ed->getMessage(), 'session' => $iktUserData));
            $message = $this->get('translator')->trans($ed->getMessage());
            $errorcl = 'alert-danger';
            return $this->render('account/iqamassn.html.twig',
                array('form' => $form->createView(), 'iktData' => $iktUserData,   'message' => $message, 'errorcl' => $errorcl)
            );
        }
    }



    /**
     * @Route("/{_country}/{_locale}/account/updatepassword" , name="front_account_updatepassword")
     * @param Request $request
     * @return Response
     */
    public function updatepasswordAction(Request $request)
    {
        $activityLog = $this->get('app.activity_log');

        try {



            $iktUserData = $this->get('session')->get('iktUserData');

            $tokenStorage = $this->get('security.token_storage');
            $form = $this->createForm(UpdatePasswordType::class, array() ,array() );




            //exit;

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
            //print_r($postData);exit;
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
                    if ($password_current != $old_password) {
                        $message = $this->get('translator')->trans('Please enter correct old password');
                        $errorcl = 'alert-danger';
                        return $this->render('account/updatepassword.html.twig',
                            array('form1' => $form->createView(), 'message' => $message , 'errorcl' => $errorcl));
                    }
                    if ($new_password == $old_password) {
                        $message = $this->get('translator')->trans('Your new and old password must not be the same');
                        $errorcl = 'alert-danger';
                        return $this->render('account/updatepassword.html.twig',
                            array('form1' => $form->createView(), 'message' => $message , 'errorcl' => $errorcl ));
                    }

                    $new_md5_password = md5($form->get('new_password')->getData());
                    // here when the offline db password is update then udate online
                    $password_changed = $this->updatePasswordOffline($request , $new_md5_password);
                    if($password_changed == true) {
                        $userInfoLoggedIn->setPassword($new_md5_password);
                        $em = $this->getDoctrine()->getManager();
                        $em->persist($userInfoLoggedIn);
                        $em->flush();

                        $tokenStorage->getToken()->getUser()->setPassword($new_md5_password);
                        $messageLog = $this->get('translator')->trans('Your password is updated successfully');

                        $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_PASSWORD_SUCCESS, $iktUserData['C_id'], array('iktissab_card_no' => $iktUserData['C_id'], 'message' => $messageLog, 'session' => $iktUserData));
                        $message = $this->get('translator')->trans('Your password is updated successfully');
                        // update password in the offline database

                        $errorcl = 'alert-success';
                        return $this->render('account/updatepassword.html.twig',
                            array('form1' => $form->createView(), 'message' => $message, 'errorcl' => $errorcl));
                    }else
                    {
                        $messageLog = $this->get('translator')->trans('Unable to update record');
                        $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_PASSWORD_ERROR, $iktUserData['C_id'], array('iktissab_card_no' => $iktUserData['C_id'], 'message' => $messageLog, 'session' => $iktUserData));
                        $message = $this->get('translator')->trans('Unable to update record');
                        // update password in the offline database

                        $errorcl = 'alert-danger';
                        return $this->render('account/updatepassword.html.twig',
                            array('form1' => $form->createView(), 'message' => $message, 'errorcl' => $errorcl));

                    }
                }
                else
                {
                    $message = $this->get('translator')->trans('Unable to update record');
                    $errorcl = 'alert-danger';
                    $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_PASSWORD_ERROR, $iktUserData['C_id'], array('iktissab_card_no' => $iktUserData['C_id'], 'message' => $message, 'session' => $iktUserData));
                    return $this->render('account/updatepassword.html.twig',
                        array('form1' => $form->createView(), 'message' => $message , 'errorcl' => $errorcl));
                }
            }
            else
            {
                $message = '';
                $errorcl = 'alert-danger';
                return $this->render('account/updatepassword.html.twig',
                    array('form1' => $form->createView(), 'message' => $message , 'errorcl' => $errorcl));
            }
        }
        catch (\Exception $e)
        {
            $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_PASSWORD_ERROR, $iktUserData['C_id'], array('iktissab_card_no' => $iktUserData['C_id'], 'message' => $e->getMessage(), 'session' => $iktUserData));
            $errorcl = 'alert-danger';
            $message = $this->get('translator')->trans('An invalid exception occurred');
            return $this->render('account/updatepassword.html.twig',
                array('form1' => $form->createView(), 'message' => $message , 'errorcl' => $errorcl));
        }


    }

    


    /**
     * @Route("/{_country}/{_locale}/account/update" , name="account_update")
     * @param Request $request
     * @return Response
     */
    public function updateProfileAction(Request $request)
    {
        $activityLog = $this->get('app.activity_log');
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('homepage',array('_country' => $request->cookies->get(AppConstant::COOKIE_COUNTRY), '_locale' => $request->cookies->get(AppConstant::COOKIE_LOCALE)));
        }
        try
        {
            $user = $this->get('session')->get('iktUserData');
            $restClient = $this->get('app.rest_client')->IsAuthorized(true);
            $url = $request->getLocale() . '/api/cities_areas_and_jobs.json';
                $cities_jobs_area = $restClient->restGet(AppConstant::WEBAPI_URL . $url, array('Country-Id' => strtoupper($request->get('_country'))));


            if(!empty($cities_jobs_area) && $cities_jobs_area !="") {
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
                    $areasArranged[$value['name']] = $value['name'];
                }
                $areasArranged['-1'] = '-1';
                $areas = $this->json($cities_jobs_area['areas']);
            }
            else
            {
                $citiesArranged=null;
                $jobsArranged=null;
                $areasArranged= null;
            }
            $birthdate = explode('/', $user['birthdate']);
            if ($birthdate[2] > 1850) {
                $dateType = 'g';
                $date = \DateTime::createFromFormat("d/m/Y", $user['birthdate']);
                $dob = $date->format(AppConstant::DATE_FORMAT);
                $dob_h = '';
            } else {
                $dateType = 'h';
                $dob = '';
                $date = \DateTime::createFromFormat("d/m/Y", $user['birthdate']);
                $dob_h = $date->format(AppConstant::DATE_FORMAT);
            }
            $dataArr = array(
                'date_type' => $dateType,
                'job_no' => $user['job_no'],
                'maritial_status' => $user['marital_status_en'][0],
                'language' => $user['lang'],
                'city_no' => $user['city_no'],
                'pur_group' => $user['pur_grp'],
                'dob' => new \DateTime($dob),
                'dob_h' => new \DateTime($dob_h),
            );

            $form = $this->createForm(IktUpdateType::class, $dataArr, array(
                    'additional' => array(
                        'locale' => $request->getLocale(),
                        'country' => $request->get('_country'),
                        'cities' => $citiesArranged,
                        'jobs' => $jobsArranged,
                        'areas' => $areasArranged,
                    )
                )
            );

            $reference_year = array('gyear' => 2017, 'hyear' => 1438);
            $current_year = date('Y');
            $islamicYear = ($current_year - $reference_year['gyear']) + $reference_year['hyear'];
            $form->handleRequest($request);
            $pData = $form->getData();

            if ($form->isSubmitted() && $form->isValid()) {

                if ($pData['date_type'] == 'g') {
                    $dateBirth = $pData['dob']->format('Y-m-d');
                } else {
                    $dateBirth = $pData['dob_h']->format('Y-m-d');
                }
                $profileFields = array(
                    "birthdate" => array('old_value' => $user['birthdate'], 'new_value' => $dateBirth),
                    "Marital_status" => array('old_value' => $user['marital_status_en'][0], 'new_value' => $pData['maritial_status']),
                    "job_no" => array('old_value' => $user['job_no'], 'new_value' => $pData['job_no']),
                    "city_no" => array('old_value' => $user['city_no'], 'new_value' => $pData['city_no']),
                    "area" => array('old_value' => $user['area'], 'new_value' => ($pData['area_no'] == '-1') ? $pData['area_text'] : $pData['area_no']),
                    "lang" => array('old_value' => $user['lang'], 'new_value' => ($pData['language'])),
                    "pur_grp" => array('old_value' => $user['pur_grp'], 'new_value' => ($pData['pur_group'])),
                );
                // now pass only those fields which are changed and are not empty
                $count = 0;
                foreach ($profileFields as $key => $val) {
                    if ($val['new_value'] != '' && ($val['new_value'] != $val['old_value'])) {
                        $form_data[$count] = array(
                            'C_id' => $user['C_id'],
                            'field' => $key,
                            'new_value' => $val['new_value'],
                            'old_value' => $val['old_value'],
                            'comments' => 'Update registered user details for field ' . $key
                        );
                    }
                    $count++;
                }

                    $activityLog = $this->get('app.activity_log');
                    $postData = json_encode($form_data);
                    $url = $request->getLocale() . '/api/update_user_detail.json';
                    $data = $restClient->restPostForm(AppConstant::WEBAPI_URL . $url, $postData, array('Country-Id' => strtoupper($request->get('_country'))));

                    if($data['success'] == true) {
                        // we need to update the session data to reflect the
                        // changes after profile update
                        $restClient = $this->get('app.rest_client')->IsAuthorized(true);
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
                        $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_USERINFO_SUCCESS, $user['C_id'], $form_data);
                        $message = $this->get('translator')->trans('Profile has been updated');
                        $errorcl = 'alert-success';
                        return $this->render('/account/update.html.twig',
                            array(
                                'form' => $form->createView(),
                                'areas' => $areas,
                                'errorcl' => $errorcl,
                                'islamicyear' => $islamicYear,
                                'message' => $message,
                            )
                        );
                    }
                    else
                    {
                        $message = $this->get('translator')->trans('Unable to update record');
                        $errorcl = 'alert-danger';
                        return $this->render('/account/update.html.twig',
                            array(
                                'form' => $form->createView(),
                                'areas' => $areas,
                                'errorcl' => $errorcl,
                                'islamicyear' => $islamicYear,
                                'message' => $message,
                            )
                        );
                    }

            }
            else {

                    $message = $this->get('translator')->trans('');
                    return $this->render('/account/update.html.twig',
                        array
                        (
                            'form'        => $form->createView(),
                            'areas'       => $areas,
                            'errorcl'     => 'alert-danger',
                            'islamicyear' => $islamicYear,
                            'message'     => $message,
                        )
                    );
            }
        }
        catch(\Exception $e)
        {
            $message = $this->get('translator')->trans('An invalid exception occurred');
            $errorcl = 'alert-danger';
            return $this->render('account/error.html.twig',
                array
                (
                    'message' => $message,
                    'errorcl'     => 'alert-danger'
                )
            );

        }
        catch(AccessDeniedException $ed)
        {
            $message = $this->get('translator')->trans($ed->getMessage());
            return $this->render('account/error.html.twig',
                array
                (
                    'message' => $message,
                    'errorcl'     => 'alert-danger'
                )
            );
        }



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
        $activityLog = $this->get('app.activity_log');
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('homepage',array('_country' => $request->cookies->get(AppConstant::COOKIE_COUNTRY), '_locale' => $request->cookies->get(AppConstant::COOKIE_LOCALE)));
        }
        try
        {
            $restClient  = $this->get('app.rest_client')->IsAuthorized(true);
            $iktUserData = $this->get('session')->get('iktUserData');
            $country_id  = $this->getCountryId($request);
            //  var_dump($iktUserData);
            $posted = array();
            $iktCardNo = $iktUserData['C_id'];
            $iktID_no     = $iktUserData['ID_no'];
            $iktMobile_no = $iktUserData['mobile'];
            $language    = $request->getLocale();
            //  echo '===='.$this->get('session')->get('userSelectedCountry');
            $form = $this->createForm(MissingCardType::class, array() ,
                array('extras' => array('iktID_no'  => $iktID_no, 'country' => $country_id)
            ));
            $data = $request->request->all();
            $url  = $request->getLocale() . '/api/update_lost_card.json';

            if(!empty($data))
            {
                /************************/
                if($data['missing_card']['new_iktissab_id']['first'] == $iktCardNo) {
                    $message = $this->get('translator')->trans('New Iktissab id and old Iktissab id must not be same');
                    $error = 'alert-danger';
                    return $this->render('account/missingcard.html.twig',
                        array('form' => $form->createView(), 'message' => $message, 'errorcl' => $error)
                    );
                }
                /************************/
                $form_data = array(
                    'ID_no'     => $iktID_no,
                    'field'     => 'lostcard',
                    'old_value' => $iktCardNo,
                    'new_value' => $data['missing_card']['new_iktissab_id']['first'],
                    'comments'  => $data['missing_card']['comment_missingcard'],
                    'mobile'    => $iktMobile_no
                );

                $postData = json_encode($form_data);
                $request->get('_country');
                $data = $restClient->restPostForm(AppConstant::WEBAPI_URL . $url, $postData, array('Country-Id' => strtoupper($request->get('_country'))));
                //dump($data); exit;// return data fromt the webservice
                if(!empty($data)) {
                    if ($data['success'] == true) {
                        // posted array is emty to clear the form after successful transction
                        $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_MISSINGCARD_SUCCESS, $iktUserData['C_id'], array('iktissab_card_no' => $iktUserData['C_id'], 'message' => $data['message'], 'session' => $iktUserData));
                        $message = $this->get('translator')->trans($data['message']);
                        $error = 'alert-success';
                        return $this->render('account/missingcard.html.twig',
                            array('form' => $form->createView(), 'message' => $message, 'errorcl' => $error)
                        );
                    } elseif ($data['success'] == false) {
                        if ($data['status'] == 1) {
                            $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_MISSINGCARD_ERROR, $iktUserData['C_id'], array('iktissab_card_no' => $iktUserData['C_id'], 'message' => $data['message'], 'session' => $iktUserData));
                            $message = $this->get('translator')->trans($data['message']);
                            $error = 'alert-danger';
                            return $this->render('account/missingcard.html.twig',
                                array('form' => $form->createView(), 'message' => $message, 'errorcl' => $error)
                            );
                        } else {
                            // Here INVALID_DATA  will come with status 0
                            $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_MISSINGCARD_ERROR, $iktUserData['C_id'], array('iktissab_card_no' => $iktUserData['C_id'], 'message' => $data['message'], 'session' => $iktUserData));
                            $message = $this->get('translator')->trans($data['message']);
                            $error = 'alert-danger';
                            return $this->render('account/missingcard.html.twig',
                                array('form' => $form->createView(), 'message' => $message, 'errorcl' => $error)
                            );
                        }
                    } else {
                        $message = $this->get('translator')->trans('');
                        $error = '';
                        return $this->render('account/missingcard.html.twig',
                            array('form' => $form->createView(), 'message' => $message, 'errorcl' => $error));
                    }
                }else
                {
                    $message = $this->get('translator')->trans('Unable to update record');
                    $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_MISSINGCARD_ERROR, $iktUserData['C_id'], array('iktissab_card_no' => $iktUserData['C_id'], 'message' => $message, 'session' => $iktUserData));
                    $error = 'alert-danger';
                    return $this->render('account/missingcard.html.twig',
                        array('form' => $form->createView(), 'message' => $message, 'errorcl' => $error)
                    );
                }
            }
            else
            {
                $message = $this->get('translator')->trans('');
                $error = '';
                return $this->render('account/missingcard.html.twig',
                    array('form' => $form->createView(),  'message' => $message, 'errorcl' => $error)
                );
            }
        }
        catch(\Exception $e)
        {
            $message = $e->getMessage();
            $message = $this->get('translator')->trans('Unable to process your request at this time.Please try later') ;
            $error   = 'alert-danger';
            $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_MISSINGCARD_ERROR, 0 , array('iktissab_card_no' => '', 'message' => $e->getMessage(), 'session' => '' ));
            return $this->render('account/missingcard.html.twig',
                array('form' => $form->createView(),  'message' => $message, 'errorcl' => $error)
            );
        }
        catch(AccessDeniedException $ed)
        {
            $message =  $this->get('translator')->trans('Unable to process your request at this time.Please try later');
            $errorcl = 'alert-danger';
            $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_MISSINGCARD_ERROR, 0 , array('iktissab_card_no' => '', 'message' => $ed->getMessage(), 'session' => '' ));
            return $this->render('account/missingcard.html.twig',
                array('form' => $form->createView(),  'message' => $message, 'errorcl' => $errorcl)
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
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('homepage',array('_country' => $request->cookies->get(AppConstant::COOKIE_COUNTRY), '_locale' => $request->cookies->get(AppConstant::COOKIE_LOCALE)));
        }
        try {


            if ($request->query->get('draw')) {
                $page = $request->query->get('draw');
            }
            $restClient = $this->get('app.rest_client')->IsAuthorized(true);
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
     * @Route("/{_country}/{_locale}/account/sendpassword_14_sept", name="send_password_14_sept")
     * Request $request
     */
    public function sendPwd_14_septAction(Request $request)
    {
        // Iktissab pincode recovery
        // we need to be logged in first
        $activityLog = $this->get('app.activity_log');
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('homepage',array('_country' => $request->cookies->get(AppConstant::COOKIE_COUNTRY), '_locale' => $request->cookies->get(AppConstant::COOKIE_LOCALE)));
        }
        $form = $this->createForm(SendPwdType::class, array(), array(
            'additional'  => array(
                'locale'  => $request->getLocale(),
                'country' => $request->get('_country'),
            )));

        try
        {
            $error = array('success' => true, 'message' => '');
            $restClient = $this->get('app.rest_client')->IsAuthorized(true);
            $smsService = $this->get('app.sms_service');

            $form = $this->createForm(SendPwdType::class, array(), array(
                'additional' => array(
                    'locale' => $request->getLocale(),
                    'country' => $request->get('_country'),
                )));
            $form->handleRequest($request);
            $pData = $form->getData();
            $data  = $request->request->all();
            $logged_user_data = $this->get('session')->get('iktUserData');
            if ($form->isSubmitted() && $form->isValid()) {
                try
                {
                    if($request->get('_country') == 'sa')
                    {
                        $validate_Iqama = $this->validateIqama($data['send_pwd']['iqama']);
                        if ($validate_Iqama == false) {
                            $message = "";
                            $error['success'] = false;
                            $error['message'] = $this->get('translator')->trans('Invalid Iqama Id/SSN Number' . $request->get('_country'));
                            return $this->render('default/send_pwd.twig',
                                array(
                                    'form' => $form->createView(),
                                    'error' => $error,
                                    'message' => $message
                                )
                            );
                        }
                    }

                    $accountEmail = $this->iktExist($pData['iktCardNo']);
                    $logged_user_data = $this->get('session')->get('iktUserData');
                    // print_r($logged_user_data);
                    $logged_user_data['C_id'];
                    $logged_user_data['ID_no'];
                    $pData['iqama'];
                    if ($pData['iqama'] != $logged_user_data['ID_no']) {
                        $error['success'] = false;
                        $error['message'] = $this->get('translator')->trans('Invalid Iqama Id/SSN Number'.$request->get('_country'));
                    } elseif ($pData['iktCardNo'] != $logged_user_data['C_id']) {
                        $error['success'] = false;
                        $error['message'] = $this->get('translator')->trans('Please enter valid Iktissab card number');
                    } else {
                        $url = $request->getLocale() . '/api/' . $pData['iktCardNo'] . '/sendsms/' . $pData['iqama'] . '.json';
                        // echo AppConstant::WEBAPI_URL.$url;
                        $data_user = $restClient->restGet(AppConstant::WEBAPI_URL . $url, array('Country-Id' => strtoupper($request->get('_country'))));
                        // print_r($data_user);exit;
                        if (!empty($data_user)) {
                            if ($data_user['success'] == true) {
                                $message = $data_user['message'];
                                $acrivityLog = $this->get('app.activity_log');
                                $acrivityLog->logEvent(AppConstant::ACTIVITY_FORGOT_PASSWORD_ERROR, 1, array('message' => $message, 'session' => $logged_user_data));
                                $error['success'] = true;
                                $error['message'] = $this->get('translator')->trans('You will recieve sms on your mobile number **** %s', ["%s" => substr($logged_user_data['mobile'], 8, 12)]);

                            } else {
                                $error['success'] = false;
                                $error['message'] = $data['message'];
                            }
                        } else {
                            $error['success'] = false;
                            $error['message'] = $this->get('translator')->trans('Unable to process your request at this time.Please try later');
                        }
                    }

                } catch (\Exception $e) {
                    $e->getMessage();
                    $error['success'] = false;
                    $acrivityLog = $this->get('app.activity_log');
                    $acrivityLog->logEvent(AppConstant::ACTIVITY_FORGOT_PASSWORD_ERROR, 1, array('message' => $e->getMessage(), 'session' => $logged_user_data));
                    $error['message'] = $this->get('translator')->trans('Unable to process your request at this time.Please try later');
                }
            }

            $message = '';
            return $this->render('/default/send_pwd.twig',
                array(
                    'form' => $form->createView(),
                    'error' => $error,
                    'message' => $message,
                )
            );

        }
        catch(\Exception $e){
            $message = $this->get('translator')->trans('An invalid exception occurred');
            $error['success'] = false;
            $error['message'] = $message;
            return $this->render('/default/send_pwd.twig',
                array(
                    'form' => $form->createView(),
                    'error' => $error,
                )
            );
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
        try
        {
            $error = array('success' => true, 'message' => '');
            $restClient = $this->get('app.rest_client');
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


            if ($form->isSubmitted() && $form->isValid())
            {
                try
                {
                    $captchaCode           = trim(strtoupper($this->get('session')->get('_CAPTCHA')));
                    // echo '<br>';
                    $captchaCodeSubmitted  = trim(strtoupper($form->get('captchaCode')->getData()));
                    $filename      = $commFunct->saveTextAsImage();
                    $response->setContent($filename['filename']);
                    $captcha_image = $filename['image_captcha'];
                    if($captchaCodeSubmitted != $captchaCode)
                    {
                        $error_cl         = 'alert-danger';
                        $error['success'] = false;
                        $message = "";
                        $error['message'] = $this->get('translator')->trans('Invalid captcha code');
                        return $this->render('/account/forgot_email.twig',
                            array
                            (
                                'form'    => $form->createView(),
                                'error'   => $error,
                                'data'    => $captcha_image
                            )
                        );
                    }


                    if( $request->get('_country') == 'sa') {
                        $validate_Iqama = $this->validateIqama($data['forgot_email']['iqama']);
                        if ($validate_Iqama == false) {
                            $message = "";
                            $error['success'] = false;
                            $error['message'] = $this->get('translator')->trans('Invalid Iqama Id/SSN Number'.$request->get('_country'));
                            return $this->render('/account/forgot_email.twig',
                                array (
                                    'form'  => $form->createView(),
                                    'error' => $error,
                                    'data'  => $captcha_image
                                )
                            );
                        }
                    }

                    $accountEmail = $this->iktExist($pData['iktCardNo']);

                    $url = $request->getLocale() . '/api/' . $pData['iktCardNo'] . '/userdata.json';
                    // echo AppConstant::WEBAPI_URL.$url;
                    $data = $restClient->restGet(AppConstant::WEBAPI_URL . $url, array('Country-Id' => strtoupper($request->get('_country'))));
                    //print_r($data);exit;
                    if (!empty($data)) {
                        if ($data['success'] == true) {
                            // match the iqama numbers ( from form and other from the local data)
                            if ($pData['iqama'] != $data['user'][0]) {
                                $error['success'] = false;
                                $form->get('iqama')->addError(new FormError($this->get('translator')->trans('Invalid Iqama Id/SSN Number'.$request->get('_country'))));
                            } else {
                                $message = $this->get('translator')->trans("Your account registration email is %s", ["%s" => $accountEmail]);
                                $acrivityLog = $this->get('app.activity_log');
                                // send sms code
                                $status_sms = $smsService->sendSms($data['user'][1], $message, $request->get('_country'));
                                if($status_sms == 1) {
                                    $acrivityLog->logEvent(AppConstant::ACTIVITY_FORGOT_EMAIL_SMS, 1, array('message' => $message, 'session' => $data['user']));
                                    $error['success'] = true;
                                    $error['message'] = $this->get('translator')->trans('You will recieve sms on your mobile number **** %s', ["%s" => substr($data['user'][1], 8, 12)]);
                                }else{
                                    $acrivityLog->logEvent(AppConstant::ACTIVITY_FORGOT_EMAIL_SMS, 0, array('message' => $message, 'session' => $data['user']));
                                    $error['success'] = false;
                                    $error['message'] = $this->get('translator')->trans('Unable to process your request at this time.Please try later');

                                }
                            }
                        } else {
                            $error['success'] = false;
                            $error['message'] = $this->get('translator')->trans('Please enter valid Iktissab id and iqama/SSN'.$request->get('_country'));
                        }
                    } else {
                        $error['success'] = false;
                        $error['message'] = $this->get('translator')->trans('Unable to update record');
                    }

                } catch (\Exception $e) {
                    $error['success'] = false;
                    $error['message'] = $e->getMessage();
                }

                $filename      = $commFunct->saveTextAsImage();
                $response->setContent($filename['filename']);
                $captcha_image = $filename['image_captcha'];
                return $this->render('/account/forgot_email.twig',
                    array
                    (
                        'form'    => $form->createView(),
                        'error'   => $error,
                        'data'    => $captcha_image

                    )
                );
            }
            else
            {
                $filename      = $commFunct->saveTextAsImage();
                $response->setContent($filename['filename']);
                $captcha_image = $filename['image_captcha'];
                $message = "";
                return $this->render('/account/forgot_email.twig',
                    array(
                        'form' => $form->createView(),
                        'error' => $error,
                        'data'    => $captcha_image
                    )
                );

            }

        }
        catch(\Exception $e)
        {
            $message = $this->get('translator')->trans('An invalid exception occurred');
            $error['success'] = false;
            $error['message'] = $message;
            return $this->render('/account/forgot_email.twig',
                array(
                    'form' => $form->createView(),
                    'error' => $error,
                    'data'    => $captcha_image

                )
            );
        }
        catch (AccessDeniedException $ed)
        {

            $message = $this->get('translator')->trans($ed->getMessage());
            $error['success'] = false;
            $error['message'] = $message;

            return $this->render('/account/forgot_email.twig',
                array(
                    'form' => $form->createView(),
                    'error' => $error,
                    'data'    => $captcha_image

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
            $password = $password;
            if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY'))
            {
                return $this->redirectToRoute('login',array('_country' => $request->cookies->get(AppConstant::COOKIE_COUNTRY), '_locale' => $request->cookies->get(AppConstant::COOKIE_LOCALE)));
            }
            $activityLog = $this->get('app.activity_log');
            $country_id  = $request->get('_country');
            $restClient  = $this->get('app.rest_client')->IsAuthorized(true);
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
            $activityLog = $this->get('app.activity_log');
            $country_id  = $request->get('_country');
            $this->getUser()->getIktCardNo();
            $restClient  = $this->get('app.rest_client')->IsAuthorized(true);
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
        // todo: getEntityManager() is deprecated
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
            //return $this->redirect($this->generateUrl('account_logout', array('_country' => $userCountry, '_locale' => $cookieLocale)));
            $this->redirect($this->generateUrl('account_logout', array('_country' => $userCountry, '_locale' => $cookieLocale)));
        }
        else
        {
            //echo 'sdf';
            //$url = $this->generateUrl('account_home', array('_country' => $userCountry, '_locale' => $cookieLocale));
            return $this->redirectToRoute('account_home', array('_country' => $userCountry, '_locale' => $cookieLocale));

            //return $this->redirect($this->generateUrl('homepage', array('_country' => $userCountry, '_locale' => $cookieLocale)));
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