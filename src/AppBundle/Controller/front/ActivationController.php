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
        if ($form->isSubmitted() && $form->isValid()) {
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


            } catch (Exception $e) {
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
            Throw new Exception($this->get('translator')->trans('You have registered previously. If you have forgot password please click this link.'), 1);
        }
        // Second step to check the  ikt card in mysql db
        $checkIktCard = $em->getRepository('AppBundle:User')->find($pData['iktCardNo']);
        if (!is_null($checkIktCard)) {
            Throw new Exception($this->get('translator')->trans('This Card is already registered.'), 1);
        }

    }

    /**
     * @param $iktCardNo
     * @return bool|string
     */

    public function checkScenerio($iktCardNo)
    {
        $restClient = $this->get('app.rest_client');
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $locale = $request->getLocale();
        $url = $request->getLocale() . '/api/' . $iktCardNo . '/card_status.json';
        $data = $restClient->restGet(AppConstant::WEBAPI_URL . $url, array('Country-Id' => strtoupper($request->get('_country'))));
        if ($data['success'] != true) {
            Throw new Exception($this->get('translator')->trans('Iktissab Card is invalid.'), 1);
//            Throw new Exception($this->get('translator')->trans($data['message']), 1);
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
        if (!$this->isReferalValid('card_activation')) {
          // return $this->redirectToRoute('front_card_activation',array('_locale'=> $request->getLocale(),'_country' => $request->get('_country')));
        }
        $restClient = $this->get('app.rest_client');
        $smsService = $this->get('app.sms_service');
        // get existing user data
        $url = $request->getLocale() . '/api/' . $this->get('session')->get('iktCardNo') . '/userinfo.json';
        $data = $restClient->restGet(AppConstant::WEBAPI_URL . $url, array('Country-Id' => strtoupper($request->get('_country'))));
        if ($data['success'] == "true") {
            $this->get('session')->set('iktUserData', $data['user']);
        }
        $error = array('success' => true);
        $fData = array('iktCardNo' => $this->get('session')->get('iktCardNo'), 'email' => $this->get('session')->get('email'));
        $form = $this->createForm(IktCustomerInfo::class, $fData);

        $form->handleRequest($request);
        $pData = $form->getData();
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->get('session')->set('pass',md5($pData['password']));
                $this->get('session')->set('mobile',$data['user']['mobile']);
                $otp = rand(111111, 999999);
                $this->get('session')->set('otp', $otp);
                $message = $this->get('translator')->trans("Please insert this temporary code %s , to continue with Iktissab Card registration.", ["%s"=>$otp]);
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
            // return $this->redirectToRoute('front_card_activation',array('_locale'=> $request->getLocale(),'_country' => $request->get('_country')));
        }
        // get all cities
        $restClient = $this->get('app.rest_client');
        $smsService = $this->get('app.sms_service');
        $url = $request->getLocale() . '/api/cities_areas_and_jobs.json';
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
                    "mobile" => ($request->get('_country') == 'sa' ? "0" : "0") . $pData['mobile'],
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
                $this->get('session')->set('new_customer', $newCustomer);
                $this->get('session')->set('pass', md5($pData['password']));
                $this->get('session')->set('mobile', $pData['mobile']);

                $otp = rand(111111, 999999);
                $this->get('session')->set('otp', $otp);
                $message = "Please insert this temporary code $otp , to continue with Iktissab website registration.";
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
        $restClient = $this->get('app.rest_client');
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $url = $request->getLocale() . '/api/' . $iqama . '/is_ssn_used.json';
        $data = $restClient->restGet(AppConstant::WEBAPI_URL . $url, array('Country-Id' => strtoupper($request->get('_country'))));
        if ($data['success'] == false) { // this iqama is not registered previously
            return true;
        } else {
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
                        $form->get('otp')->addError(new FormError($this->get('translator')->trans('Invalid Code Please try again')));
                    } else {
                        $user = new User();
                        $user->setEmail($this->get('session')->get('email'));
                        $user->setIktCardNo($this->get('session')->get('iktCardNo'));
                        $user->setRegDate(time());
                        $user->setPassword($this->get('session')->get('pass'));
                        $user->setActivationSource(User::ACTIVATION_SOURCE_CALL_CENTER);
                        $em->persist($user);
                        $em->flush();
                        $request->getSession()
                            ->getFlashBag()
                            ->add('ikt_success', $this->get('translator')->trans('Dear customer, your account has been created you can login now '));

                        $message = $this->get('translator')->trans('Welcome to iktissab website your Username:' . $this->get('session')->get('email'));
                        $smsService->sendSms($this->get('session')->get('mobile'), $message, $request->get('_country'));

                        $activityLog->logEvent(AppConstant::ACTIVITY_EXISTING_CARD_REGISTRATION_SUCCESS, $this->get('session')->get('iktCardNo'), array('message' => $message, 'session' => json_encode($this->get('session')->get('iktUserData'))));
                        $message = \Swift_Message::newInstance();

                        $message->addTo($this->get('session')->get('email'), $this->get('session')->get('email'))
                            ->addFrom($this->getParameter('mailer_user'))
                            ->setSubject(AppConstant::EMAIL_SUBJECT)
                            ->setBody(
                                $this->renderView(':email-templates/customers:new-account-creation.html.twig', ['customer' => $this->get('session')->get('email'), 'email' => $this->get('session')->get('email')]),
                                'text/html'
                            );

                        $this->get('mailer')->send($message);
                        return $this->redirectToRoute('activation_thanks', array('_locale' => $request->getLocale(), '_country' => $request->get('_country')));
                    }
                    break;
                case AppConstant::IKT_REG_SCENERIO_1;
                    if ($form->getData()['otp'] != $this->get('session')->get('otp')) {
                        $form->get('otp')->addError(new FormError($this->get('translator')->trans('Invalid Code Please try again')));
                    } else {
                        $newCustomer = $this->get('session')->get('new_customer');
                        // check in stagging // check in live db
                        try {

                            $this->checckBeforeAdd($newCustomer['C_id']);
                            $this->checkIqamaLocal($newCustomer['ID_no']);
                            $this->add();
                            $message = $this->get('translator')->trans('Welcome to iktissab website your Username:' . $newCustomer['email']);
                            $smsService->sendSms($this->get('session')->get('mobile'), $message, $request->get('_country'));

                            $message = \Swift_Message::newInstance();

                            $message->addTo($newCustomer['email'], $newCustomer['cname'])
                                ->addFrom($this->getParameter('mailer_user'))
                                ->setSubject(AppConstant::EMAIL_SUBJECT)
                                ->setBody(
                                    $this->renderView(':email-templates/customers:new-account-creation.html.twig', ['customer' => $newCustomer['cname'], 'email' => $newCustomer['cname']]),
                                    'text/html'
                                );

                            $this->get('mailer')->send($message);
                            $request->getSession()
                                ->getFlashBag()
                                ->add('ikt_success', $this->get('translator')->trans('Dear customer, your account information has been sent to us succesfully. the Iktissab Team will send you the security code and the activation number to your registered mobile number, also we have sent you an email containing a link to confirm & activate your Iktissab Account'));
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
        $restClient = $this->get('app.rest_client');
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
//        $request = Request::createFromGlobals();
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $restClient = $this->get('app.rest_client');
        $url = $request->getLocale() . '/api/' . $iktCardNo . '/is_in_stagging.json';
        $data = $restClient->restGet(AppConstant::WEBAPI_URL . $url, array('Country-Id' => strtoupper($request->get('_country'))));
        if ($data['success'] == true) // this card is in stagging so dont add again
        {
            Throw New Exception($this->get('translator')->trans($data['message']));
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
//            var_dump($cData);
//            var_dump($url);
//            var_dump($saveCustomer);
//            die('----');
            if ($saveCustomer != true) {
                Throw New Exception($this->get('translator')->trans($saveCustomer['message']));
            }
            $user = new User();
            $user->setEmail($newCustomer['email']);
            $user->setIktCardNo($newCustomer['C_id']);
            $user->setRegDate(time());
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
     * @Route("/testsms")
     */
    function testsmsAction()
    {
        $message = \Swift_Message::newInstance();

        $message->addTo('abdulbasitnawab@gmail.com', 'Abdul')
            ->addFrom('fidakhan007@gmail.com')
            ->setSubject('test email')
            ->setBody(
                $this->renderView(':email-templates/customers:new-account-creation.html.twig', ['customer' => 'cname', 'email' => 'cname']),
                'text/html'
            );

        $mail = $this->get('mailer')->send($message);

        // test via sms as service
        dump($mail);
        dump($message);
//        $smsService = $this->get('app.sms_service');
//        $smsService->sendSms("569858396", 7777, 'sa');
        return new Response();
        die('----');

        // this method will be used from sms service now
        $restClient = $this->get('app.rest_client');
        $payload = "mobile=Othaim&password=0557554443&numbers=966569858396&sender=Iktissab&msg=rrereewelcome&timeSend=0&dateSend=0&applicationType=68&domainName=othaimmarkets.com&msgId=3813&deleteKey=101115&lang=3";
        $url = 'http://www.mobily.ws/api/msgSend.php?' . $payload;
        echo "<br /> payload == " . $payload;
        $sms = $restClient->restGet($url, array());
        var_dump($sms);
    }

}