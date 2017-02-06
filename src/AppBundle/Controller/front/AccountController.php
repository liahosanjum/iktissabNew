<?php
/**
 * Created by PhpStorm.
 * User: abdulali
 * Date: 12/4/16
 * Time: 9:47 AM
 */

namespace AppBundle\Controller\Front;

use AppBundle\AppConstant;
use AppBundle\Entity\User;
use AppBundle\Security\User\IktissabUser;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use SoapClient;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use AppBundle\HijriGregorianConvert;





class AccountController extends Controller
{

    /**
     * @Route("/{_country}/{_locale}/account/home", name="account_home")
     */
    public function myAccountAction(Request $request)
    {
        $restClient = $this->get('app.rest_client');
        if(!$this->get('session')->get('iktUserData')) {
            $url = $request->getLocale() . '/api/' . $this->getUser()->getIktCardNo() . '/userinfo.json';
            // echo AppConstant::WEBAPI_URL.$url;
            $data = $restClient->restGet(AppConstant::WEBAPI_URL.$url, array('Country-Id' => strtoupper($request->get('_country'))));
            if($data['success'] == "true") {
                $this->get('session')->set('iktUserData', $data['user']);
            }
        }
        $iktUserData = $this->get('session')->get('iktUserData');
        var_dump($iktUserData);
        return $this->render('/account/home.twig',
            array('iktData' => $iktUserData)
        );
    }

    /**
     * @Route("/{_country}/{_locale}/account/email")
     */
    public function accountEmailAction(Request $request)
    {
        try
        {
            $restClient = $this->get('app.rest_client');
            $Country_id = strtoupper($request->get('_country'));
            if(!$this->get('session')->get('iktUserData'))
            {
                $url  = $request->getLocale() . '/api/' . $this->getUser()->getIktCardNo() . '/userinfo.json';
                $data = $restClient->restGet(AppConstant::WEBAPI_URL.$url, array('Country-Id' => strtoupper($request->get('_country'))));
                if($data['success'] == "true")
                {
                    $this->get('session')->set('iktUserData', $data['user']);
                }
            }
            $iktUserData      = $this->get('session')->get('iktUserData');
            $posted = array();
            // print_r($iktUserData);
            //current logged in user iktissab id
            $iktCardNo        = $iktUserData['C_id'];
            $iktID_no         = $iktUserData['ID_no'];
            //get current email of the user
            $currentEmail     = $iktUserData['email'];
            echo '=========>'.$mobile           = $iktUserData['mobile'];
            //form posted data
            $data = $request->request->all();
            //print_r($data);

            $url = $request->getLocale() . '/api/update_user_detail.json';
            if(!empty($data))
            {
                // here we will add validation to the form
                /************************/
                $newemail = $data['newemail'];
                $confirmnewemail = $data['confirmnewemail'];
                $posted['newemail'] = $newemail;
                $posted['confirmemail'] = $confirmnewemail;
                //print_r($posted);
                if($newemail == "")
                {
                    if (!filter_var($newemail, FILTER_VALIDATE_EMAIL))
                    {
                        $message = $this->get('translator')->trans('Please enter valid email address');
                        return $this->render('account/email.html.twig',
                            array('iktData' => $iktUserData, 'posted' => $posted , 'message' => $message)
                        );
                    }
                }
                /************************/
                if($confirmnewemail == "")
                {
                    if (!filter_var($confirmnewemail, FILTER_VALIDATE_EMAIL))
                    {
                        $message = $this->get('translator')->trans('Please enter valid email address');
                        return $this->render('account/email.html.twig',
                            array('iktData' => $iktUserData, 'posted' => $posted , 'message' => $message)
                        );
                    }
                }
                if($confirmnewemail != $newemail)
                {
                    $message = $this->get('translator')->trans('New Email and Confirm new email must be same');
                    return $this->render('account/email.html.twig',
                        array('iktData' => $iktUserData, 'posted' => $posted , 'message' => $message)
                    );
                }
                if($currentEmail == $newemail)
                {
                    $message = $this->get('translator')->trans('New email and current email must not be the same');
                    return $this->render('account/email.html.twig',
                        array('iktData' => $iktUserData, 'posted' => $posted , 'message' => $message)
                    );
                }
                /**************************/

                $form_data[0]   =     array(
                    'C_id'      =>  $iktCardNo,
                    'field'     =>  'email',
                    'new_value' =>  $data['newemail'],
                    'old_value' =>  $currentEmail,
                    'comments'  =>  'test comments test comments'
                );


                $this->get('session')->set('new_value', $data['newemail']);
                // here we will check if the email is already registered on website or not
                //
                $email_val = $this->checkEmail($data['newemail'],$Country_id);
                if ($email_val['email'])
                {
                    $message = $this->get('translator')->trans('The new email is already registered before , please enter a valid email');
                    return $this->render('account/email.html.twig',
                        array('iktData' => $iktUserData,'posted' => $posted , 'message' => $message)
                    );
                }
                //send sms
                $otp = rand(111111, 999999);
                $this->get('session')->set('smscode', $otp);
                $message = "Please enter the verification code you received on your mobile number ******".substr($mobile,6,10).$otp;

                echo $smsmessage = $this->get('translator')->trans("Verification code:" ).$otp.$this->get('translator')->trans("Changing account email");
                $smsService = $this->get('app.sms_service');


                echo 'testing';


                $numbers = "0583847092";

                $MsgID = rand(1, 99999);
                $msg = $message;  //$this->get('translator')->trans('test_message'); //"welcome to you in mobily.ws ,testing sms service";
                echo "====>".$smsResponse = $smsService->sendSms($numbers, $smsmessage, $request->get('_country'));
                //echo 'testing';
                //exit();

                if($smsResponse == 1){
                    // update the local website table for the new email
                    // next time the user will be logged in with the new email as user name
                    // $message  = "Please insert this temporary code you recieved on your mobile";
                    return $this->render('account/sendsms.html.twig',
                            array('iktData' => $iktUserData, 'posted' => $posted , 'message' => $message)
                    );
                }
                else
                {
                    $message = $this->get('translator')->trans('SMS not sent');
                    return $this->render('account/sendsms.html.twig',
                        array('iktData' => $iktUserData, 'posted' => $posted , 'message' => $message)
                    );
                }
            }
            else
            {
                $message = $this->get('translator')->trans('');
                return $this->render('account/email.html.twig',
                    array('iktData' => $iktUserData, 'posted' => $posted , 'message' => '')
                );
            }
        }
        catch (\Exception $e)
        {
            //$message = $this->get('translator')->trans('An invalid exception occurred');
            $message = $e->getMessage();
            return $this->render('account/email.html.twig',
                array('iktData' => $iktUserData, 'posted' => $posted , 'message' => '')
            );
        }

    }


    /**
     * @Route("/{_country}/{_locale}/account/smsverification")
     */
    public function accountSMSVerificationAction(Request $request)
    {
        try
        {
            $restClient = $this->get('app.rest_client');
            $Country_id = strtoupper($request->get('_country'));
            if (!$this->get('session')->get('iktUserData')) {
                $url = $request->getLocale() . '/api/' . $this->getUser()->getIktCardNo() . '/userinfo.json';
                $data = $restClient->restGet(AppConstant::WEBAPI_URL . $url, array('Country-Id' => strtoupper($request->get('_country'))));
                if ($data['success'] == "true") {
                    $this->get('session')->set('iktUserData', $data['user']);
                }
            }

            $iktUserData = $this->get('session')->get('iktUserData');
            //current logged in user iktissab id
            $iktCardNo = $iktUserData['C_id'];
            $iktID_no = $iktUserData['ID_no'];
            //get current email of the user
            $currentEmail = $iktUserData['email'];
            //form posted data
            $data = $request->request->all();
            //print_r($data);
            $url  = $request->getLocale() . '/api/update_user_detail.json';
            $code = $this->get('session')->get('smscode');
            $val  = $data['smsverify'];
            if ($val == $code) {
                //$url = $request->getLocale() . '/api/' . $iktCardNo . '/card_status.json';
                $form_data[0] = array(
                    'C_id' => $iktCardNo,
                    'field' => 'email',
                    'new_value' => $this->get('session')->get('new_value'),
                    'old_value' => $currentEmail,
                    'comments' => 'test comments test comments'
                );

                $postData = json_encode($form_data);
                $request->get('_country');
                $data = $restClient->restPost(AppConstant::WEBAPI_URL . $url, $postData, array('Country-Id' => strtoupper($request->get('_country'))));
                print_r($data);

                if ($data['success']) {
                    // $this->get('session')->set('iktUserData', $data['user']);
                    $email_val = $this->updateEmail($this->get('session')->get('new_value'), $Country_id, $iktCardNo);
                    if ($email_val == '1') {
                        if ($request->getLocale() == 'ar') {
                            $first = 'تم تحديث بريدكم الإلكتروني. نأمل استخدام البريد';
                            $last = 'عند الدخول إلى الموقع مرة أخرى';
                            $message = $last . '( ' . $this->get('session')->get("new_value") . ' )' . $first;
                        } else {
                            $message = $this->get('translator')->trans('Your email is updated. Next time, please use the ( ' . $this->get('session')->get("new_value") . ') address for signing to the website  ');
                        }
                        $tokenStorage = $this->get('security.token_storage');
                        $tokenStorage->getToken()->getUser()->setUsername($this->get('session')->get("new_value"));
                        return $this->render('account/sendsmssuccess.html.twig',
                            array('userNewEmail' => $this->get('session')->get('new_value'), 'message' => $message)
                        );
                    } elseif ($email_val == '0') {
                        $message = $this->get('translator')->trans('Your record is not updated');
                        return $this->render('account/sendsmssuccess.html.twig',
                            array('userNewEmail' => $this->get('session')->get('new_value'), 'message' => $message)
                        );
                    } else {
                        $message = $this->get('translator')->trans('An invalid exception occurred');
                        return $this->render('account/sendsmssuccess.html.twig',
                            array('userNewEmail' => $this->get('session')->get('new_value'), 'message' => $message)
                        );
                    }
                    $message = $this->get('translator')->trans($data['success']);
                    return $this->render('account/sendsms.html.twig',
                        array('iktData' => $iktUserData, 'message' => $message)
                    );
                } elseif ($data['success']) {
                    // $this->get('session')->set('iktUserData', $data['user']);
                    $message = $this->get('translator')->trans('');
                    return $this->render('account/sendsms.html.twig',
                        array('iktData' => $iktUserData, 'message' => $message)
                    );
                } elseif ($data['success'] == "INVALID_DATA") {
                    // $this->get('session')->set('iktUserData', $data['user']);
                    $message = $data['message'];
                    return $this->render('account/sendsms.html.twig',
                        array('iktData' => $iktUserData, 'message' => $message)
                    );
                } else {
                    $message = $this->get('translator')->trans('An invalid exception occurred');
                    return $this->render('account/sendsms.html.twig',
                        array('message' => $message)
                    );
                }
            } else {
                $message = $this->get('translator')->trans('Please enter correct verification code');
                return $this->render('account/sendsms.html.twig',
                    array('iktData' => $iktUserData, 'message' => $message)
                );
            }
        }
        catch (\Exception $e)
        {
            //$message = $this->get('translator')->trans('An invalid exception occurred');
            $message = $e->getMessage();
            return $this->render('account/sendsms.html.twig',
                array('message' => $message)
            );
        }


    }

    /**
     * @Route("/{_country}/{_locale}/account/smsverificationbk")
     */
    public function accountSMSVerificationbkAction(Request $request)
    {
        try {
            $restClient = $this->get('app.rest_client');
            $Country_id = strtoupper($request->get('_country'));
            if (!$this->get('session')->get('iktUserData')) {
                $url = $request->getLocale() . '/api/' . $this->getUser()->getIktCardNo() . '/userinfo.json';
                $data = $restClient->restGet(AppConstant::WEBAPI_URL . $url, array('Country-Id' => strtoupper($request->get('_country'))));
                if ($data['success'] == "true") {
                    $this->get('session')->set('iktUserData', $data['user']);
                }
            }
            $iktUserData = $this->get('session')->get('iktUserData');
            //current logged in user iktissab id
            $iktCardNo = $iktUserData['C_id'];
            $iktID_no = $iktUserData['ID_no'];
            //get current email of the user
            $currentEmail = $iktUserData['email'];
            //form posted data
            $data = $request->request->all();
            //print_r($data);

            $url = $request->getLocale() . '/api/update_user_detail.json';
            if (!empty($data))
            {
                //$url = $request->getLocale() . '/api/' . $iktCardNo . '/card_status.json';
                $form_data[0] = array(
                    'C_id' => $iktCardNo,
                    'field' => 'email',
                    'new_value' => $data['newemail'],
                    'old_value' => $currentEmail,
                    'comments' => 'test comments test comments'
                );
                // before calling the rest we need to check if there is any email already registered with the new email value
                $email_val = $this->checkEmail($data['newemail'], $Country_id);
                if ($email_val['email']) {
                    $message = $this->get('translator')->trans('The new email is already registered before , please enter a valid email111');
                    return $this->render('account/sendsms.html.twig',
                        array('iktData' => $iktUserData, 'message' => $message)
                    );
                } else {


                    $postData = json_encode($form_data);
                    $request->get('_country');
                    $data = $restClient->restPost(AppConstant::WEBAPI_URL . $url, $postData, array('Country-Id' => strtoupper($request->get('_country'))));
                    if ($data['success']) {
                        // $this->get('session')->set('iktUserData', $data['user']);
                        $message = $this->get('translator')->trans('');
                        return $this->render('account/sendsms.html.twig',
                            array('iktData' => $iktUserData, 'message' => $message)
                        );
                    } elseif (!$data['success']) {
                        // $this->get('session')->set('iktUserData', $data['user']);
                        $message = $this->get('translator')->trans('');
                        return $this->render('account/sendsms.html.twig',
                            array('iktData' => $iktUserData, 'message' => $message)
                        );
                    } elseif ($data['success'] == "INVALID_DATA") {
                        // $this->get('session')->set('iktUserData', $data['user']);
                        $message = $data['message'];
                        return $this->render('account/sendsms.html.twig',
                            array('iktData' => $iktUserData, 'message' => $message)
                        );
                    } else {
                        $message = $this->get('translator')->trans('An invalid exception occurred');
                        return $this->render('account/sendsms.html.twig',
                            array('message' => $message)
                        );
                    }
                }
            }
            else
            {
                $message = $this->get('translator')->trans('Invalid user');
                return $this->render('account/sendsms.html.twig',
                    array('iktData' => $iktUserData, 'message' => $message)
                );
            }
        }
        catch (\Exception $e){

            //$message = $this->get('translator')->trans('An invalid exception occurred');
            $message = $e->getMessage();
            return $this->render('account/sendsms.html.twig',
                array('message' => $message)
            );
        }


    }

    private function checkEmail($email,$Country_id){
        //$em = $this->getDoctrine()->getManager("default2");
            $em   = $this->getDoctrine()->getEntityManager();
            $conn = $em->getConnection();
            $queryBuilder = $conn->createQueryBuilder();
            if($Country_id == 'SA'){$tbl_suffix = "";}
            else{$tbl_suffix = "_EG";}
            // right now all the data comes to user table only
            $tbl_suffix = '';
            $stm  = $conn->prepare('
            SELECT * FROM   user'.$tbl_suffix.'  WHERE   
              email = ?
            ');

            $stm->bindValue(1, $email);

            $stm->execute();
            $result = $stm->fetch();
            return $result;
    }


    private function updateEmail( $email , $Country_id, $C_id )
    {
        try
        {
            $em = $this->getDoctrine()->getEntityManager();
            $conn = $em->getConnection();
            echo $email = $email;
            echo $Country_id = $Country_id;
            echo $C_id = $C_id;

            $queryBuilder = $conn->createQueryBuilder();
            if ($Country_id == 'EG') {
                $tbl_suffix = "_EG";
            } else {
                $tbl_suffix = "";
            }
            $data_values = array($email = $email, $C_id = $C_id);
            $stm = $conn->executeUpdate('UPDATE user SET  
                                                    email    = ?
                                                    WHERE ikt_card_no = ?   ', $data_values);
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
     * @Route("/{_country}/{_locale}/account/mobile")
     */
    public function userMobileAction(Request $request)
    {

        try
        {
            $activityLog = $this->get('app.activity_log');
            //check user
            //var_dump($this->get('session'));
            $country_id = $request->get('_country');
            $language   = $request->getLocale();
            //echo '===='.$this->get('session')->get('userSelectedCountry');
            $restClient = $this->get('app.rest_client');
            if(!$this->get('session')->get('iktUserData')) {
                $url = $request->getLocale() . '/api/' . $this->getUser()->getIktCardNo() . '/userinfo.json';
                // echo AppConstant::WEBAPI_URL.$url;
                $data = $restClient->restGet(AppConstant::WEBAPI_URL.$url, array('Country-Id' => strtoupper($request->get('_country'))));
                if($data['success'] == "true") {
                    $this->get('session')->set('iktUserData', $data['user']);
                }
            }
            $iktUserData = $this->get('session')->get('iktUserData');
            if($country_id == 'eg')
            {
                $country_file_ext = 'eg';
                $country_id = 'EG';
            }
            else
            {
                $country_file_ext = 'sa';
                $country_id = 'SA';
            }
            //var_dump($iktUserData);
            $posted = array();
            echo $iktCardNo = $iktUserData['C_id'];
            echo '--'.$iktID_no = $iktUserData['ID_no'];
            echo '--'.$iktMobile = $iktUserData['mobile'];

            $iktID_no = $iktUserData['ID_no'];
            //get current email of the user
            $currentEmail = $iktUserData['email'];

            //form posted data
            $data = $request->request->all();

            $posted = array();

            $url = $request->getLocale() . '/api/update_user_mobile.json';






            if(!empty($data))
            {
                // here we will add validation to the form
                /************************/
                $iqamaid_mobile = trim($data['iqamaid_mobile']);
                $mobile = trim($data['mobile']);
                $comment_mobile = trim($data['comment_mobile']);
                $posted['iqamaid_mobile'] = $iqamaid_mobile;
                $posted['mobile'] = $mobile;
                $posted['comment_mobile'] = $comment_mobile;

                //print_r($posted);

                /************************/
                if($country_id == 'SA') {
                    if (!preg_match('/^[0-9]{10}$/', $iqamaid_mobile)) {

                        $message = $this->get('translator')->trans('Please enter valid 10 digits Iqama Id/SSN');
                        return $this->render('account/'.$country_file_ext.'/mobile.html.twig',
                            array('iktData' => $iktUserData, 'posted' => $posted, 'message' => $message)
                        );
                    }
                    if (!preg_match('/^[0-9]{9}$/', $mobile)) {

                        $message = $this->get('translator')->trans('Please enter valid 9 digits mobile number');
                        return $this->render('account/'.$country_file_ext.'/mobile.html.twig',
                            array('iktData' => $iktUserData, 'posted' => $posted, 'message' => $message)
                        );
                    }
                    if ($comment_mobile == "") {

                        $message = $this->get('translator')->trans('Please enter comments');
                        return $this->render('account/'.$country_file_ext.'/mobile.html.twig',
                            array('iktData' => $iktUserData, 'posted' => $posted, 'message' => $message)
                        );
                    }

                    if ($iktID_no != $iqamaid_mobile)
                    {
                        $message = $this->get('translator')->trans('Invalid Iqama Id / SSN or mobile number. Please enter correct Iqama Id/SSN');
                        return $this->render('account/'.$country_file_ext.'/mobile.html.twig',
                            array('iktData' => $iktUserData, 'posted' => $posted, 'message' => $message)
                        );
                    }
                }

                if($country_id == 'EG')
                {
                    if (!preg_match('/^[0-9]{14}$/', $iqamaid_mobile)) {
                        $message = $this->get('translator')->trans('Please enter valid 14 digits Iqama Id/SSN');
                        return $this->render('account/'.$country_file_ext.'/mobile.html.twig',
                            array('iktData' => $iktUserData, 'posted' => $posted, 'message' => $message)
                        );
                    }



                    if (!preg_match('/^[0-9]{10}$/', $mobile))
                    {
                        $message = $this->get('translator')->trans('Please enter valid 10 digits mobile number for egypt');
                        return $this->render('account/'.$country_file_ext.'/mobile.html.twig',
                            array('iktData' => $iktUserData, 'posted' => $posted, 'message' => $message)
                        );
                    }

                    if ($comment_mobile == "") {

                        $message = $this->get('translator')->trans('Please enter comments');
                        return $this->render('account/'.$country_file_ext.'/mobile.html.twig',
                            array('iktData' => $iktUserData, 'posted' => $posted, 'message' => $message)
                        );
                    }
                    if ($iktID_no != $iqamaid_mobile)
                    {
                        $message = $this->get('translator')->trans('Invalid Iqama Id / SSN. Please enter correct Iqama Id/SSN');
                        return $this->render('account/'.$country_file_ext.'/mobile.html.twig',
                            array('iktData' => $iktUserData, 'posted' => $posted, 'message' => $message)
                        );
                    }
                }
                /**************************/
                // set mobile number to 10 digits for webservice which needs 10 digits but when used 966 on website form
                // then we are forcing user to enter 9 digits without 0 for KSA.

                // mobile format for webservice
                $mobile_format_webservice = $this->getMobileFormat($request , $data['mobile']);

                $form_data      =   array(
                    'C_id'      =>  $iktCardNo,
                    'field'     =>  'mobile',
                    'old_value' =>  $iktMobile,
                    'new_value' =>  $mobile_format_webservice,
                    'comments'  =>  $data['comment_mobile']
                );

                $postData = json_encode($form_data);
                $request->get('_country');
                $data = $restClient->restPost(AppConstant::WEBAPI_URL . $url, $postData, array('Country-Id' => strtoupper($request->get('_country'))));
                if($data['success']){
                    // posted array is emty to clear the form after successful transction
                    $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_MOBILE_SUCCESS, $iktUserData['C_id'] , array('iktissab_card_no' => $iktUserData['C_id'], 'message' => $data['message'], 'session' => $iktUserData));
                    $posted="";
                    $message = $this->get('translator')->trans($data['message']);
                    return $this->render('account/'.$country_file_ext.'/mobile.html.twig',
                        array('iktData' => $iktUserData, 'posted' => $posted, 'message' => $message)
                    );
                }
                if(!$data['success']){
                    $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_MOBILE_ERROR, $iktUserData['C_id'] , array('iktissab_card_no' => $iktUserData['C_id'], 'message' => $data['message'], 'session' => $iktUserData));
                    $message = $this->get('translator')->trans($data['message']);
                    return $this->render('account/'.$country_file_ext.'/mobile.html.twig',
                        array('iktData' => $iktUserData, 'posted' => $posted, 'message' => $message)
                    );
                }
                if(!$data['success'] == 'INVALID_DATA'){
                    $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_MOBILE_ERROR, $iktUserData['C_id'] , array('iktissab_card_no' => $iktUserData['C_id'], 'message' => $data['message'], 'session' => $iktUserData));

                    $message = $this->get('translator')->trans($data['message']);
                    return $this->render('account/'.$country_file_ext.'/mobile.html.twig',
                        array('iktData' => $iktUserData, 'posted' => $posted, 'message' => $message)
                    );
                }
            }
            else
            {
                // this will load view for the first time when user click on the update mobile link
                //
                $message = $this->get('translator')->trans('');
                // empty the posted data
                $posted = "";
                echo 'account/'.$country_file_ext.'/mobile.html.twig';
                return $this->render('account/'.$country_file_ext.'/mobile.html.twig',
                    array('iktData' => $iktUserData, 'posted' => $posted, 'message' => $message)
                );
            }
        }
        catch (\Exception $e)
        {
            $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_MOBILE_ERROR, $iktUserData['C_id'] , array('iktissab_card_no' => $iktUserData['C_id'], 'message' => $e->getMessage(), 'session' => $iktUserData));
            //$error['success'] = false;
            //$error['message'] = $e->getMessage();
            // $e->getMessage();
            $data = $request->request->all();
            if(!empty($data)) {
                $iqamaid_mobile = trim($data['iqamaid_mobile']);
                $mobile = trim($data['mobile']);
                $comment_mobile = trim($data['comment_mobile']);

                $posted['iqamaid_mobile'] = $iqamaid_mobile;
                $posted['mobile'] = $mobile;
                $posted['comment_mobile'] = $comment_mobile;
            }
            $message = $this->get('translator')->trans('An invalid exception occurred here');
            echo 'account/'.$country_file_ext.'/mobile.html.twig';
            return $this->render('account/'.$country_file_ext.'/mobile.html.twig',
                array('iktData' => $iktUserData, 'posted' => $posted, 'message' => $message)
            );
        }
    }


    private function getMobileFormat(Request $request, $mobile_number)
    {
        if($request->get('_country') == 'sa')
        {
            return  $mobile_number = "0".$mobile_number ;
        }
        if($request->get('_country') == 'eg')
        {
            return  $mobile_number = "0020".$mobile_number ;
        }
    }

    /**
     * @Route("/{_country}/{_locale}/account/fullname")
     */
    public function fullNameAction(Request $request)
    {
        try
        {
            $activityLog = $this->get('app.activity_log');
            //check user
            //var_dump($this->get('session'));
            $country_id  = $request->get('_country');
            $language    = $request->getLocale();
            //echo '===='.$this->get('session')->get('userSelectedCountry');
            echo $this->getUser()->getIktCardNo();
            $restClient  = $this->get('app.rest_client');
            if($this->get('session')->get('iktUserData')) {

                    $url = $request->getLocale() . '/api/' . $this->getUser()->getIktCardNo() . '/userinfo.json';
                    // echo AppConstant::WEBAPI_URL.$url;
                    $data = $restClient->restGet(AppConstant::WEBAPI_URL . $url, array('Country-Id' => strtoupper($request->get('_country'))));
                    if ($data['success'] == "true") {
                        $this->get('session')->set('iktUserData', $data['user']);
                    }


            }
            $iktUserData = $this->get('session')->get('iktUserData');
            if($country_id == 'eg')
            {
                $country_file_ext = 'eg';
                $country_id = 'EG';
            }
            else
            {
                $country_file_ext = 'sa';
                $country_id = 'SA';
            }
            //var_dump($iktUserData);
            $posted = array();
            $iktCardNo = $iktUserData['C_id'];
            echo '--'.$iktID_no = $iktUserData['ID_no'];

            $iktID_no = $iktUserData['ID_no'];
            $ikt_cname = $iktUserData['cname'];

            //get current email of the user
            //form posted data

            $data = $request->request->all();
            $posted = array();

            if(!empty($data))
            {
                // here we will add validation to the form
                /************************/
                $full_name = trim($data['full_name']);
                $comment_fullname = trim($data['comment_fullname']);
                $posted['fullname_registered_iqamaid'] = $iktID_no;
                $posted['full_name'] = $full_name;
                $posted['comment_fullname'] = $comment_fullname;
                // print_r($posted);

                /************************/
                if($country_id == 'SA') {
                    if ($full_name == "")
                    {
                        $message = $this->get('translator')->trans('Please enter your full name');
                        return $this->render('account/'.$country_file_ext.'/fullname.html.twig',
                            array('iktData' => $iktUserData, 'posted' => $posted, 'message' => $message)
                        );
                    }
                    if ($comment_fullname == "")
                    {
                        $message = $this->get('translator')->trans('Please enter comments');
                        return $this->render('account/'.$country_file_ext.'/fullname.html.twig',
                            array('iktData' => $iktUserData, 'posted' => $posted, 'message' => $message)
                        );
                    }
                }
                if($country_id == 'EG')
                {
                    if ($full_name == "")
                    {
                        $message = $this->get('translator')->trans('Please enter your full name');
                        return $this->render('account/'.$country_file_ext.'/fullname.html.twig',
                            array('iktData' => $iktUserData, 'posted' => $posted, 'message' => $message)
                        );
                    }

                    if ($comment_fullname == "")
                    {
                        $message = $this->get('translator')->trans('Please enter comments');
                        return $this->render('account/'.$country_file_ext.'/fullname.html.twig',
                            array('iktData' => $iktUserData, 'posted' => $posted, 'message' => $message)
                        );
                    }
                }
                /**************************/
                $url = $request->getLocale() . '/api/update_user_detail.json';
                $form_data[0]      =   array(
                    'C_id'      =>  $iktCardNo,
                    'field'     =>  'cname',
                    'old_value' =>  $ikt_cname,
                    'new_value' =>  $data['full_name'],
                    'comments'  =>  $data['comment_fullname']
                );

                $postData = json_encode($form_data);
                $request->get('_country');
                $data = $restClient->restPost(AppConstant::WEBAPI_URL . $url, $postData, array('Country-Id' => strtoupper($request->get('_country'))));
                print_r($data);
                if($data['success']){
                    // posted array is emty to clear the form after successful transction
                    $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_FULLNAME_SUCCESS, $iktUserData['C_id'] , array('iktissab_card_no' => $iktUserData['C_id'], 'message' => $data['message'], 'session' => $iktUserData));
                    $posted  = "";
                    $message = $this->get('translator')->trans($data['message']);
                    return $this->render('account/'.$country_file_ext.'/fullname.html.twig',
                        array('iktData' => $iktUserData, 'posted' => $posted, 'message' => $message)
                    );
                }
                if(!$data['success']){
                    $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_FULLNAME_ERROR, $iktUserData['C_id'] , array('iktissab_card_no' => $iktUserData['C_id'], 'message' => $data['message'], 'session' => $iktUserData));
                    $message = $this->get('translator')->trans($data['message']);
                    return $this->render('account/'.$country_file_ext.'/fullname.html.twig',
                        array('iktData' => $iktUserData, 'posted' => $posted, 'message' => $message)
                    );
                }
                if(!$data['success'] == 'INVALID_DATA'){
                    $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_FULLNAME_ERROR, $iktUserData['C_id'] , array('iktissab_card_no' => $iktUserData['C_id'], 'message' => $data['message'], 'session' => $iktUserData));

                    $message = $this->get('translator')->trans($data['message']);
                    return $this->render('account/'.$country_file_ext.'/fullname.html.twig',
                        array('iktData' => $iktUserData, 'posted' => $posted, 'message' => $message)
                    );
                }
            }
            else
            {
                // this will load view for the first time when user click on the update mobile link
                $message = $this->get('translator')->trans('');
                // empty the posted data
                $posted = "";
                echo 'account/'.$country_file_ext.'/fullname.html.twig';
                return $this->render('account/'.$country_file_ext.'/fullname.html.twig',
                    array('iktData' => $iktUserData, 'posted' => $posted, 'message' => $message)
                );
            }
        }
        catch (\Exception $e)
        {
            $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_FULLNAME_ERROR, $iktUserData['C_id'] , array('iktissab_card_no' => $iktUserData['C_id'], 'message' => $e->getMessage(), 'session' => $iktUserData));
            $data = $request->request->all();
            if(!empty($data))
            {
                // here we will add validation to the form
                /************************/
                $full_name = trim($data['full_name']);
                $comment_fullname = trim($data['comment_fullname']);
                $posted['fullname_registered_iqamaid'] = $iktID_no;
                $posted['full_name'] = $full_name;
                $posted['comment_fullname'] = $comment_fullname;
            }
            $message = $e->getMessage();
            //$this->get('translator')->trans('An invalid exception occurred');
            echo 'account/'.$country_file_ext.'/fullname.html.twig';
            return $this->render('account/'.$country_file_ext.'/fullname.html.twig',
                array('iktData' => $iktUserData, 'posted' => $posted, 'message' => $message)
            );
        }

    }




    /**
     * @Route("/{_country}/{_locale}/account/iqamassn")
     */

    public function iqamaSNNAction(Request $request)
    {
        try
        {
            $activityLog = $this->get('app.activity_log');
            //check user
            //var_dump($this->get('session'));
            $country_id = $request->get('_country');
            $language = $request->getLocale();
            //echo '===='.$this->get('session')->get('userSelectedCountry');
            $restClient = $this->get('app.rest_client');
            if(!$this->get('session')->get('iktUserData')) {
                $url = $request->getLocale() . '/api/' . $this->getUser()->getIktCardNo() . '/userinfo.json';
                // echo AppConstant::WEBAPI_URL.$url;
                $data = $restClient->restGet(AppConstant::WEBAPI_URL.$url, array('Country-Id' => strtoupper($request->get('_country'))));
                if($data['success'] == "true") {
                    $this->get('session')->set('iktUserData', $data['user']);
                }
            }
            $iktUserData = $this->get('session')->get('iktUserData');
            var_dump($iktUserData);
            if($country_id == 'eg')
            {
                $country_file_ext = 'eg';
                $country_id = 'EG';
            }
            else
            {
                $country_file_ext = 'sa';
                $country_id = 'SA';
            }
            //var_dump($iktUserData);
            $posted = array();
            echo $iktCardNo = $iktUserData['C_id'];
            echo '--'.$iktID_no = $iktUserData['ID_no'];

            $iktID_no = $iktUserData['ID_no'];

            //get current email of the user
            $currentEmail = $iktUserData['email'];
            //form posted data

            $data = $request->request->all();

            $posted = array();
            $url = $request->getLocale() . '/api/update_user_ssn.json';
            $csrf_token_iqama = trim($data['_csrf_token_iqama']);


            if(!empty($data)) {

                if ($this->isCsrfTokenValid('auth_iqamassn', $csrf_token_iqama))
                {
                    // here we will add validation to the form
                    /************************/
                    echo $iqamassn_registered = trim($data['iqamassn_registered']);
                    echo '<br>';
                    echo $iqamassn_new = trim($data['iqamassn_new']);
                    $confirm_iqamassn_new = trim($data['confirm_iqamassn_new']);
                    $comment_iqamassn = trim($data['comment_iqamassn']);

                    $posted['iqamassn_registered'] = $iktID_no;
                    $posted['iqamassn_new'] = $iqamassn_new;
                    $posted['confirm_iqamassn_new'] = $confirm_iqamassn_new;
                    $posted['comment_iqamassn'] = $comment_iqamassn;

                    //print_r($posted);

                    /************************/
                    if ($country_id == 'SA') {
                        if (!preg_match('/^[0-9]{10}$/', $iqamassn_new)) {
                            $message = $this->get('translator')->trans('Please enter valid 10 digits Iqama Id/SSN');
                            return $this->render('account/' . $country_file_ext . '/iqamassn.html.twig',
                                array('iktData' => $iktUserData, 'posted' => $posted, 'message' => $message)
                            );
                        }
                        if (!preg_match('/^[0-9]{10}$/', $confirm_iqamassn_new)) {

                            $message = $this->get('translator')->trans('Please enter valid 10 digits Iqama Id/SSN');
                            return $this->render('account/' . $country_file_ext . '/iqamassn.html.twig',
                                array('iktData' => $iktUserData, 'posted' => $posted, 'message' => $message)
                            );
                        }
                        if ($iqamassn_new == $iqamassn_registered) {
                            $message = $this->get('translator')->trans('New Iqama Id/SSN and old Iqama Id/SSN must not be same');
                            return $this->render('account/' . $country_file_ext . '/iqamassn.html.twig',
                                array('iktData' => $iktUserData, 'posted' => $posted, 'message' => $message)
                            );
                        }

                        if ($iqamassn_new != $confirm_iqamassn_new) {
                            $message = $this->get('translator')->trans('New Iqama Id/SSN and confirm new Iqama Id/SSN must be same');
                            return $this->render('account/' . $country_file_ext . '/iqamassn.html.twig',
                                array('iktData' => $iktUserData, 'posted' => $posted, 'message' => $message)
                            );
                        }
                        if ($comment_iqamassn == "") {
                            $message = $this->get('translator')->trans('Please enter comments');
                            return $this->render('account/' . $country_file_ext . '/iqamassn.html.twig',
                                array('iktData' => $iktUserData, 'posted' => $posted, 'message' => $message)
                            );
                        }
                    }

                    if ($country_id == 'EG') {
                        if (!preg_match('/^[0-9]{14}$/', $iqamassn_new)) {
                            $message = $this->get('translator')->trans('Please enter valid 14 digits Iqama Id/SSN');
                            return $this->render('account/' . $country_file_ext . '/iqamassn.html.twig',
                                array('iktData' => $iktUserData, 'posted' => $posted, 'message' => $message)
                            );
                        }

                        if (!preg_match('/^[0-9]{14}$/', $confirm_iqamassn_new)) {
                            $message = $this->get('translator')->trans('Please enter valid 14 digits Iqama Id/SSN');
                            return $this->render('account/' . $country_file_ext . '/iqamassn.html.twig',
                                array('iktData' => $iktUserData, 'posted' => $posted, 'message' => $message)
                            );
                        }

                        if ($iqamassn_new == $iqamassn_registered) {
                            $message = $this->get('translator')->trans('New Iqama Id/SSN and old Iqama Id/SSN must not be same');
                            return $this->render('account/' . $country_file_ext . '/iqamassn.html.twig',
                                array('iktData' => $iktUserData, 'posted' => $posted, 'message' => $message)
                            );
                        }


                        if ($iqamassn_new != $confirm_iqamassn_new) {
                            $message = $this->get('translator')->trans('New Iqama Id/SSN and confirm new Iqama Id/SSN must be same');
                            return $this->render('account/' . $country_file_ext . '/iqamassn.html.twig',
                                array('iktData' => $iktUserData, 'posted' => $posted, 'message' => $message)
                            );
                        }

                        if ($comment_iqamassn == "") {
                            $message = $this->get('translator')->trans('Please enter comments');
                            return $this->render('account/' . $country_file_ext . '/iqamassn.html.twig',
                                array('iktData' => $iktUserData, 'posted' => $posted, 'message' => $message)
                            );
                        }
                    }
                    /**************************/
                    // set mobile number to 10 digits for webservice which needs 10 digits but when used 966 on website form
                    // then we are forcing user to enter 9 digits without 0 for KSA.

                    // mobile format for webservice

                    $form_data = array(
                        'C_id' => $iktCardNo,
                        'field' => 'iqamassn',
                        'old_value' => $iktID_no,
                        'new_value' => $data['iqamassn_new'],
                        'comments' => $data['comment_iqamassn']
                    );

                    $postData = json_encode($form_data);
                    $request->get('_country');
                    $data = $restClient->restPost(AppConstant::WEBAPI_URL . $url, $postData, array('Country-Id' => strtoupper($request->get('_country'))));
                    if ($data['success']) {
                        // posted array is emty to clear the form after successful transction
                        $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_IQAMA_SUCCESS, $iktUserData['C_id'], array('iktissab_card_no' => $iktUserData['C_id'], 'message' => $data['message'], 'session' => $iktUserData));
                        $posted = "";
                        $message = $this->get('translator')->trans($data['message']);
                        return $this->render('account/' . $country_file_ext . '/iqamassn.html.twig',
                            array('iktData' => $iktUserData, 'posted' => $posted, 'message' => $message)
                        );
                    }
                    if (!$data['success']) {
                        $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_IQAMA_ERROR, $iktUserData['C_id'], array('iktissab_card_no' => $iktUserData['C_id'], 'message' => $data['message'], 'session' => $iktUserData));
                        $message = $this->get('translator')->trans($data['message']);
                        return $this->render('account/' . $country_file_ext . '/iqamassn.html.twig',
                            array('iktData' => $iktUserData, 'posted' => $posted, 'message' => $message)
                        );
                    }
                    if (!$data['success'] == 'INVALID_DATA') {
                        $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_IQAMA_ERROR, $iktUserData['C_id'], array('iktissab_card_no' => $iktUserData['C_id'], 'message' => $data['message'], 'session' => $iktUserData));

                        $message = $this->get('translator')->trans($data['message']);
                        return $this->render('account/' . $country_file_ext . '/iqamassn.html.twig',
                            array('iktData' => $iktUserData, 'posted' => $posted, 'message' => $message)
                        );
                    }
                }
                else
                {
                    // this will load view for the first time when user click on the update mobile link
                    //
                    $message = $this->get('translator')->trans('Invalid token detected.Please submit again');
                    // empty the posted data
                    $posted = "";
                    echo 'account/'.$country_file_ext.'/iqamassn.html.twig';
                    return $this->render('account/'.$country_file_ext.'/iqamassn.html.twig',
                        array('iktData' => $iktUserData, 'posted' => $posted, 'message' => $message)
                    );

                }
            }
            else
            {
                // this will load view for the first time when user click on the update mobile link
                //
                $message = $this->get('translator')->trans('');
                // empty the posted data
                $posted = "";
                echo 'account/'.$country_file_ext.'/iqamassn.html.twig';
                return $this->render('account/'.$country_file_ext.'/iqamassn.html.twig',
                    array('iktData' => $iktUserData, 'posted' => $posted, 'message' => $message)
                );
            }
        }
        catch (\Exception $e)
        {

                $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_IQAMA_ERROR, $iktUserData['C_id'] , array('iktissab_card_no' => $iktUserData['C_id'], 'message' => $e->getMessage(), 'session' => $iktUserData));
                // $error['success'] = false;
                // $error['message'] = $e->getMessage();
                echo $e->getMessage();
                $data = $request->request->all();
                if(!empty($data))
                {
                    // here we will add validation to the form
                    /************************/
                    echo $iqamassn_registered = trim($data['iqamassn_registered']);
                    echo '<br>';
                    echo $iqamassn_new = trim($data['iqamassn_new']);
                    $confirm_iqamassn_new = trim($data['confirm_iqamassn_new']);
                    $comment_iqamassn = trim($data['comment_iqamassn']);
                    $posted['iqamassn_registered'] = $iktID_no;
                    $posted['iqamassn_new'] = $iqamassn_new;
                    $posted['confirm_iqamassn_new'] = $confirm_iqamassn_new;
                    $posted['comment_iqamassn'] = $comment_iqamassn;
                }



            $message = $e->getMessage();
            //$this->get('translator')->trans('An invalid exception occurred');

            echo 'account/'.$country_file_ext.'/iqamassn.html.twig';
            return $this->render('account/'.$country_file_ext.'/iqamassn.html.twig',
                array('iktData' => $iktUserData, 'posted' => $posted, 'message' => $message)
            );
        }
    }



    /**
     * @Route("/{_country}/{_locale}/account/updatepassword")
     * @param Request $request
     * @return Response
     */
    public function updatepasswordAction(Request $request)
    {
        $tokenStorage = $this->get('security.token_storage');
        //dump($tokenStorage->getToken()->getUser());die();
        $message = '';

            $form = $this->createFormBuilder(null, ['attr' => ['novalidate' => 'novalidate']])
            ->add('old_password', TextType::class, ['label' => "Enter Current Password", 'label_attr' => ['class' => 'CUSTOM_LABEL_CLASS'], 'attr' => ['class' => 'form-control col-lg-12'],
                'constraints' => array(
                    new NotBlank(array('message' => $this->get('translator')->trans('This field is required'))),
                ),
            ])
            ->add('new_password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => $this->get('translator')->trans('Password fields must match'),
                'required' => true,
                'first_options' => array('label' => 'New Password', 'label_attr' => ['class' => 'CUSTOM_LABEL_CLASS']),
                'second_options' => array('label' => 'Confirm New Password', 'label_attr' => ['class' => 'CUSTOM_LABEL_CLASS']),
                'options' => array('attr' => array('class' => 'form-control')),
                'constraints' => array(
                    new NotBlank(array('message' => $this->get('translator')->trans('This field is required'))),
                )
            ])

            ->add($this->get('translator')->trans('Update'), SubmitType::class ,array(
                'attr' => array('class' => 'btn btn-primary'),
            ) )
            ->getForm();



        try {

            $activityLog = $this->get('app.activity_log');
            //check user
            //var_dump($this->get('session'));
            $country_id = $request->get('_country');
            $language   = $request->getLocale();
            //echo '===='.$this->get('session')->get('userSelectedCountry');
            $restClient = $this->get('app.rest_client');
            if(!$this->get('session')->get('iktUserData')) {
                $url = $request->getLocale() . '/api/' . $this->getUser()->getIktCardNo() . '/userinfo.json';
                // echo AppConstant::WEBAPI_URL.$url;
                $data = $restClient->restGet(AppConstant::WEBAPI_URL.$url, array('Country-Id' => strtoupper($request->get('_country'))));
                if($data['success'] == "true") {
                    $this->get('session')->set('iktUserData', $data['user']);
                }
            }
            $iktUserData = $this->get('session')->get('iktUserData');
            if($country_id == 'eg')
            {
                $country_file_ext = 'eg';
                $country_id = 'EG';
            }
            else
            {
                $country_file_ext = 'sa';
                $country_id = 'SA';
            }

            // get logged in user info
            // var_dump($this->getUser());
            // var_dump($iktUserData);
            $posted = array();
            echo $iktCardNo = $iktUserData['C_id'];
            echo '--'.$iktID_no = $iktUserData['ID_no'];
            $iktID_no = $iktUserData['ID_no'];
            //form posted data
            /******/
            $em = $this->getDoctrine()->getManager();
            $userInfoLoggedIn = $em->getRepository('AppBundle:User')->find($iktCardNo);
            echo "hhh".$email = $userInfoLoggedIn->getEmail();
            $form->handleRequest($request);
            /******/

            print_r($userInfoLoggedIn);
            $posted = array();

            $postData = $request->request->all();
            if (!empty($postData))
            {
                /***********/
                // var_dump($objUser);
                // current is the logged in user password
                $objUser = $this->getUser();
                $password_current = $objUser->getPassword();
                $old_password = md5(trim($postData['form']['old_password']));
                /***********/
                //$csrf_token_udatepassword = trim($postData['_csrf_token_udatepassword']);
                if ($form->isValid())
                {
                    if ($password_current != $old_password) {
                            $message = $this->get('translator')->trans('Please enter correct old password');
                            return $this->render('account/updatepassword.html.twig',
                                array('form1' => $form->createView(), 'message' => $message));
                        }
                        $new_password = $postData['form']['new_password'];
                        $userInfoLoggedIn->setPassword(md5($form->get('new_password')->getData()));
                        $em = $this->getDoctrine()->getManager();
                        $em->persist($userInfoLoggedIn);
                        $em->flush();
                        $tokenStorage = $this->get('security.token_storage');
                        $tokenStorage->getToken()->getUser()->setPassword(md5($form->get('new_password')->getData()));
                    $messageLog = $this->get('translator')->trans('Password updated successfully');
                    $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_PASSWORD_SUCCESS, $iktUserData['C_id'], array('iktissab_card_no' => $iktUserData['C_id'], 'message' => $messageLog, 'session' => $iktUserData));
                    $message = $this->get('translator')->trans('Your password is changed.Please logged in again using new password');
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
            $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_PASSWORD_ERROR, $iktUserData['C_id'], array('iktissab_card_no' => $iktUserData['C_id'], 'message' => $e->getMessage(), 'session' => $iktUserData));
            return $this->render('account/updatepassword.html.twig',
                array('form1' => $form->createView(), 'message' => $e->getMessage()));
        }

    }


    /**
     * @Route("/{_country}/{_locale}/account/userinfo" , name="account/userinfo")
     * @param Request $request
     * @return Response
     */
    public function userinfoAction(Request $request)
    {
        try
        {
            $activityLog = $this->get('app.activity_log');
            $tokenStorage = $this->get('security.token_storage');
        // get all cities
        $restClient = $this->get('app.rest_client');
        //$smsService = $this->get('app.sms_service');
        $url = $request->getLocale() . '/api/cities_areas_and_jobs.json';
        $cities_jobs_area = $restClient->restGet(AppConstant::WEBAPI_URL . $url, array('Country-Id' => strtoupper($request->get('_country'))));
        //var_dump($cities_jobs_area);
        //var_dump($cities_jobs_area['cities']);
        //var_dump($cities_jobs_area['jobs']);
        //print_r($cities_jobs_area['areas']);
        /*************/
        // only get cities according to the language provided
        // $url = $request->getLocale() . '/api/get_cities.json';
        // $cities = $restClient->restGet(AppConstant::WEBAPI_URL . $url, array('Country-Id' => strtoupper($request->get('_country'))));
        // var_dump($cities_jobs_area);
        /*************/

        /********/

        $citiesListing = array();
        foreach ($cities_jobs_area['cities'] as $key_city => $value_city)
        {
            if (array_key_exists('name', $value_city)) {

                $citiesListing[($request->getLocale() == 'ar') ? $value_city['name'] : $value_city['name']] = $value_city['city_no'];
            }
        }


        foreach ($cities_jobs_area['jobs'] as $key_job => $value_job)
        {
            if (array_key_exists('name', $value_job)) {
                $jobListing[($request->getLocale() == 'ar') ? $value_job['name'].$value_job['job_no'] : $value_job['name'].$value_job['job_no']] = $value_job['job_no'];
            }
        }

        $arreaListing = array();
        $i = 1;
        foreach ($cities_jobs_area['areas'] as $key_area => $value_area)
        {
            if (array_key_exists('name', $value_area)) {
                if ($request->getLocale() == 'ar') {
                    $arreaListing[trim($value_area['name'])] =  trim($value_area["city_no"].'_'.$value_area['area_code']);
                } else {
                    $i++;
                    $arreaListing[trim($value_area["name"])] = trim($value_area["city_no"].'_'.$value_area['area_code']);
                }
            }
        }


        // dump($tokenStorage->getToken()->getUser());die();

        $message = '';


        $form = $this->createFormBuilder(null, ['attr' => ['novalidate' => 'novalidate' , 'action' => '' ]])
            ->add('iqama', TextType::class, array('attr' => ['class' => 'form-control col-lg-12', 'readonly' => 'readonly'],
                'label' => 'Iqama/SSN Number',
                'constraints' => array(
                    new NotBlank(array('message' => $this->get('translator')->trans('This field is required'))),
                    new Regex(
                        array(
                            'pattern' => ($request->get('_country') == 'sa') ? '/^[1,2]([0-9]){9}$/' : '/^([0-9]){14}$/',
                            'match' => true,
                            'message' => $this->get('translator')->trans('Invalid Iqama Id / SSN'))
                    ),
//                    new Assert\Callback([
//                        'callback' => [$this, 'validateIqama']
//                    ])
                )
            ))
            ->add('dob', DateType::class, array(
                'widget' => 'single_text','format' => 'yyyy-MM-dd',
                'label' => $this->get('translator')->trans('BirthDate ( yyyy-MM-dd)'),
            ))

            ->add('calender_converter', ChoiceType::class, array('attr' => ['class' => ''],
                    'label' => $this->get('translator')->trans('Convert to'),
                    'choices' => array($this->get('translator')->trans('Convert to') => '', 'Hijri' => 'Hijri', 'Gregorian' => 'Gregorian'),
                )
            )
            ->add('dob_result', DateType::class, array(
                'widget' => 'single_text','format' => 'yyyy-MM-dd',
                'label' => $this->get('translator')->trans('Result ( yyyy-MM-dd)'),
            ))


            ->add('maritial_status', ChoiceType::class, array('attr' => ['class' => 'form-control col-lg-12'],
                'label' => 'Marital Status',
                'choices' => array('Single' => 'S', 'Married' => 'M', 'Widow' => 'W', 'Divorce' => 'D'),

            ))

            ->add('job_no', ChoiceType::class, array('attr' => ['class' => 'form-control col-lg-12'],
                'choices' => $jobListing,
                'label' => 'Job',
                'placeholder' => 'Select Job',

            ))
            ->add('city_no', ChoiceType::class, array('attr' => ['class' => 'form-control col-lg-12'],
                'choices' => $citiesListing,
                'label' => $this->get('translator')->trans('City'),
                'placeholder' => 'Select City',

            ))
            ->add('area_no', ChoiceType::class, array('attr' => ['class' => 'form-control col-lg-12'],
                'choices' => $arreaListing,
                'label' => $this->get('translator')->trans('Area'),
                'placeholder' => $this->get('translator')->trans('Select Area'),

            ))



            ->add('language', ChoiceType::class, array('attr' => ['class' => 'form-control col-lg-12'],
                    'label' => $this->get('translator')->trans('Select Language'),
                    'choices' => array('Preffered Language' => '', 'Arabic' => 'A', 'English' => 'E'),

                )
            )
            ->add('houseno', NumberType::class, array('attr' => ['class' => 'form-control col-lg-12'],'label' => 'House Number',

                ))
            ->add('pobox', NumberType::class, array('attr' => ['class' => 'form-control col-lg-12'],'label' => 'PO Box'))
            ->add('zip', NumberType::class, array('attr' => ['class' => 'form-control col-lg-12'],'label' => 'Zip Code'))
            ->add('tel_office', NumberType::class, array('attr' => ['class' => 'form-control col-lg-12'],'label' => 'Telephone (Office)'))
            ->add('tel_home', NumberType::class, array('attr' => ['class' => 'form-control col-lg-12'],'label' => 'Telephone (Home)'))

            ->add('pur_group', ChoiceType::class, array('attr' => ['class' => 'form-control col-lg-12'],
                'label' => 'Shoppers',
                'placeholder' => 'Select Shopper',
                'choices' => array('Husband' => '1', 'Wife' => '2', 'Children' => '3', 'Relative' => '4', 'Applicant' => '5', 'Servent' => '6'),

            ))
            ->add('selected_city', HiddenType::class, array('attr' => ['class' => 'form-control col-lg-12'],'label' => ''))
            ->add($this->get('translator')->trans('Update'), SubmitType::class ,array(
                'attr' => array('class' => 'btn btn-primary'),
            ) )

            ->getForm();

            $activityLog = $this->get('app.activity_log');
            $data = $request->request->all();
            /******************/
            //$this->get('session')->set('pass', md5($pData['password']));
            /******************/



            //check user
            //var_dump($this->get('session'));
            $country_id = $request->get('_country');
            $language   = $request->getLocale();
            //echo '===='.$this->get('session')->get('userSelectedCountry');
            $restClient = $this->get('app.rest_client');
            if($this->get('session')->get('iktUserData')) {
                $url = $request->getLocale() . '/api/' . $this->getUser()->getIktCardNo() . '/userinfo.json';
                // echo AppConstant::WEBAPI_URL.$url;
                $data = $restClient->restGet(AppConstant::WEBAPI_URL.$url, array('Country-Id' => strtoupper($request->get('_country'))));
                print_r($data['user']);
                if($data['success'] == "true") {
                    $this->get('session')->set('iktUserData', $data['user']);
                }
            }
            $iktUserData = $this->get('session')->get('iktUserData');

            if($country_id == 'eg')
            {
                $country_file_ext = 'eg';
                $country_id = 'EG';
            }
            else
            {
                $country_file_ext = 'sa';
                $country_id = 'SA';
            }

            // get logged in user info
            // var_dump($this->getUser());
            var_dump($iktUserData);
            $posted = array();
            echo $iktCardNo = $iktUserData['C_id'];
            echo '--'.$iktID_no = $iktUserData['ID_no'];
            $iktID_no = $iktUserData['ID_no'];
            // set form data
            $form->get('iqama')->setData($iktID_no);

            //form posted data

            /******/
            $em = $this->getDoctrine()->getManager();
            $form->handleRequest($request);
            /******/
            $postData = $request->request->all();
            var_dump($postData);
            echo 'test1';
            if (!empty($postData))
            {
                if ($form->isValid())
                {
                    /***********/
                    //echo $dateofbirth   = $postData['form_dob_year'].'-'.$postData['form_dob_month'].'-'.$postData['form_dob_day'];
                    /***********/
                    echo "test2".$postData['form']['maritial_status'];
                    /*****************/
                    // manipulating old values from logged user data;

                    $Marital_status = substr($iktUserData['marital_status_en'],0,1);
                    $G_birthdate    = $iktUserData['birthdate'];
                    $job_no         = $iktUserData['job_no'];
                    $city_no        = $iktUserData['city_no'];
                    $area           = $iktUserData['area'];
                    $lang           = $iktUserData['lang'];
                    $houseno        = $iktUserData['houseno'];
                    $pobox          = $iktUserData['pobox'];
                    $zip            = $iktUserData['zip'];
                    $tel_home       = $iktUserData['tel_home'];
                    $tel_office     = $iktUserData['tel_office'];
                    $pur_group      = $iktUserData['pur_grp'];
                    /*****************/

                    /*if($postData['form']['dob']['year'] != "" ) {
                        $birth_date = $postData['form']['dob']['year'] . "-" . $postData['form']['dob']['month'] . "-" . $postData['form']['dob']['day'];
                    }else{
                        $birth_date = "";
                    }*/
                    if($postData['form']['dob_result'] !="")
                    {
                        $birth_date = $postData['form']['dob_result'];
                    }
                    else
                    {
                        if($postData['form']['dob'] !="")
                        {
                            $birth_date = $postData['form']['dob'];
                        }
                        else
                        {
                            $birth_date = "";
                        }
                    }
                    echo "-------......".$birth_date;
                    echo "test4";
                    if($postData['form']['area_no'] !=""){
                        $area_no = explode("_",$postData['form']['area_no']);
                        $area_no = $area_no[1];
                    }
                    else
                    {
                        $area_no = "";
                    }


                    $Data = array(
                        "birthdate"            => $birth_date,
                        "marital_status_en"    => $postData['form']['maritial_status'],
                        "job_no"               => $postData['form']['job_no'],
                        "city_no"              => $postData['form']['city_no'],
                        "area"                 => $area_no,
                        "lang"                 => $postData['form']['language'],
                        "houseno"              => $postData['form']['houseno'],
                        "pobox"                => $postData['form']['pobox'],
                        "zip"                  => $postData['form']['zip'],
                        "tel_home"             => $postData['form']['tel_home'],
                        "tel_office"           => $postData['form']['tel_office'],
                        "pur_grp"              => $postData['form']['pur_group']
                    );
                    /*
                        $Data = array(
                    "   G_birthdate"       => $birth_date,
                    "   city_no"              => $postData['form']['city_no']
                    );*/
                echo "test5";
                echo '========-----11----=========';
                var_dump($postData);
                var_dump($Data);
                echo '========-----11----=========';
                $url = $request->getLocale() . '/api/update_user_detail.json';
                //$this->get('session')->set('edit_customer', $Data);
                $i=0;
                foreach($Data as $key => $key_value) {
                    //echo $kay_value = trim($key_value);

                    if($key_value !="")
                    {
                        echo "Key = " . $key . ", Value = " . $key_value ."==<br>";
                        $key_field = $key;
                        if($key == 'birthdate'){
                            $key_field = 'G_birthdate';
                        }
                        if($key == 'marital_status_en'){
                            $key_field = 'Marital_status';
                        }



                        $form_data[$i]   =     array(
                            'C_id'      =>  $iktCardNo,
                            'field'     =>  $key_field,
                            'new_value' =>  $key_value,
                            'old_value' =>  $iktUserData[$key],
                            'comments'  =>  'Update registered user details for field'.$key_field
                        );
                        $i++;
                    }
                }
                $postData = json_encode($form_data);
                //print_r($postData);

                $data = $restClient->restPost(AppConstant::WEBAPI_URL . $url, $postData, array('Country-Id' => strtoupper($request->get('_country'))));
                print_r($data);
                // var_dump($Data);
                /***********/
                //$csrf_token_udatepassword = trim($postData['_csrf_token_udatepassword']);
                // $new_password = $postData['form']['new_password'];
                    $messageLog = $this->get('translator')->trans('User details Updated');
                    $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_USERINFO_SUCCESS, $iktUserData['C_id'], array('iktissab_card_no' => $iktUserData['C_id'], 'message' => $messageLog, 'session' => $iktUserData));
                    $message = $this->get('translator')->trans('Account updated successfully');
                    return $this->render('account/userinfo.html.twig',
                        array('form1' => $form->createView(), 'message' => $message));
                }
                else
                {
                    $message = $this->get('translator')->trans('Invalid token detected.Please submit again');
                    $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_USERINFO_ERROR, $iktUserData['C_id'], array('iktissab_card_no' => $iktUserData['C_id'], 'message' => $message, 'session' => $iktUserData));
                    return $this->render('account/userinfo.html.twig',
                        array('form1' => $form->createView(), 'message' => $message));
                }
            }
            else
            {
                $message = '';
                return $this->render('account/userinfo.html.twig',
                    array('form1' => $form->createView(), 'message' => $message));
            }
        }
        catch (\Exception $e)
        {
            return new Response($e);exit;
            $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_USERINFO_ERROR, $iktUserData['C_id'], array('iktissab_card_no' => $iktUserData['C_id'], 'message' => $e->getMessage(), 'session' => $iktUserData));
            return $this->render('account/userinfo.html.twig',
                array('form1' => $form->createView(), 'message' => $e->getMessage()));
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
     * @Route("/{_country}/{_locale}/account/missingcard")
     * @param Request $request
     * @return Response
     */
    public function missingCardAction(Request $request)
    {
        try
        {
            $activityLog = $this->get('app.activity_log');
            //check user
            //var_dump($this->get('session'));
            $country_id  = $request->get('_country');
            $language    = $request->getLocale();
            //echo '===='.$this->get('session')->get('userSelectedCountry');
            echo $this->getUser()->getIktCardNo();
            $restClient  = $this->get('app.rest_client');
            if(!$this->get('session')->get('iktUserData'))
            {
                $url = $request->getLocale() . '/api/' . $this->getUser()->getIktCardNo() . '/userinfo.json';
                // echo AppConstant::WEBAPI_URL.$url;
                $data = $restClient->restGet(AppConstant::WEBAPI_URL . $url, array('Country-Id' => strtoupper($request->get('_country'))));
                if($data['success'] == "true")
                {
                    $this->get('session')->set('iktUserData', $data['user']);
                }
            }
            $iktUserData = $this->get('session')->get('iktUserData');
            if($country_id == 'eg')
            {
                $country_file_ext = 'eg';
                $country_id = 'EG';
            }
            else
            {
                $country_file_ext = 'sa';
                $country_id = 'SA';
            }
            //var_dump($iktUserData);
            $posted = array();
            $iktCardNo = $iktUserData['C_id'];
            echo '--'.$iktID_no = $iktUserData['ID_no'];

            $iktID_no = $iktUserData['ID_no'];
            $iktMobile_no = $iktUserData['mobile'];

            //get current email of the user
            //form posted data

            $data = $request->request->all();
            $posted = array();
            $url = $request->getLocale() . '/api/update_lost_card.json';
            if(!empty($data)) {
                $csrf_token = trim($data['_csrf_token']);
                if ($this->isCsrfTokenValid('auth_missigcard', $csrf_token))
                {
                    // here we will add validation to the form
                    /************************/
                    $missingcard_registered_iqamaid = trim($data['missingcard_registered_iqamaid']);
                    $new_iktissab_id = trim($data['new_iktissab_id']);
                    $confirm_iktissab_id = trim($data['confirm_iktissab_id']);
                    $comment_missingcard = trim($data['comment_missingcard']);


                    $posted['missingcard_registered_iqamaid'] = $missingcard_registered_iqamaid;
                    $posted['new_iktissab_id'] = $new_iktissab_id;
                    $posted['confirm_iktissab_id'] = $confirm_iktissab_id;
                    $posted['comment_missingcard'] = $comment_missingcard;
                    /************************/
                    if ($country_id == 'SA') {
                        if (!preg_match('/^[0-9]{8}$/', $new_iktissab_id)) {
                            $message = $this->get('translator')->trans('Please enter valid Iktissab Card no');
                            return $this->render('account/' . $country_file_ext . '/missingcard.html.twig',
                                array('iktData' => $iktUserData, 'posted' => $posted, 'message' => $message)
                            );
                        } else {
                            $first_ch = substr($new_iktissab_id, 0, 1);
                            if ($first_ch != 9) {
                                $message = $this->get('translator')->trans('Iktissab card number must start with 9 for Saudi Arabia');
                                return $this->render('account/' . $country_file_ext . '/missingcard.html.twig',
                                    array('iktData' => $iktUserData, 'posted' => $posted, 'message' => $message)
                                );
                            }
                        }

                        if (!preg_match('/^[0-9]{8}$/', $confirm_iktissab_id)) {
                            $message = $this->get('translator')->trans('Please enter valid Iktissab Card no');
                            return $this->render('account/' . $country_file_ext . '/missingcard.html.twig',
                                array('iktData' => $iktUserData, 'posted' => $posted, 'message' => $message)
                            );
                        } else {
                            $first_ch = substr($confirm_iktissab_id, 0, 1);
                            if ($first_ch != 9) {
                                $message = $this->get('translator')->trans('Iktissab card number must start with 9 for Saudi Arabia');
                                return $this->render('account/' . $country_file_ext . '/missingcard.html.twig',
                                    array('iktData' => $iktUserData, 'posted' => $posted, 'message' => $message)
                                );
                            }
                        }
                        if ($comment_missingcard == "") {
                            $message = $this->get('translator')->trans('Please enter comments');
                            return $this->render('account/' . $country_file_ext . '/missingcard.html.twig',
                                array('iktData' => $iktUserData, 'posted' => $posted, 'message' => $message)
                            );
                        }
                    }

                    if ($country_id == 'EG') {
                        if (!preg_match('/^[0-9]{8}$/', $new_iktissab_id)) {
                            $message = $this->get('translator')->trans('Please enter valid Iktissab Card no');
                            return $this->render('account/' . $country_file_ext . '/missingcard.html.twig',
                                array('iktData' => $iktUserData, 'posted' => $posted, 'message' => $message)
                            );
                        } else {
                            $first_ch = substr($new_iktissab_id, 0, 1);
                            if ($first_ch != 5) {
                                $message = $this->get('translator')->trans('Iktissab card number must start with 5 for Egypt');
                                return $this->render('account/' . $country_file_ext . '/missingcard.html.twig',
                                    array('iktData' => $iktUserData, 'posted' => $posted, 'message' => $message)
                                );
                            }
                        }

                        if (!preg_match('/^[0-9]{8}$/', $confirm_iktissab_id)) {
                            $message = $this->get('translator')->trans('Please enter valid Iktissab Card no');
                            return $this->render('account/' . $country_file_ext . '/missingcard.html.twig',
                                array('iktData' => $iktUserData, 'posted' => $posted, 'message' => $message)
                            );
                        } else {
                            $first_ch = substr($confirm_iktissab_id, 0, 1);
                            if ($first_ch != 5) {
                                $message = $this->get('translator')->trans('Iktissab card number must start with 5 for Egypt');
                                return $this->render('account/' . $country_file_ext . '/missingcard.html.twig',
                                    array('iktData' => $iktUserData, 'posted' => $posted, 'message' => $message)
                                );
                            }
                        }


                        if ($comment_missingcard == "") {
                            $message = $this->get('translator')->trans('Please enter comments');
                            return $this->render('account/' . $country_file_ext . '/missingcard.html.twig',
                                array('iktData' => $iktUserData, 'posted' => $posted, 'message' => $message)
                            );
                        }
                    }
                    /**************************/
                    $form_data = array(
                        'ID_no' => $iktID_no,
                        'field' => 'lostcard',
                        'old_value' => $iktCardNo,
                        'new_value' => $data['new_iktissab_id'],
                        'comments' => $data['comment_missingcard'],
                        'mobile' => $iktMobile_no
                    );

                    $postData = json_encode($form_data);
                    $request->get('_country');
                    $data = $restClient->restPost(AppConstant::WEBAPI_URL . $url, $postData, array('Country-Id' => strtoupper($request->get('_country'))));
                    print_r($data);
                    if ($data['success']) {
                        // posted array is emty to clear the form after successful transction
                        $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_MISSINGCARD_SUCCESS, $iktUserData['C_id'], array('iktissab_card_no' => $iktUserData['C_id'], 'message' => $data['message'], 'session' => $iktUserData));
                        $posted = "";
                        $message = $this->get('translator')->trans($data['message']);
                        return $this->render('account/' . $country_file_ext . '/missingcard.html.twig',
                            array('iktData' => $iktUserData, 'posted' => $posted, 'message' => $message)
                        );
                    }
                    if (!$data['success']) {
                        $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_MISSINGCARD_ERROR, $iktUserData['C_id'], array('iktissab_card_no' => $iktUserData['C_id'], 'message' => $data['message'], 'session' => $iktUserData));
                        $message = $this->get('translator')->trans($data['message']);
                        return $this->render('account/' . $country_file_ext . '/missingcard.html.twig',
                            array('iktData' => $iktUserData, 'posted' => $posted, 'message' => $message)
                        );
                    }
                    if (!$data['success'] == 'INVALID_DATA') {
                        $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_MISSINGCARD_ERROR, $iktUserData['C_id'], array('iktissab_card_no' => $iktUserData['C_id'], 'message' => $data['message'], 'session' => $iktUserData));

                        $message = $this->get('translator')->trans($data['message']);
                        return $this->render('account/' . $country_file_ext . '/missingcard.html.twig',
                            array('iktData' => $iktUserData, 'posted' => $posted, 'message' => $message)
                        );
                    }
                }
                else
                {
                    // this will load view for the first time when user click on the update mobile link
                    $message = $this->get('translator')->trans('Invalid token detected.Please submit again');
                    // empty the posted data
                    $posted = "";
                    echo 'account/'.$country_file_ext.'/missingcard.html.twig';
                    return $this->render('account/'.$country_file_ext.'/missingcard.html.twig',
                        array('iktData' => $iktUserData, 'posted' => $posted, 'message' => $message)
                    );
                }
            }
            else
            {
                // this will load view for the first time when user click on the update mobile link
                $message = $this->get('translator')->trans('');
                // empty the posted data
                $posted = "";
                echo 'account/'.$country_file_ext.'/missingcard.html.twig';
                return $this->render('account/'.$country_file_ext.'/missingcard.html.twig',
                    array('iktData' => $iktUserData, 'posted' => $posted, 'message' => $message)
                );
            }
        }
        catch (\Exception $e)
        {
            $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_MISSINGCARD_ERROR, $iktUserData['C_id'] , array('iktissab_card_no' => $iktUserData['C_id'], 'message' => $e->getMessage(), 'session' => $iktUserData));
            // $error['success'] = false;
            // $error['message'] = $e->getMessage();
            $e->getMessage();
            $data = $request->request->all();
            if(!empty($data))
            {
                // here we will add validation to the form
                /************************/
                $missingcard_registered_iqamaid = trim($data['missingcard_registered_iqamaid']);
                $new_iktissab_id = trim($data['new_iktissab_id']);
                $confirm_iktissab_id = trim($data['confirm_iktissab_id']);
                $comment_missingcard = trim($data['comment_missingcard']);

                $posted['missingcard_registered_iqamaid'] = $missingcard_registered_iqamaid;
                $posted['new_iktissab_id'] = $new_iktissab_id;
                $posted['confirm_iktissab_id'] = $confirm_iktissab_id;
                $posted['comment_missingcard'] = $comment_missingcard;
            }
            $message = $e->getMessage();
            //$this->get('translator')->trans('An invalid exception occurred');
            echo 'account/'.$country_file_ext.'/missingcard.html.twig';
            return $this->render('account/'.$country_file_ext.'/missingcard.html.twig',
                array('iktData' => $iktUserData, 'posted' => $posted, 'message' => $message)
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

    /**
     * @Route("/account/dateofbirthconverter/{datetoconvert}/{conversionto}")
     * @param Request $request
     * @return Response
     */

    public function dateOfBirhtConverterAction(Request $request,$datetoconvert , $conversionto)
    {
        try {
            //$DateConv = new HijriGregorianConvert();
            $datetoconvert = $request->get('datetoconvert');
            $conversionto = $request->get('conversionto');

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




}