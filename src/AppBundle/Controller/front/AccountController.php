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
use AppBundle\Entity\User;
use AppBundle\Form\ForgotEmailType;



use AppBundle\Form\IktUpdateType;
use AppBundle\Form\MobileType;
use AppBundle\Form\UpdateEmailType;
use AppBundle\Form\UpdateFullnameType;
use AppBundle\Form\UpdatePasswordType;
use AppBundle\Form\MissingCardType;
use AppBundle\HijriGregorianConvert;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
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


class AccountController extends Controller
{

    /**
     * @Route("/{_country}/{_locale}/account/home", name="account_home")
     */

    public function myAccountAction(Request $request)
    {
        $response  = new Response();
        $commFunct = new FunctionsController();
        /****************/
         $response  = new Response();
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
             if($userLang == $locale) {
                 $commFunct->changeLanguage($request, $userLang);
                 $locale = $request->getLocale();
             } else {
                 if($request->cookies->get(AppConstant::COOKIE_LOCALE)){
                     return $this->redirect($this->generateUrl('homepage', array('_country' => $request->cookies->get(AppConstant::COOKIE_COUNTRY), '_locale' => $request->cookies->get(AppConstant::COOKIE_LOCALE))));
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
                     return $this->redirect($this->generateUrl('homepage', array('_country' => $request->cookies->get(AppConstant::COOKIE_COUNTRY), '_locale' => $request->cookies->get(AppConstant::COOKIE_LOCALE))));
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


        /****************/










        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            echo $commFunct->checkSessionCookies($request) ;
            exit;
            if ($commFunct->checkSessionCookies($request) == false) {
                //return $this->redirect($this->generateUrl('landingpage'));
            }
            else
            {

                //return $this->redirectToRoute('homepage', array('_country' => $request->cookies->get(AppConstant::COOKIE_COUNTRY), '_locale' => $request->cookies->get(AppConstant::COOKIE_LOCALE)));
            }
        }else {


            $restClient = $this->get('app.rest_client');
            if (!$this->get('session')->get('iktUserData')) {
                $url = $request->getLocale() . '/api/' . $this->getUser()->getIktCardNo() . '/userinfo.json';
                // echo AppConstant::WEBAPI_URL.$url;
                $data = $restClient->restGet(AppConstant::WEBAPI_URL . $url, array('Country-Id' => strtoupper($request->get('_country'))));
                if ($data['success'] == "true") {
                    $this->get('session')->set('iktUserData', $data['user']);
                }
            }

            $url = $request->getLocale() . '/api/' . $this->getUser()->getIktCardNo() . '/userinfo.json';

            // echo AppConstant::WEBAPI_URL.$url;

            $data = $restClient->restGet(AppConstant::WEBAPI_URL . $url, array('Country-Id' => strtoupper($request->get('_country'))));


            $iktUserData = $this->get('session')->get('iktUserData');
            //var_dump($iktUserData);
            return $this->render('/account/home.html.twig',
                array('iktData' => $iktUserData)
            );
        }
    }





    /**
     * @Route("/{_country}/{_locale}/account/info", name="front_account_info")
     */

    public function accountInfoAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('homepage',array('_country' => $request->cookies->get(AppConstant::COOKIE_COUNTRY), '_locale' => $request->cookies->get(AppConstant::COOKIE_LOCALE)));
        }
        $restClient = $this->get('app.rest_client');
        if(!$this->get('session')->get('iktUserData'))
        {
            $url = $request->getLocale() . '/api/' . $this->getUser()->getIktCardNo() . '/userinfo.json';
            // echo AppConstant::WEBAPI_URL.$url;
            $data = $restClient->restGet(AppConstant::WEBAPI_URL.$url, array('Country-Id' => strtoupper($request->get('_country'))));
            if($data['success'] == "true") {
                $this->get('session')->set('iktUserData', $data['user']);
            }
        }
        $iktUserData = $this->get('session')->get('iktUserData');
        return $this->render('/account/accountinfo.html.twig',
            array('iktData' => $iktUserData)
        );
    }


    /**
     * @Route("/{_country}/{_locale}/account/personalinfo", name="front_account_personalinfo")
     */

    public function personalInfoAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('homepage',array('_country' => $request->cookies->get(AppConstant::COOKIE_COUNTRY), '_locale' => $request->cookies->get(AppConstant::COOKIE_LOCALE)));
        }
        $restClient = $this->get('app.rest_client');
        if(!$this->get('session')->get('iktUserData'))
        {
            $url = $request->getLocale() . '/api/' . $this->getUser()->getIktCardNo() . '/userinfo.json';
            // echo AppConstant::WEBAPI_URL.$url;
            $data = $restClient->restGet(AppConstant::WEBAPI_URL.$url, array('Country-Id' => strtoupper($request->get('_country'))));
            if($data['success'] == "true") {
                $this->get('session')->set('iktUserData', $data['user']);
            }
        }
        $iktUserData = $this->get('session')->get('iktUserData');
        return $this->render('/account/personalinfo.html.twig',
            array('iktData' => $iktUserData)
        );
    }




    /**
     * @Route("/{_country}/{_locale}/account/email", name="front_account_email")
     */
    public function accountEmailAction(Request $request)
    {
        try
        {
            $restClient       = $this->get('app.rest_client');
            $commFunct = new FunctionsController();
            $locale_cookie = $request->getLocale();
            $country_cookie = $request->get('_country');
            $userLang = trim($request->query->get('lang'));
            if ($userLang != '' && $userLang != null) {
                if($userLang == $locale_cookie) {
                    $request->getLocale();
                    $commFunct->changeLanguage($request, $userLang);
                    $locale_cookie = $request->getLocale();
                } else {
                    if($request->cookies->get(AppConstant::COOKIE_LOCALE)){
                        return $this->redirect($this->generateUrl('account_home', array('_country' => $request->cookies->get(AppConstant::COOKIE_COUNTRY), '_locale' => $request->cookies->get(AppConstant::COOKIE_LOCALE))));
                    }
                }
            }

            if($request->cookies->get(AppConstant::COOKIE_LOCALE)) {
                echo '===>>>>'.$cookieLocale = $request->cookies->get(AppConstant::COOKIE_LOCALE);
                $cookieCountry = $request->cookies->get(AppConstant::COOKIE_COUNTRY);
                if (isset($cookieLocale) && $cookieLocale <> '' && $cookieLocale != $locale_cookie) {
                    // modify here if the language is to be changes forom the uprl
                    return $this->redirect($this->generateUrl('account_home', array('_country' => $request->cookies->get(AppConstant::COOKIE_COUNTRY), '_locale' => $request->cookies->get(AppConstant::COOKIE_LOCALE))));
                }
                if (isset($cookieCountry) && $cookieCountry <> '' && $cookieCountry != $country_cookie) {
                    return $this->redirect($this->generateUrl('account_home', array('_country' => $request->cookies->get(AppConstant::COOKIE_COUNTRY), '_locale' => $request->cookies->get(AppConstant::COOKIE_LOCALE))));
                }
            }

            $activityLog      = $this->get('app.activity_log');
            $Country_id       = strtoupper($this->getCountryId($request));
            $iktUserData      = $this->get('session')->get('iktUserData');
            $posted           = array();
            $iktCardNo        = $iktUserData['C_id'];
            $iktID_no         = $iktUserData['ID_no'];
            //get current email of the user
            $currentEmail     = $iktUserData['email'];
            $mobile  = $iktUserData['mobile'];
            $form = $this->createForm(UpdateEmailType::class, array() ,array(
                    'extras'  => array('email'   => $currentEmail)));
            // form posted data
            $data = $request->request->all();
            //print_r($data); exit;
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
                    'comments'  =>   'test comments test comments'
                );
                // print_r($form_data[0]);exit;
                $this->get('session')->set('new_value', $data['update_email']['newemail']['first']);
                // here we will check if the email is already registered on website or not
                $email_val = $this->checkEmail($data['update_email']['newemail']['first'],$Country_id);


                //print_r($email_val);echo $email_val['result']['email'];exit;
                /****/
                //check email exist
                /****/
                if($email_val['success']) {
                    if ($email_val['result']['email']) {
                        $message = $this->get('translator')->trans('The new email is already registered before , please enter a valid email');
                        $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_EMAIL_ERROR, $iktUserData['C_id'] , array('iktissab_card_no' => $iktUserData['C_id'], 'message' => $message, 'session' => $iktUserData));
                        return $this->render('account/email.html.twig',
                            array('form' => $form->createView(), 'message' => $message)
                        );
                    }
                }
                else
                {
                    // check online email before sendin sms
                    // current logged in user iktissab id
                    $iktCardNo    = $iktUserData['C_id'];
                    $iktID_no     = $iktUserData['ID_no'];
                    // get current email of the user
                    $currentEmail = $iktUserData['email'];
                    // form posted data

                    $data = $request->request->all();
                    $form_data = array('email' => $data['update_email']['newemail']['first']);
                    $postData  = json_encode($form_data);
                    // print_r($data);
                    $url  = $request->getLocale() . '/api/checkmail.json';
                    $chk_email = $restClient->restPostForm(AppConstant::WEBAPI_URL . $url, $postData, array('Country-Id' => strtoupper($request->get('_country'))));
                    //print_r($chk_email);
                    if($chk_email['success'] == true)
                    {
                        $message = $this->get('translator')->trans('The new email is already registered before , please enter a valid email');
                        $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_EMAIL_ERROR, $iktUserData['C_id'] , array('iktissab_card_no' => $iktUserData['C_id'], 'message' => $message, 'session' => $iktUserData));
                        return $this->render('account/email.html.twig',
                            array('form' => $form->createView(), 'message' => $message)
                        );
                    }
                    elseif($chk_email['success'] == false && $chk_email['status'] == 0)
                    {
                        $message = $chk_email['message'];
                        $activityLog->logEvent(AppConstant::ACTIVITY_EMAIL_UPDATE_SMS_ERROR, $iktUserData['C_id'] , array('iktissab_card_no' => $iktUserData['C_id'], 'message' =>$message, 'session' => $iktUserData));
                        return $this->render('account/email.html.twig',
                            array('form' => $form->createView(),  'message' => $message)
                        );
                    }
                    else
                    {
                        // sending sms
                        $otp = rand(111111, 999999);
                        $this->get('session')->set('smscode', $otp);
                        $message = "Please enter the verification code you received on your mobile number ******" . substr($mobile, 6, 10) . $otp;
                        $smsmessage = $this->get('translator')->trans("Verification code:") . $otp . $this->get('translator')->trans("Changing account email");
                        $smsService = $this->get('app.sms_service');
                        $MsgID = rand(1, 99999);
                        $msg = $message;  //$this->get('translator')->trans('test_message'); //"welcome to you in mobily.ws ,testing sms service";
                        //$smsResponse = $smsService->sendSmsEmail($mobile, $smsmessage, $request->get('_country'));
                        // revert this code
                        $smsResponse = 1;
                        // exit;
                        if ($smsResponse == 1) {
                            $message_sms = $this->get('translator')->trans('SMS sent successfully');
                            $activityLog->logEvent(AppConstant::ACTIVITY_EMAIL_UPDATE_SMS_SUCCESS, $iktUserData['C_id'], array('iktissab_card_no' => $iktUserData['C_id'], 'message' => $message_sms, 'session' => $iktUserData));
                            return $this->render('account/sendsms.html.twig',
                                array('form' => $form->createView(), 'message' => $message)
                            );
                        } else {
                            $message = $this->get('translator')->trans('SMS not sent.Please try again');
                            $activityLog->logEvent(AppConstant::ACTIVITY_EMAIL_UPDATE_SMS_ERROR, $iktUserData['C_id'], array('iktissab_card_no' => $iktUserData['C_id'], 'message' => $message, 'session' => $iktUserData));
                            return $this->render('account/email.html.twig',
                                array('form' => $form->createView(), 'message' => $message)
                            );
                        }
                    }
                }
            }
            else
            {
                $message = $this->get('translator')->trans('');
                return $this->render('account/email.html.twig',
                    array('form' => $form->createView(),'message' => '')
                );
            }
        }
        catch (\Exception $e)
        {
            // $message = $this->get('translator')->trans('An invalid exception occurred');
            $message = $e->getMessage();
            $activityLog->logEvent(AppConstant::ACTIVITY_EMAIL_UPDATE_SMS_ERROR, $iktUserData['C_id'] , array('iktissab_card_no' => $iktUserData['C_id'], 'message' =>$message, 'session' => $iktUserData));
            return $this->render('account/email.html.twig',
                array('form' => $form->createView(),  'message' => $message)
            );
        }

    }


    /**
     * @Route("/{_country}/{_locale}/account/smsverification", name="front_account_sms_verification")
     */
    public function accountSMSVerificationAction(Request $request)
    {
        try
        {
            $activityLog  = $this->get('app.activity_log');
            $restClient   = $this->get('app.rest_client');
            $Country_id   = strtoupper($this->getCountryId($request));
            $iktUserData  = $this->get('session')->get('iktUserData');
            //current logged in user iktissab id
            $iktCardNo    = $iktUserData['C_id'];
            $iktID_no     = $iktUserData['ID_no'];
            // get current email of the user
            $currentEmail = $iktUserData['email'];
            // form posted data

            $data = $request->request->all();
            // print_r($data);
            $url  = $request->getLocale() . '/api/update_user_email.json';
            $code = $this->get('session')->get('smscode');
            $val  = $data['smsverify'];
            if($val == $code) {
                $comments = '';
                $form_data[0]   =  array(
                    'C_id'      => $iktCardNo,
                    'field'     => 'email',
                    'new_value' => $this->get('session')->get('new_value'),
                    'old_value' => $currentEmail,
                    'comments'  => $comments
                );
                $postData = json_encode($form_data);
                //print_r($postData);
                //exit;
                $data = array();
                $data = $restClient->restPost(AppConstant::WEBAPI_URL . $url, $postData, array('Country-Id' => strtoupper($request->get('_country'))));
                // echo $data['update_fields'];
                // var_dump($data);
                // $data["success"] = 'true';
                    if($data["success"] == true)
                    {
                    // $this->get('session')->set('iktUserData', $data['user']);
                        $email_val = $this->updateEmail($this->get('session')->get('new_value'), $Country_id, $iktCardNo);
                        if ($email_val == '1') {
                            if ($request->getLocale() == 'ar') {
                                $first = 'تم تحديث بريدكم الإلكتروني. نأمل استخدام البريد';
                                $last = 'عند الدخول إلى الموقع مرة أخرى';
                                $message = $last . '( ' . $this->get('session')->get("new_value") . ' )' . $first;
                                // todo:enable it
                                // $message = $this->get('translator')->trans('Your email is updated. Next time, please use the ( ' . $this->get('session')->get("new_value") . ') address for signing to the website  ');
                            } else {
                                $message = $this->get('translator')->trans('Your email is updated. Next time, please use the ( ' . $this->get('session')->get("new_value") . ') address for signing to the website  ');
                            }
                            $tokenStorage = $this->get('security.token_storage');
                            $tokenStorage->getToken()->getUser()->setUsername($this->get('session')->get("new_value"));
                            $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_EMAIL_SUCCESS, $iktUserData['C_id'] , array('iktissab_card_no' => $iktUserData['C_id'], 'message' => $data['message'], 'session' => $iktUserData));
                            return $this->render('account/sendsmssuccess.html.twig',
                                array('userNewEmail' => $this->get('session')->get('new_value'), 'message' => $message)
                            );
                        }
                        elseif ($email_val == '0')
                        {
                            $message = $this->get('translator')->trans('Your email is not updated.Please try again');
                            $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_EMAIL_ERROR, $iktUserData['C_id'] , array('iktissab_card_no' => $iktUserData['C_id'], 'message' => $message, 'session' => $iktUserData));
                            return $this->render('account/sendsmssuccess.html.twig',
                            array('userNewEmail' => $this->get('session')->get('new_value'), 'message' => $message));
                        }
                        else
                        {
                            $message = $this->get('translator')->trans('An invalid exception occurred');
                            $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_EMAIL_ERROR, $iktUserData['C_id'] , array('iktissab_card_no' => $iktUserData['C_id'], 'message' => $message, 'session' => $iktUserData));
                            return $this->render('account/sendsmssuccess.html.twig',
                                array('userNewEmail' => $this->get('session')->get('new_value'), 'message' => $message)
                            );
                        }

                        // $message = $this->get('translator')->trans($data['message']);
                        // return $this->render('account/email.html.twig',
                        //    array(  'message' => $message)
                        // );

                    }
                    elseif($data['success'] == false)
                    {
                        // $this->get('session')->set('iktUserData', $data['user']);
                        if($data['status'] == 1 )
                        {
                            $message = $data['message'];
                            $email = $this->get('session')->get("new_value");
                            $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_EMAIL_ERROR, $iktUserData['C_id'] , array('iktissab_card_no' => $iktUserData['C_id'], 'message' => $message, 'session' => $iktUserData));
                            return $this->render('account/sendsmssuccess.html.twig', array( 'message' => $message  ));
                        }
                        else
                        {
                            // in else status is zero means validation errors
                            // redirect to email
                            $message = $this->get('translator')->trans('Your email address is not updated.Please try again');
                            $message = $data['message'];
                            $email   = $this->get('session')->get("new_value");
                            $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_EMAIL_ERROR, $iktUserData['C_id'] , array('iktissab_card_no' => $iktUserData['C_id'], 'message' => $message, 'session' => $iktUserData));
                            return $this->render('account/sendsmssuccess.html.twig', array( 'message' => $email.' '.$message  ));
                        }
                    }
                    else
                    {
                        $message = $this->get('translator')->trans('An invalid exception occurred');
                        return $this->render('account/sendsmssuccess.html.twig',
                            array('message' => $message)
                        );
                    }
                }
                else
                {
                    $message = $this->get('translator')->trans('Please enter correct verification code');
                    $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_EMAIL_ERROR, $iktUserData['C_id'] , array('iktissab_card_no' => $iktUserData['C_id'], 'message' => $message, 'session' => $iktUserData));
                    return $this->render('account/sendsms.html.twig',
                        array('message' => $message)
                    );
                }
        }
        catch (\Exception $e)
        {
            $message = $e->getMessage();
            return $this->render('account/sendsmssuccess.html.twig',
                array('userNewEmail' => $this->get('session')->get('new_value'), 'message' => $message)
            );
        }
    }

    private function checkEmail($email,$Country_id)
    {
        //  $em = $this->getDoctrine()->getManager("default2");
        try
        {
            $em = $this->getDoctrine()->getEntityManager();
            $conn = $em->getConnection();
            $queryBuilder = $conn->createQueryBuilder();
            $country_id   = strtolower($Country_id);
            $stm = $conn->prepare('SELECT * FROM   user  WHERE   email = ? AND country = ?  ');
            $stm->bindValue(1, $email);
            $stm->bindValue(2, $country_id);
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
        catch (Exception $e)
        {
           return  $data_email = array('success' => false , 'result' => $e->getMessage());
        }
    }


    private function updateEmail( $email , $Country_id, $C_id )
    {
        try
        {
            $em = $this->getDoctrine()->getEntityManager();
            $conn = $em->getConnection();
            $email = $email;
            $Country_id = $Country_id;
            $C_id = $C_id;

            $queryBuilder = $conn->createQueryBuilder();
            if ($Country_id == 'EG') {
                $tbl_suffix = "_EG";
            } else {
                $tbl_suffix = "";
            }
            // $data_values = array($email = $email, $C_id = $C_id);
            $data_values = array($email , $C_id );
            $stm = $conn->executeUpdate('UPDATE user SET email = ? WHERE ikt_card_no = ?   ', $data_values);
            return $stm;
        }
        catch (\Exception $e)
        {
            return 'INVALID_DATA';
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

        try
        {
            $activityLog = $this->get('app.activity_log');
            // check user
            // var_dump($this->get('session'));

            $country_id = $request->get('_country');
            $language   = $request->getLocale();
            // echo '===='.$this->get('session')->get('userSelectedCountry');
            $restClient = $this->get('app.rest_client');
            $iktUserData = $this->get('session')->get('iktUserData');
            // print_r($iktUserData);exit;
            $data = $request->request->all();

            if($country_id == 'eg') {
                $country_id = 'eg';
            }
            else{
                $country_id  = 'sa';
            }
            // var_dump($iktUserData);
            $posted = array();
            $iktCardNo      = $iktUserData['C_id'];
            $iktID_no       = $iktUserData['ID_no'];
            echo  '----> iktissab user '.$iktMobile      = $iktUserData['mobile'];

            $iktID_no = $iktUserData['ID_no'];
            //get current email of the user
            $currentEmail = $iktUserData['email']; // MobileType MobileType
            $form = $this->createForm(MobileType::class,  array() ,array(
                'extras'    => array(
                    'country'  => $country_id,
                    'iktID_no' => $iktID_no
            )));
            $url = $request->getLocale() . '/api/update_user_mobile.json';
            if(!empty($data))
            {
                /************************/
                $iqamaid_mobile = trim($data['mobile']['iqamaid_mobile']);
                echo '===> posted value '.$mobile = trim($data['mobile']['mobile']);
                $comment_mobile = trim($data['mobile']['comment_mobile']);
                //print_r($posted);
                /************************/
                if($country_id == 'sa')
                {
                    $extension = '0';
                    if($iktID_no != $iqamaid_mobile)
                    {
                        $message = $this->get('translator')->trans('Invalid Iqama Id / SSN or mobile number. Please enter correct Iqama Id/SSN');
                        return $this->render('account/mobile.html.twig',
                            array('form' => $form->createView(), 'country' => $country_id,   'message' => $message));
                    }
                    if($mobile == $iktMobile) {
                        $message = $this->get('translator')->trans('Your new mobile number and old mobile number must not be same');
                        return $this->render('account/mobile.html.twig', array('form' => $form->createView(), 'country' => $country_id,   'message' => $message));
                    }
                }
                if($country_id == 'eg')
                {
                    $extension = $data['mobile']['ext'];
                    if ($iktID_no != $iqamaid_mobile) {
                        $message = $this->get('translator')->trans('Invalid Iqama Id / SSN. Please enter correct Iqama Id/SSN');
                        return $this->render('account/mobile.html.twig', array('form' => $form->createView() ,'country' => $country_id,  'message' => $message));
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
                $data = $restClient->restPostForm(AppConstant::WEBAPI_URL . $url, $postData, array('Country-Id' => strtoupper($request->get('_country'))));
                // print_r($data);exit;
                if($data['success'] == true){
                    $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_MOBILE_SUCCESS, $iktUserData['C_id'] , array('iktissab_card_no' => $iktUserData['C_id'], 'message' => $data['message'], 'session' => $iktUserData));
                    $message = $this->get('translator')->trans($data['message']);
                    return $this->render('account/mobile.html.twig',
                        array('form' => $form->createView(), 'country' => $country_id, 'message' => $message)
                    );
                }
                if($data['success'] == false)
                {
                    if($data['status'] == 1)
                    {
                        $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_MOBILE_ERROR, $iktUserData['C_id'], array('iktissab_card_no' => $iktUserData['C_id'], 'message' => $data['message'], 'session' => $iktUserData));
                        $message = $this->get('translator')->trans($data['message']);
                        return $this->render('account/mobile.html.twig',
                            array('form' => $form->createView(), 'country' => $country_id, 'message' => $message)
                        );
                    }
                    else
                    {
                        // INVALID_DATA SECTION
                        $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_MOBILE_ERROR, $iktUserData['C_id'], array('iktissab_card_no' => $iktUserData['C_id'], 'message' => $data['message'], 'session' => $iktUserData));
                        $message = $this->get('translator')->trans($data['message']);
                        return $this->render('account/mobile.html.twig',
                            array('form' => $form->createView(), 'country' => $country_id, 'message' => $message)
                        );
                    }
                }
            }
            else
            {
                // this will load view for the first time when user click on the update mobile link
                //echo 'ads';
                $message = $this->get('translator')->trans('');
                return $this->render('account/mobile.html.twig',
                    array('form' => $form->createView(),'country' => $country_id,  'message' => $message)
                );
            }
        }
        catch (\Exception $e)
        {
            $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_MOBILE_ERROR, $iktUserData['C_id'] , array('iktissab_card_no' => $iktUserData['C_id'], 'message' => $e->getMessage(), 'session' => $iktUserData));
            $message = $this->get('translator')->trans('An invalid exception occurred22'.$e->getMessage());
            // echo $e->getMessage();
            return $this->render('account/mobile.html.twig',
                array('form' => $form->createView(),'country' => $country_id,'message' => $message)
            );
        }
    }




    /**
     * @Route("/{_country}/{_locale}/account/fullname" , name="front_account_fullname" )
     */
    public function fullNameAction(Request $request)
    {
        try
        {
            $activityLog = $this->get('app.activity_log');
            $country_id  = $request->get('_country');
            $this->getUser()->getIktCardNo();
            $restClient  = $this->get('app.rest_client');
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
                        $message = $this->get('translator')->trans('Full name must be of atleast two parts');
                        return $this->render('account/fullname.html.twig', array('form' => $form->createView(),   'message' => $message));
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
                // print_r($data);
                // $data['success'] = false;
                // $data['status']  = 0;
                if($data['success'] == true)
                {
                    $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_FULLNAME_SUCCESS, $iktUserData['C_id'] , array('iktissab_card_no' => $iktUserData['C_id'], 'message' => $data['message'], 'session' => $iktUserData));
                    return $this->render('account/fullname.html.twig', array('form' => $form->createView(),'message' => $this->get('translator')->trans($data['message'])));
                }
                if(!$data['success'])
                {
                    if($data['status'] == 1)
                    {
                        $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_FULLNAME_ERROR, $iktUserData['C_id'], array('iktissab_card_no' => $iktUserData['C_id'], 'message' => $data['message'], 'session' => $iktUserData));
                        return $this->render('account/fullname.html.twig', array('form' => $form->createView(), 'message' => $this->get('translator')->trans($data['message'])));
                    }
                    else
                    {
                        $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_FULLNAME_ERROR, $iktUserData['C_id'], array('iktissab_card_no' => $iktUserData['C_id'], 'message' => $data['message'], 'session' => $iktUserData));
                        return $this->render('account/fullname.html.twig', array('form' => $form->createView(), 'message' => $this->get('translator')->trans($data['message'])));
                    }
                }
            }
            else
            {
                $message = $this->get('translator')->trans('');
                return $this->render('account/fullname.html.twig',
                    array('form' => $form->createView(), 'message' => $message)
                );
            }
        }
        catch (\Exception $e)
        {
             $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_FULLNAME_ERROR, $iktUserData['C_id'] , array('iktissab_card_no' => $iktUserData['C_id'], 'message' => $e->getMessage(), 'session' => $iktUserData));
             $message = $e->getMessage();
             return $this->render('account/fullname.html.twig',
                array( 'form' => $form->createView() ,  'message' => $message));
        }
    }




    /**
     * @Route("/{_country}/{_locale}/account/iqamassn" , name="front_account_iqamassn")
     */

    public function iqamaSNNAction(Request $request)
    {
        try
        {
            $activityLog = $this->get('app.activity_log');
            $country_id  = $request->get('_country');
            $language    = $request->getLocale();
            $restClient  = $this->get('app.rest_client');
            $iktUserData = $this->get('session')->get('iktUserData');
            $iktCardNo     = $iktUserData['C_id'];
            $iktID_no           = $iktUserData['ID_no'];
            $url  = $request->getLocale() . '/api/update_user_ssn.json';
            $form = $this->createForm(IqamassnType::class, array() ,array(
                'extras' => array(
                    'country' => $country_id,
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
                        $message = $this->get('translator')->trans('New Iqama Id/SSN and old Iqama Id/SSN must not be same');
                        return $this->render('account/iqamassn.html.twig',
                            array('form' => $form->createView(),'message' => $message));
                    }

                    if ($iqamassn_new != $confirm_iqamassn_new) {
                        $message = $this->get('translator')->trans('New Iqama Id/SSN and confirm new Iqama Id/SSN must be same');
                        return $this->render('account/iqamassn.html.twig',
                            array('form' => $form->createView(),'message' => $message));
                    }

                    $validateIqama = $this->validateIqama($iqamassn_new);
                    if($validateIqama == false){
                        $message = $this->get('translator')->trans('Invalid Iqama/SSN Number');
                        return $this->render('account/iqamassn.html.twig',
                            array('form' => $form->createView(),'message' => $message));
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
                    if ($data['success']) {
                        // posted array is emty to clear the form after successful transction
                        $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_IQAMA_SUCCESS, $iktUserData['C_id'], array('iktissab_card_no' => $iktUserData['C_id'], 'message' => $data['message'], 'session' => $iktUserData));
                        $message = $this->get('translator')->trans($data['message']);
                        // return $this->render('account/' . $country_file_ext . '/iqamassn.html.twig',
                        return $this->render('account/iqamassn.html.twig',
                            array('form' => $form->createView(),'message' => $message)
                        );
                    }
                    if(!$data['success'])
                    {
                        if($data['status'] == 1)
                        {
                            $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_IQAMA_ERROR, $iktUserData['C_id'], array('iktissab_card_no' => $iktUserData['C_id'], 'message' => $data['message'], 'session' => $iktUserData));
                            $message = $this->get('translator')->trans($data['message']);
                            return $this->render('account/iqamassn.html.twig',
                                array('form' => $form->createView(), 'message' => $message)
                            );
                        }
                        else
                        {
                            $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_IQAMA_ERROR, $iktUserData['C_id'], array('iktissab_card_no' => $iktUserData['C_id'], 'message' => $data['message'], 'session' => $iktUserData));
                            $message = $this->get('translator')->trans($data['message']);
                            return $this->render('account/iqamassn.html.twig',
                                array('form' => $form->createView(),'message' => $message)
                            );
                        }
                    }
            }
            else
            {
                $message = $this->get('translator')->trans('');
                return $this->render('account/iqamassn.html.twig',
                    array('form' => $form->createView(),'message' => $message)
                );
            }
        }
        catch (\Exception $e)
        {
            $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_IQAMA_ERROR, $iktUserData['C_id'] , array('iktissab_card_no' => $iktUserData['C_id'], 'message' => $e->getMessage(), 'session' => $iktUserData));
            $message = $e->getMessage();
            return $this->render('account/iqamassn.html.twig',
                array('form' => $form->createView(), 'iktData' => $iktUserData,   'message' => $message)
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
        try {

            $tokenStorage = $this->get('security.token_storage');
            $form = $this->createForm(UpdatePasswordType::class, array() ,array() );
            $activityLog = $this->get('app.activity_log');

            $iktUserData = $this->get('session')->get('iktUserData');
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
                        return $this->render('account/updatepassword.html.twig',
                            array('form1' => $form->createView(), 'message' => $message));
                    }
                    if ($new_password == $old_password) {
                        $message = $this->get('translator')->trans('Your new and old password must not be the same');
                        return $this->render('account/updatepassword.html.twig',
                            array('form1' => $form->createView(), 'message' => $message));
                    }
                    // $new_password = $postData['form']['new_password'];
                    $userInfoLoggedIn->setPassword(md5($form->get('new_password')->getData()));
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($userInfoLoggedIn);
                    $em->flush();
                    $tokenStorage->getToken()->getUser()->setPassword(md5($form->get('new_password')->getData()));
                    $messageLog = $this->get('translator')->trans('Password updated successfully');
                    $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_PASSWORD_SUCCESS, $iktUserData['C_id'], array('iktissab_card_no' => $iktUserData['C_id'], 'message' => $messageLog, 'session' => $iktUserData));
                    $message = $this->get('translator')->trans('Password updated successfully');
                    return $this->render('account/updatepassword.html.twig',
                        array('form1' => $form->createView(), 'message' => $message));
                }
                else
                {
                    $message = $this->get('translator')->trans('Invalid token detected.Please submit again');
                    $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_PASSWORD_ERROR, $iktUserData['C_id'], array('iktissab_card_no' => $iktUserData['C_id'], 'message' => $message, 'session' => $iktUserData));
                    return $this->render('account/updatepassword.html.twig',
                        array('form1' => $form->createView(), 'message' => $message));
                }
            }
            else
            {
                $message = '';
                return $this->render('account/updatepassword.html.twig',
                    array('form1' => $form->createView(), 'message' => $message));
            }
        }
        catch (\Exception $e)
        {
            $e->getMessage();
            $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_PASSWORD_ERROR, $iktUserData['C_id'], array('iktissab_card_no' => $iktUserData['C_id'], 'message' => $e->getMessage(), 'session' => $iktUserData));
            return $this->render('account/updatepassword.html.twig',
                array('form1' => $form->createView(), 'message' => $e->getMessage()));
        }

    }


    /**
     * @Route("/{_country}/{_locale}/account/update" , name="account_update")
     * @param Request $request
     * @return Response
     */
    public function updateProfileAction(Request $request)
    {
        $user = $this->get('session')->get('iktUserData');
        $restClient = $this->get('app.rest_client');
        $url = $request->getLocale() . '/api/cities_areas_and_jobs.json';
        $cities_jobs_area = $restClient->restGet(AppConstant::WEBAPI_URL . $url, array('Country-Id' => strtoupper($request->get('_country'))));
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
            try {
                $activityLog = $this->get('app.activity_log');
                $postData = json_encode($form_data);
                $url = $request->getLocale() . '/api/update_user_detail.json';
                $data = $restClient->restPost(AppConstant::WEBAPI_URL . $url, $postData, array('Country-Id' => strtoupper($request->get('_country'))));
                $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_USERINFO_SUCCESS, $user['C_id'], $form_data);
                $message = $this->get('translator')->trans('Profile has been updated');
                return $this->render('/account/update.html.twig',
                    array(
                        'form' => $form->createView(),
                        'areas' => $areas,
                        'islamicyear' => $islamicYear,
                        'message' => $message,
                    )
                );
            } catch (Exception $e) {
                //die('in catch');
                $request->getSession()
                    ->getFlashBag()
                    ->add('error', 'Error While processing your request');
                $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_USERINFO_ERROR, $user['C_id'], $form_data);

            }
            return $this->redirectToRoute('account_update', array('_locale' => $request->getLocale(), '_country' => $request->get('_country')));
        }
        $message = $this->get('translator')->trans('');
        return $this->render('/account/update.html.twig',
            array(
                'form' => $form->createView(),
                'areas' => $areas,
                'islamicyear' => $islamicYear,
                'message' => $message,
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
        try
        {
            $activityLog = $this->get('app.activity_log');
            $restClient  = $this->get('app.rest_client');
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
            //print_r($data);exit;
            if(!empty($data))
            {
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
                    // print_r($data); // return data fromt the webservice
                    if ($data['success'] == true)
                    {
                        // posted array is emty to clear the form after successful transction
                        $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_MISSINGCARD_SUCCESS, $iktUserData['C_id'], array('iktissab_card_no' => $iktUserData['C_id'], 'message' => $data['message'], 'session' => $iktUserData));
                        $message = $this->get('translator')->trans($data['message']);
                        return $this->render('account/missingcard.html.twig',
                            array('form' => $form->createView(),  'message' => $message)
                        );
                    }
                    elseif ($data['success'] == false)
                    {
                        if($data['status'] == 1)
                        {
                            $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_MISSINGCARD_ERROR, $iktUserData['C_id'], array('iktissab_card_no' => $iktUserData['C_id'], 'message' => $data['message'], 'session' => $iktUserData));
                            $message = $this->get('translator')->trans($data['message']);
                            return $this->render('account/missingcard.html.twig',
                                array('form' => $form->createView(), 'message' => $message)
                            );
                        }
                        else
                        {
                            // Here INVALID_DATA  will come with status 0
                            $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_MISSINGCARD_ERROR, $iktUserData['C_id'], array('iktissab_card_no' => $iktUserData['C_id'], 'message' => $data['message'], 'session' => $iktUserData));
                            $message = $this->get('translator')->trans($data['message']);
                            return $this->render('account/missingcard.html.twig',
                                array('form' => $form->createView(),  'message' => $message)
                            );
                        }
                    }
                    else
                    {
                        $message = $this->get('translator')->trans('');
                        return $this->render('account/missingcard.html.twig',
                        array('form' => $form->createView(),  'message' => $message));
                    }
            }
            else
            {
                $message = $this->get('translator')->trans('');
                return $this->render('account/missingcard.html.twig',
                    array('form' => $form->createView(),  'message' => $message)
                );
            }
        }
        catch (\Exception $e)
        {

            $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_MISSINGCARD_ERROR, $iktUserData['C_id'] , array('iktissab_card_no' => $iktUserData['C_id'], 'message' => $e->getMessage(), 'session' => $iktUserData));
            $message = $e->getMessage();
            return $this->render('account/missingcard.html.twig',
                array('form' => $form->createView(), 'message' => $message)
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
        echo $this->createJson($data);
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
        catch (Exception $e){
            return new Response($e->getMessage());
        }
    }


    /**
     * @Route("/{_country}/{_locale}/account/transactions/{page}", name="ikt_transactions")
     */
    public function transactionsAction(Request $request, $page)
    {
        if ($request->query->get('draw')) {
            $page = $request->query->get('draw');
        }
        $restClient = $this->get('app.rest_client');
        //fetch trans count and save in session for future
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
        return new Response("Transaction resp");
    }



    /**
     * @Route("/{_country}/{_locale}/forgot/email", name="forgot_email")
     * Request $request
     */
    public function forgotEmailAction(Request $request)
    {
        $error = array('success' => true, 'message' =>'');
        $restClient = $this->get('app.rest_client');
        $smsService = $this->get('app.sms_service');
        $form = $this->createForm(ForgotEmailType::class, array(), array(
                'additional'  => array(
                    'locale'  => $request->getLocale(),
                    'country' => $request->get('_country'),
                )
            )
        );
        $form->handleRequest($request);
        $pData = $form->getData();
        if ($form->isSubmitted() && $form->isValid())
        {
            try {
                $accountEmail = $this->iktExist($pData['iktCardNo']);
                $url = $request->getLocale() . '/api/' . $pData['iktCardNo'] . '/userinfo.json';
                // echo AppConstant::WEBAPI_URL.$url;
                $data = $restClient->restGet(AppConstant::WEBAPI_URL . $url, array('Country-Id' => strtoupper($request->get('_country'))));
                // print_r($data);exit;

                if($data['success'] == true)
                {
                    // match the iqama numbers ( from form and other from the local data)
                    if ($pData['iqama'] != $data['user']['ID_no']) {
                        $form->get('iqama')->addError(new FormError($this->get('translator')->trans('Please enter correct Iqama number')));
                    }else{
                        $message = $this->get('translator')->trans("Your account registration email is %s", ["%s"=>$accountEmail]);
                        $acrivityLog = $this->get('app.activity_log');
                        //send sms code
                        $smsService->sendSms($data['user']['mobile'], $message, $request->get('_country'));
                        $acrivityLog->logEvent(AppConstant::ACTIVITY_FORGOT_EMAIL_SMS, 1, array('message' => $message, 'session' => $data['user']));
                        $error['success'] = true;
                        $error['message'] = $this->get('translator')->trans('You will recieve sms on your mobile number **** %s', [ "%s" => substr($data['user']['mobile'] , 8, 12)] );
                    }
                }
            } catch (Exception $e) {
                $error['success'] = false;
                $error['message'] = $e->getMessage();

            }
        }
        return $this->render('/account/forgot_email.twig',
            array(
                'form' => $form->createView(),
                'error' => $error
            )
        );
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





    function iktExist($ikt)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $checkIktCard = $em->getRepository('AppBundle:User')->find($ikt);
        if (is_null($checkIktCard)) {
            Throw new Exception($this->get('translator')->trans('Card is not registered on website'), 1);
        } else {
            return $checkIktCard->getEmail();
        }

    }
}