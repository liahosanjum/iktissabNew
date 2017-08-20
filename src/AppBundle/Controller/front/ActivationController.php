<?php
/**
 * Created by PhpStorm.
 * User: abdulali
 * Date: 12/28/16
 * Time: 12:44 PM
 */

namespace AppBundle\Controller\Front;


use AppBundle\AppConstant;
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

class ActivationController extends Controller
{
    /**
     * @Route("/{_country}/{_locale}/card-activation", name="front_card_activation")
     * @param Request $request
     * @return Response
     */
    public function cardActivationAction(Request $request)
    {
        $error = array('success' => true);
        $country_id  = $request->get('_country');
        $form = $this->createForm(ActivateCardoneType::class, array() ,
            array('extras' => array('country' => $country_id)
            ));
        $form->handleRequest($request);
        $pData = $form->getData();
        if ($form->isSubmitted() && $form->isValid())
        {
            try {

                $this->checkOnline($pData);
                // check if card valid from local/office ikt database
                $scenerio = $this->checkScenerio($pData['iktCardNo']);
                // proceed to next step with full form registration
                $this->get('session')->set('scenerio', $scenerio);
                $this->get('session')->set('iktCardNo', $pData['iktCardNo']);
                $this->get('session')->set('email', $pData['email']);
                $acrivityLog = $this->get('app.activity_log');
                $this->checkInStagging($pData['iktCardNo']);
                //exit;
                if ($this->get('session')->get('scenerio') == AppConstant::IKT_REG_SCENERIO_1) {
                    // make log
                    $acrivityLog->logEvent(AppConstant::ACTIVITY_NEW_CARD_REGISTRATION, 1, array('ikt_card' => $pData['iktCardNo'], 'email' => $pData['email']));
                    // proceed to full form registration
                    return $this->redirectToRoute('customer_information', array('_locale' => $request->getLocale(), '_country' => $request->get('_country')));
                } elseif ($this->get('session')->get('scenerio') == AppConstant::IKT_REG_SCENERIO_2) {
                    echo "inside elseif";
                    $acrivityLog->logEvent(AppConstant::ACTIVITY_EXISTING_CARD_REGISTRATION, 1, array('ikt_card' => $pData['iktCardNo'], 'email' => $pData['email']));
                    // proceed to only update email page to login
                    return $this->redirectToRoute('activate_card', array('_locale' => $request->getLocale(), '_country' => $request->get('_country')));
                } else {
                    echo "inside else";
                }
            }
            catch (Exception $e)
            {
                $error['success'] = false;
                $error['message'] = $e->getMessage();
            }
        }
        return $this->render('/activation/activation.twig',
            array(
                'form' => $form->createView(),
                'error' => $error,
            )
        );
    }



    public function checkOnline($pData)
    {
        $em = $this->getDoctrine()->getEntityManager();
        // First step to check the  email in mysql db



        $checkEmail = $em->getRepository('AppBundle:User')->findOneByEmail($pData['email']);
        if (!is_null($checkEmail)) {
            Throw new Exception($this->get('translator')->trans('The new email is already registered before , please enter a valid email'), 100);
        }
        // Second step to check the  ikt card in mysql db
        $checkIktCard = $em->getRepository('AppBundle:User')->find($pData['iktCardNo']);
        //print_r($checkIktCard->getIktCardNo().'--'.$checkIktCard->getStatus());exit;
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
            $restClient = $this->get('app.rest_client')->IsAdmin(true);
            $request = $this->container->get('request_stack')->getCurrentRequest();
            $locale = $request->getLocale();
            $url = $request->getLocale() . '/api/' . $iktCardNo . '/card_status.json';
            // todo: adding admin previleges
            $data = $restClient->restGet(AppConstant::WEBAPI_URL . $url, array('Country-Id' => strtoupper($request->get('_country'))));
                if ($data['success'] != true) {
                    Throw new Exception($this->get('translator')->trans('Invalid Iktissab Card Number'), 1);
                    // Throw new Exception($this->get('translator')->trans($data['message']), 1);
                } else {
                    if ($data['data']['cust_status'] == 'Active' || $data['data']['cust_status'] == 'In-Active') {
                        return AppConstant::IKT_REG_SCENERIO_2;
                    } elseif ($data['data']['cust_status'] == 'NEW' || $data['data']['cust_status'] == 'Distributed') {
                        return AppConstant::IKT_REG_SCENERIO_1;
                    }
                }

        return true;

    }

    /**
     * @Route("/{_country}/{_locale}/activate-card", name="activate_card")
     */
    public function activateCardAction(Request $request)
    {
        // check referal
        // to do: uncomment below
        // if (!$this->isReferalValid('card_activation')) {
        // return $this->redirectToRoute('front_card_activation',array('_locale'=> $request->getLocale(),'_country' => $request->get('_country')));
        // }
        $restClient = $this->get('app.rest_client')->IsAdmin(true);
        $smsService = $this->get('app.sms_service');
        // get existing user data
        $url  = $request->getLocale() . '/api/' . $this->get('session')->get('iktCardNo') . '/userinfo.json';
        $data = $restClient->restGet(AppConstant::WEBAPI_URL . $url, array('Country-Id' => strtoupper($request->get('_country'))));

        if ($data['success'] == "true") {
            $this->get('session')->set('iktUserData', $data['user']);
        }


        $error = array('success' => true);
        $fData = array('iktCardNo' => $this->get('session')->get('iktCardNo'), 'email' => $this->get('session')->get('email'));
        $form  = $this->createForm(IktCustomerInfo::class, $fData);
        $form->handleRequest($request);
        $pData = $form->getData();
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->get('session')->set('pass',md5($pData['password']));
                $this->get('session')->set('password_unmd5',$pData['password']);
                
                $this->get('session')->set('mobile',$data['user']['mobile']);
                
                $otp = rand(111111, 999999);
                $this->get('session')->set('otp', $otp);

                if($request->getLocale() == 'ar'){
                    $message = $this->get('translator')->trans("الرجاء إدخال الرمز المؤقت التالي للاستمرار بعملية التسجيل في موقع اكتساب:".$otp);
                }
                else{
                    $message = $this->get('translator')->trans("Please insert this temporary code to continue with Iktissab Card registration:".$otp);
                }
                $acrivityLog = $this->get('app.activity_log');
                //send sms code
                $smsService->sendSms($data['user']['mobile'], $message, $request->get('_country'));

                $acrivityLog->logEvent(AppConstant::ACTIVITY_SEND_SMS, 1, array('message' => $message, 'session' => $data['user']));
                $request->getSession()
                    ->getFlashBag()
                    ->add('smsSuccess', 'One time password has been sent to your mobile, please enter to continue!');
                return $this->redirectToRoute('enter_otp', array('_locale' => $request->getLocale(), '_country' => $request->get('_country')));
            } catch (Exception $e) {

            }
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
        // get all cities
        $restClient = $this->get('app.rest_client');
        $smsService = $this->get('app.sms_service');
        $url = $request->getLocale() . '/api/cities_areas_and_jobs.json';
        // todo: adding admin previleges
        $cities_jobs_area = $restClient->restGet(AppConstant::WEBAPI_URL . $url, array('Country-Id' => strtoupper($request->get('_country'))));
//        var_dump($cities_jobs_area); die('---');
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
            $areasArranged[$value['name']] = $value['name'];
        }
        $areasArranged['-1'] = '-1';
        $areas = $this->json($cities_jobs_area['areas']);

        $pData = array('iktCardNo' => $this->get('session')->get('iktCardNo'), 'email' => $this->get('session')->get('email'));

        $form = $this->createForm(IktRegType::class, $pData, array(
                    'additional' => array(
                    'locale' => $request->getLocale(),
                    'country' => $request->get('_country'),
                    'cities' => $citiesArranged,
                    'jobs' => $jobsArranged,
                    'areas' => $areasArranged,
                )
            )
        );
        $form->handleRequest($request);

        $pData = $form->getData();

        $error = array('success' => true);
//        dump($_POST); die('--');
        if ($form->isValid() && $form->isSubmitted()) {
            try {
                $area = ($pData['area_no'] == '-1' ) ? $pData['area_text'] : $pData['area_no'];

                // check iqama validity
                if($request->get('_country') == 'sa') {
                    $this->validateIqama($pData['iqama']);
                }
                // check iqama in local SQl db
                $this->checkIqamaLocal($pData['iqama']);

                if($pData['date_type'] == 'h'){
                    $dob = $pData['dob_h']->format('Y-m-d h:i:s');
                }else{
                    $dob = $pData['dob']->format('Y-m-d h:i:s');
                }
                // save the provided data to session
                $newCustomer = array(
                    "C_id" => $pData['iktCardNo'],
                    "cname" => $pData['fullName'],
                    "area" => $area,
                    "city_no" => $pData['city_no'],
                    "mobile" => ($request->get('_country') == 'sa' ? "0" : "0020") . $pData['mobile'],
                    "email" => $pData['email'],
                    "nat_no" => $pData['nationality']->getId(),
                    "Marital_status" => $pData['maritial_status'],
                    "ID_no" => $pData['iqama'],
                    "job_no" => $pData['job_no'],
                    "gender" => $pData['gender'],
                    "pur_grp" => $pData['pur_group'],
                    "birthdate" => $dob,
                    "pincode" => mt_rand(1000, 9999),
                    "source" => User::ACTIVATION_SOURCE_WEB
                );

                if($request->get('_country') == 'eg'){
                    $mob_with_country_code = "0020".$pData['mobile'];
                }else{
                    // here no country code
                    $mob_with_country_code = $pData['mobile'];
                }
                $this->get('session')->set('new_customer', $newCustomer);
                $this->get('session')->set('password_unmd5',$pData['password']);
                $this->get('session')->set('pass', md5($pData['password']));
                $this->get('session')->set('mobile', $mob_with_country_code);

                $otp = rand(111111, 999999);
                $this->get('session')->set('otp', $otp);
                if($request->getLocale() == 'ar'){
                    $message = $this->get('translator')->trans("الرجاء إدخال الرمز المؤقت التالي للاستمرار بعملية التسجيل في موقع اكتساب:".$otp);
                }
                else{
                    $message = $this->get('translator')->trans("Please insert this temporary code to continue with Iktissab Card registration:".$otp);
                }



                $acrivityLog = $this->get('app.activity_log');
                //send sms code
                $smsService->sendSms($pData['mobile'], $message, $request->get('_country'));
                $acrivityLog->logEvent(AppConstant::ACTIVITY_SEND_SMS, 1, array('message' => $message, 'session' => $newCustomer));

                // one status code mobily that sms sent sucessfully
                $request->getSession()
                    ->getFlashBag()
                    ->add('smsSuccess', 'One time password has been sent to your mobile, please enter to continue!');
                return $this->redirectToRoute('enter_otp', array('_locale' => $request->getLocale(), '_country' => $request->get('_country')));

            } catch (Exception $e) {
                $error['success'] = false;
                $error['message'] = $e->getMessage();
            }
        }
        $reference_year = array('gyear'=>2017, 'hyear'=>1438);
        $current_year = date('Y');
        $islamicYear = ($current_year - $reference_year['gyear']) + $reference_year['hyear'];
        $iktCardNo = $this->get('session')->get('iktCardNo');
        return $this->render('/activation/customer_information.twig',
            array('form' => $form->createView(),
                'error' => $error,
                'areas' => $areas,
                'islamicyear' => $islamicYear,
                'iktCardNo' => $iktCardNo
        ));


    }

    function checkIqamaLocal($iqama)
    { // iqama validation in local MSSQL db
        $restClient = $this->get('app.rest_client')->IsAdmin(true);
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $url = $request->getLocale() . '/api/' . $iqama . '/is_ssn_used.json';
        // todo:addingadmin previleges

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
        $activityLog = $this->get('app.activity_log');
        $this->get('session')->get('otp');
        $error = array('success' => true);
        $form = $this->createForm(EnterOtpType::class);
        $form->handleRequest($request);
        $restClient = $this->get('app.rest_client');
        $smsService = $this->get('app.sms_service');
        $scenerio = $this->get('session')->get('scenerio');
        if ($form->isSubmitted() && $form->isValid()) {
            switch ($scenerio) {
                case AppConstant::IKT_REG_SCENERIO_2:
                    $em = $this->getDoctrine()->getEntityManager();
                    if ($form->getData()['otp'] != $this->get('session')->get('otp')) {
                        $form->get('otp')->addError(new FormError($this->get('translator')->trans('Please enter correct verification code')));
                    } else {
                        //try catch adde by sohail
                        try
                        {
                            $user = new User();
                            $user->setEmail($this->get('session')->get('email'));
                            $user->setIktCardNo($this->get('session')->get('iktCardNo'));
                            $user->setRegDate(time());
                            $user->setPassword($this->get('session')->get('pass'));
                            $user->setStatus('0');
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
                                $message = $this->get('translator')->trans('Welcome to iktissab website your Username:' . $this->get('session')->get('email') . 'and Password: ' . $this->get('session')->get('password_unmd5'));
                            }


                            $smsService->sendSms($this->get('session')->get('mobile'), $message, $request->get('_country'));

                            // sohail code if send sms is successfull then creat the
                            // same account in offline db for accessing services.


                            $form_data = array(
                                'email' => $this->get('session')->get('email'),
                                'password' => $this->get('session')->get('pass'),
                                'country' => $request->get('_country'),
                                'status' => 1,
                                'ActivationSource' => 'w',
                                'C_id' => $this->get('session')->get('iktCardNo')
                            );
                            $this->createTempUser($request, $form_data);

                            $ikt_sec2_data = $this->get('session')->get('iktUserData');
                            $activityLog->logEvent(AppConstant::ACTIVITY_EXISTING_CARD_REGISTRATION_SUCCESS, $this->get('session')->get('iktCardNo'), array('message' => $message, 'session' => json_encode($this->get('session')->get('iktUserData'))));
                            $message = \Swift_Message::newInstance();
                            $message->addTo($this->get('session')->get('email'), $this->get('session')->get('email'))
                                ->addFrom($this->getParameter('mailer_user'))
                                ->setSubject(AppConstant::EMAIL_SUBJECT)
                                ->setBody(
                                    $this->renderView(':email-templates/customers:new-account-creation.html.twig', ['customer' => $ikt_sec2_data['cname'] , 'email' => $this->get('session')->get('email'),
                                        'password' => $this->get('session')->get('password_unmd5')
                                    ]),
                                    'text/html'
                                );
                            $this->get('mailer')->send($message);
                            return $this->redirectToRoute('activation_thanks', array('_locale' => $request->getLocale(), '_country' => $request->get('_country')));
                        }
                        catch(\Exception $e)
                        {
                            $activityLog->logEvent(AppConstant::ACTIVITY_NEW_CARD_REGISTRATION_ERROR, 1, array('iktissab_card_no' =>  $this->get('session')->get('name'), 'message' => $e->getMessage(), 'session' => json_encode($this->get('session')->get('iktUserData')) ));
                            $error['success'] = false;
                            $error['message'] = $e->getMessage();
                        }
                    }

                    break;
                case AppConstant::IKT_REG_SCENERIO_1;

                    if ($form->getData()['otp'] != $this->get('session')->get('otp')) {
                        $form->get('otp')->addError(new FormError($this->get('translator')->trans('Please enter correct verification code')));
                    } else {
                        $newCustomer = $this->get('session')->get('new_customer');
                        // check in stagging // check in live db
                        try {

                            $this->checckBeforeAdd($newCustomer['C_id']);
                            $this->checkIqamaLocal($newCustomer['ID_no']);
                            $this->add();
                            if($request->getLocale() == 'ar'){
                                $message = $this->get('translator')->trans('اهلاً بك في موقع اكتساب. اسم المستخدم الخاص بك:' . $this->get('session')->get('email').' و كلمة المرور :'.$this->get('session')->get('password_unmd5'));
                            }
                            else {
                                $message = $this->get('translator')->trans('Welcome to iktissab website your Username:' . $this->get('session')->get('email') . 'and Password: '.$this->get('session')->get('password_unmd5') );
                            }

                            $smsService->sendSms($this->get('session')->get('mobile'), $message, $request->get('_country'));

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
                            $activityLog->logEvent(AppConstant::ACTIVITY_NEW_CARD_REGISTRATION_SUCCESS, $newCustomer['C_id'], array('message' => $message, 'session' => $newCustomer));
                            return $this->redirectToRoute('activation_thanks', array('_locale' => $request->getLocale(), '_country' => $request->get('_country')));

                        } catch (Exception $e) {
                            $activityLog->logEvent(AppConstant::ACTIVITY_NEW_CARD_REGISTRATION_ERROR, 1, array('iktissab_card_no' => $newCustomer['C_id'], 'message' => $e->getMessage(), 'session' => $newCustomer));
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
        // print_r($data);
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
        $em = $this->getDoctrine()->getEntityManager();
        $newCustomer = $this->get('session')->get('new_customer');
        $url = $request->getLocale() . '/api/add_new_user.json';
        $restClient = $this->get('app.rest_client');
        $cData = json_encode($newCustomer);
        try {
            $this->checkOnline(array('iktCardNo' => $newCustomer['C_id'], 'email' => $newCustomer['email']));
            $saveCustomer = $restClient->restPost(AppConstant::WEBAPI_URL . $url, $cData, array('Country-Id' => strtoupper($request->get('_country'))));
            //var_dump($cData);
            //var_dump($url);
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
        $success = $request->getSession()->getFlashBag()->get('ikt_success');
        // code added by sohail
        $ikt_card_no    = $this->get('session')->get('iktCardNo');
        $session = $request->getSession();
        $session->invalidate();
        // code added by sohail
        return $this->render('/activation/thanks.twig', [
            'success' => $success,
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
    public function test2Action(Request $request)
    {
        $smsService = $this->get('app.sms_service');
        //$message = $this->get('translator')->trans('Creation Date');

        $message = $this->get('translator')->trans('Username');
        $acrivityLog = $this->get('app.activity_log');
        //send sms code
        $smsService->sendSmsTest('0583847092', $message, 'sa');
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
                    'country'  => $country_id,
                    'status'    => 1,
                    'ActivationSource' => 'w',
                    'C_id'      => $form_param['C_id']
            );
            $postData = json_encode($form_data);
            // print_r($postData);

            $data = $restClient->restPostForm(AppConstant::WEBAPI_URL . $url, $postData, array('Country-Id' => 'sa'));
            //print_r($data);exit;
            if($data['status'] == 1)
            {
                if ($data['status'] == true)
                {
                    return true;
                }
            }
            else
            {
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
                    $stm = $conn->executeUpdate('UPDATE 
                                                  temp_user SET  
                                                            status    = ?,
                                                            ikt_card_no = ? ,
                                                            data = ?,
                                                            country = ? 
                    WHERE ikt_card_no = ?  ', $data_values);



                }
                else
                {


                     $stm = $queryBuilder
                        ->insert('temp_user')
                        ->values(
                            array
                            (
                                'ikt_card_no' => '?',
                                'field' => '?',
                                'data' => '?',
                                'country' => '?',
                                'status' => '?',
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