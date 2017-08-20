<?php
/**
 * Created by PhpStorm.
 * User: s.aman
 * Date: 1/11/17
 * Time: 1:23 PM
 */

namespace AppBundle\Controller\Api;


use AppBundle\AppBundle;
use AppBundle\AppConstant;

use AppBundle\Entity\NotificationSubscription;

use AppBundle\Entity\NotificationSubscriptionDevices;
use AppBundle\Entity\RejectedUser;
use AppBundle\Entity\User;
use AppBundle\Exceptions\RestServiceFailedException;
use AppBundle\Form\IktRegType;
use AppBundle\Form\IktUpdateType;

use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Controller\FOSRestController;

use Symfony\Component\Config\Definition\Exception\Exception;

use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * Class ApiController
 * @package AppBundle\Controller\Api
 * @RouteResource("User", pluralize=false)
 */
class ApiController extends FOSRestController
{
    private function isRoleApi(){
        $roles = $this->get('security.token_storage')->getToken()->getUser()->getRoles();
        if(in_array('ROLE_API', $roles)) return true;
        return false;
    }
    private function isRoleUser(){
        $roles = $this->get('security.token_storage')->getToken()->getUser()->getRoles();
        if(in_array('ROLE_API_CUSTOMER', $roles)) return true;
        return false;
    }
    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function postDo_loginAction(Request $request)
    {

        $email = $request->request->get('email', '');
        $secret = $request->request->get('secret', '');

        $view = $this->view(['Success'=>false, 'Value' =>''], Response::HTTP_OK);
        if($secret != '' && $email != ''){
            $user = $this->getDoctrine()->getManager()->getRepository('AppBundle:User')->findOneBy(['email'=>$email]);

            if($user){
                //echo md5($user->getPassword() . md5($email)) ." --- " . $secret;die();
                if(md5($user->getPassword() . md5($email)) == $secret){
                    $view = $this->view(['Success'=>true, 'Value' =>$user->getIktCardNo()], Response::HTTP_OK);
                }

            }
            else{
                $view = $this->view(['Success'=>false, 'Value' =>''], Response::HTTP_OK);
            }
        }

        return $this->handleView($view);

    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getTerms_and_conditionsAction(Request $request){

        $view = $this->view(["success"=>true, "Terms"=>$this->get('translator')->trans("These are the test terms and condition services. that will return terms and conditions")], Response::HTTP_OK);

        return $this->handleView($view);

    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function postIs_card_or_email_existAction(Request $request)
    {

        $post = $request->request->all();

        if(key_exists('email', $post) && key_exists('card', $post)){

            $user = $this->getDoctrine()->getManager()->getRepository('AppBundle:User')->findOneBy(['iktCardNo'=>$post['card']]);
            if($user != null){
                return $this->handleView($this->view([ "Value"=>"CardExist"], Response::HTTP_OK));
            }

            $user = $this->getDoctrine()->getManager()->getRepository('AppBundle:User')->findOneBy(['email'=>$post['email']]);
            if($user != null){
                return $this->handleView($this->view(["Value"=>"EmailExist"], Response::HTTP_OK));
            }

            return $this->handleView($this->view(["Value"=>"New"], Response::HTTP_OK));

        }
        return $this->handleView($this->view([], Response::HTTP_BAD_REQUEST));
    }

    /**
     * @param Request $request
     * @param $mobile
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getSend_activation_codeAction(Request $request, $mobile)
    {
        $code = rand(111111, 999999);
        //return $this->handleView($this->view(["Value"=>$code], Response::HTTP_OK));
        $message = $this->get('translator')->trans("Please use this temporary code to continue with Iktissab Card registration: {code}", $code);
        $is_sms_sended = $this->get("app.sms_service")->sendSms($mobile, $message, $request->get("_country") );
        if($is_sms_sended){
            return $this->handleView($this->view(["Value"=>sha1($code . $mobile . md5($code))], Response::HTTP_OK));
        }

        return $this->handleView($this->view(["Value"=>"NotSend"], Response::HTTP_OK));

        //$activityLog = $this->get('app.activity_log');
        //$activityLog->logEvent(AppConstant::ACTIVITY_SEND_SMS, 1, array('message' => $message, 'session' => $data['user']));

    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function postFirst_time_registerationAction(Request $request)
    {
        $responseCodes = array(
            'INVALID_DATA'=>0,
            'PENDING'=>1,
            'SERVICE_CONNECTION'=>2
        );

        if($request->headers->get('ApiResp', 'false') == 'true'){
            return $this->handleView(
                $this->view(array('ResponseTemplate'=>array('Success'=>'false/true', 'ResponseCode'=>'null/0/1/2'), 'ResponseCodes'=>$responseCodes)));
        }


        $translator = $this->get('translator');
        $cardService = $this->get('app.services.iktissab_card_service');

        try{
            $citesAreasAndJobs = $cardService->getCitiesAreasAndJobs();

        }
        catch (RestServiceFailedException $e){
            return $this->handleView($this->view(array('success'=>false, "ResponseCode"=>$responseCodes['SERVICE_CONNECTION'])));
        }

        $citiesArranged = array();
        foreach ($citesAreasAndJobs['cities'] as $key => $value) {
            $citiesArranged[$value['name']] = $value['city_no'];
        }

        $jobsArranged = array();
        foreach ($citesAreasAndJobs['jobs'] as $key => $value) {
            $jobsArranged[$value['name']] = $value['job_no'];
        }

        $areasArranged = array();
        foreach ($citesAreasAndJobs['areas'] as $key => $value) {
            if(!isset($value['name'])){
                continue;
            }
            $areasArranged[$value['name']] = $value['name'];
        }

        $areasArranged['-1'] = '-1';
        $parameters = $request->request->all();

        //changes in parameters
        $parameters['dob_h'] = $parameters['dob'];
        $parameters['dob_h']['year'] = round(($parameters['dob_h']['year'] - 622) * (33 / 32)); //(date('Y') - 2017) + 1438;

        $pData = array('iktCardNo' => $parameters['iktCardNo'], 'email' => $parameters['email']);

        $options = array(
            'additional'=>array(
                'locale' => $request->getLocale(),
                'country' => $request->get('_country'),
                'cities' => $citiesArranged,
                'jobs' => $jobsArranged,
                'areas' => $areasArranged
            ));
        $form = $this->createForm(IktRegType::class, $pData, $options );

        $form->submit($parameters, true);

        if($form->isValid()){

            $birthDay = \DateTime::createFromFormat("Y-m-d", $parameters['dob']["year"]."-".$parameters['dob']["month"]."-".$parameters['dob']["day"]);
            $areaText = ($parameters['area_no'] == "-1")?$parameters["area_text"]:$parameters['area_no'];
            $newCustomer = array(
                "C_id" => $parameters['iktCardNo'],
                "cname" => $parameters['fullName'],
                "area" => $areaText,
                "city_no" => $parameters['city_no'],
                "mobile" => ($request->get("_country") == 'sa' ? '0':'002') . $parameters['mobile'],
                "email" => $parameters['email'],
                "nat_no" => $parameters['nationality'],
                "Marital_status" => $parameters['maritial_status'],
                "ID_no" => $parameters['iqama'],
                "job_no" => $parameters['job_no'],
                "gender" => $parameters['gender'],
                "pur_grp" => $parameters['pur_group'],
                "birthdate" => $birthDay->format('Y-m-d h:i:s'),
                "pincode" => mt_rand(1000, 9999),
                'source'=> User::ACTIVATION_SOURCE_MOBILE
            );

            try {
                $staging = $cardService->isCardInStaging($parameters['iktCardNo']);
                if($staging == true){
                    return $this->handleView($this->view(array('Success'=>false, 'ResponseCode'=>$responseCodes['PENDING'])));
                }
                $saveCustomer = $cardService->saveCard($newCustomer);
                if ($saveCustomer['success'] != true) {
                    return $this->handleView($this->view(array('Success'=>false, 'ResponseCode'=>$responseCodes['SERVICE_CONNECTION']), Response::HTTP_OK));
                }
            }
            catch (RestServiceFailedException $e) {
                return $this->handleView($this->view(array('Success'=>false, 'ResponseCode'=>$responseCodes['SERVICE_CONNECTION']), Response::HTTP_OK));
            }

            $user = new User();
            $user->setEmail($parameters['email']);
            $user->setIktCardNo($parameters['iktCardNo']);
            $user->setRegDate(time());
            $user->setPassword(md5($parameters['password']['first']));
            $user->setActivationSource(User::ACTIVATION_SOURCE_MOBILE);
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            //send sms to user
            $smsText = $this->get('translator')->trans('Welcome to iktissab website your Username:' . $newCustomer['email']);
            $smsService = $this->get('app.sms_service');
            $smsService->sendSms($parameters['mobile'], $smsText, $request->get('_country'));

            //send email to the user
            $emailMessage = \Swift_Message::newInstance();
            $emailMessage->addTo($parameters['email'], $parameters['fullName'])
                ->addFrom($this->getParameter('mailer_user'))
                ->setSubject(AppConstant::EMAIL_SUBJECT)
                ->setBody(
                    $this->renderView(':email-templates/customers:new-account-creation.html.twig', ['customer' => $parameters['fullName'], 'email' => $parameters['email']]),
                    'text/html'
                );
            $this->get('mailer')->send($emailMessage);

            //Save Log
            $log = $this->get('app.activity_log');
            $log->logEvent(
                AppConstant::ACTIVITY_NEW_CARD_REGISTRATION_SUCCESS,
                $parameters['iktCardNo'], ['message' => 'New Card Registeration From Mobile', 'session' => $newCustomer]);
            return $this->handleView($this->view(array("Success"=>true, "ResponseCode"=>null), Response::HTTP_OK));
        }

        return $this->handleView($this->view(array("Success"=>false, "ResponseCode"=>$responseCodes['INVALID_DATA'], "errors"=>$form->getErrors(true)), Response::HTTP_OK));
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function postWebsite_registerationAction(Request $request)
    {
        $responseCodes = array(
            'INVALID_DATA'=>0,
            'PENDING'=>1,
            'SERVICE_CONNECTION'=>2
        );

        if($request->headers->get('ApiResp', 'false') == 'true'){
            return $this->handleView(
                $this->view(array('ResponseTemplate'=>array('Success'=>'false/true', 'ResponseCode'=>'null/0/1/2'), 'ResponseCodes'=>$responseCodes)));
        }

        $parameters = $request->request->all();
        $cardService = $this->get('app.services.iktissab_card_service');

        try{
            $userInformation = $cardService->getUserInfo($parameters['iktCardNo']);
        }
        catch(RestServiceFailedException $ex){
            return $this->handleView($this->view(array('Success'=>false, 'ResponseCode'=>$responseCodes['INVALID_DATA']), Response::HTTP_OK));
        }

        //build form

        $form = $this->createFormBuilder(null, ['method'=>'POST', 'csrf_protection'=>false])
            ->add('iktCardNo', TextType::class, ['label' => 'Iktissab ID', 'attr' =>array('maxlength'=>8),
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Iktissab id  is required']),
                    new Assert\Regex([
                        'pattern' => '/^[9,5]([0-9]){7}$/',
                        'match' => true,
                        'message' => 'Invalid Iktissab Card Number'
                    ])
                ]
            ])
            ->add('email', TextType::class, array('label' => 'Email',
                    'constraints' => [
                        new Assert\NotBlank(['message' => 'This field is required']),
                        new Assert\Email(["message"=>"Invalid email address"])
                    ]
                )
            )
            ->add('password', RepeatedType::class,
                array(
                    'type' => PasswordType::class,
                    'invalid_message' => 'Password fields must match',
                    'required' => true,
                    'first_options' => ['label' => 'Password'],
                    'second_options' => ['label' => 'Repeat password'],
                    'constraints' => [
                        new Assert\NotBlank(['message' => 'This field is required']),
                        new Assert\Length(['min'=> 6, 'minMessage'=> 'Password must be greater then 6 characters'])
                    ]
                )
            )->getForm();

        $form->submit($parameters);

        if($form->isValid()){
            $user = new User();
            $user->setEmail($parameters['email']);
            $user->setIktCardNo($parameters['iktCardNo']);
            $user->setRegDate(time());
            $user->setPassword(md5($parameters['password']['first']));
            $user->setActivationSource(User::ACTIVATION_SOURCE_CALL_CENTER);
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            //send sms to user
            if($request->getLocale() == 'ar'){
                $smsText = $this->get('translator')->trans('اهلاً بك في موقع اكتساب. اسم المستخدم الخاص بك:' . $parameters['email'] .' و كلمة المرور :'.$parameters['password']['first']);
            }
            else {
                $smsText = $this->get('translator')->trans('Welcome to iktissab website your Username:' . $parameters['email'] . 'and Password: '.$parameters['password']['first'] );
            }
            //$smsText = $this->get('translator')->trans('Welcome to iktissab website your Username:{username} password:{password}', array('{username}'=>$parameters['email'], '{password}'=>$parameters['password']['first']));


            $smsService = $this->get('app.sms_service');
            $smsService->sendSms($userInformation['user']['mobile'], $smsText, $request->get('_country'));

            //send email to the user
            $emailMessage = \Swift_Message::newInstance();
            $emailMessage->addTo($parameters['email'], $userInformation['user']['cname'])
                ->addFrom($this->getParameter('mailer_user'))
                ->setSubject(AppConstant::EMAIL_SUBJECT)
                ->setBody(
                    $this->renderView(':email-templates/customers:new-account-creation.html.twig', ['customer' => $userInformation['user']['cname'], 'email' => $parameters['email']]),
                    'text/html'
                );
            $this->get('mailer')->send($emailMessage);

            //Save Log
            $log = $this->get('app.activity_log');
            $log->logEvent(
                AppConstant::ACTIVITY_NEW_CARD_REGISTRATION_SUCCESS,
                $parameters['iktCardNo'], ['message' => 'New Card Registeration From Mobile', 'session' => serialize($userInformation['user'])]);

            return $this->handleView($this->view(array("Success"=>true, "ResponseCode"=>null), Response::HTTP_OK));
        }

        return $this->handleView($this->view(array("Success"=>false, "ResponseCode"=>$responseCodes['INVALID_DATA']), Response::HTTP_OK));

    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function postUpdate_user_detailsAction(Request $request){
        //status saved=1, service connection = 2, validation = 3
        $translator = $this->get('translator');
        $cardService = $this->get('app.services.iktissab_card_service');
        try{
            $citesAreasAndJobs = $cardService->getCitiesAreasAndJobs();
        }
        catch (RestServiceFailedException $e){
            return $this->handleView($this->view(ApiResponse::Response(false, 2, null), Response::HTTP_OK));
        }

        $citiesArranged = array();
        foreach ($citesAreasAndJobs['cities'] as $key => $value) {
            $citiesArranged[$value['name']] = $value['city_no'];
        }

        $jobsArranged = array();
        foreach ($citesAreasAndJobs['jobs'] as $key => $value) {
            $jobsArranged[$value['name']] = $value['job_no'];
        }

        $areasArranged = array();
        foreach ($citesAreasAndJobs['areas'] as $key => $value) {
            if(!isset($value['name'])){
                continue;
            }
            $areasArranged[$value['name']] = $value['name'];
        }

        $parameters = $request->request->all();
        $user = $parameters['user'];
        unset($parameters["user"]);
        $date = \DateTime::createFromFormat("d/m/Y", $user['birthdate']);
        $dob = $date->format(AppConstant::DATE_FORMAT);
        $dataArr = array(
            'date_type' => "g",
            'job_no' => $user['job_no'],
            'maritial_status' => $user['marital_status_en'],
            'language' => $user['lang'],
            'city_no' => $user['city_no'],
            'pur_group' => $user['pur_grp'],
            'dob' => new \DateTime($dob),
            'dob_h' => new \DateTime(),
        );

        $options = array(
            'additional'=>array(
                'locale' => $request->getLocale(),
                'country' => $request->get('_country'),
                'cities' => $citiesArranged,
                'jobs' => $jobsArranged,
                'areas' => $areasArranged
            ));

        $form = $this->createForm(IktUpdateType::class, $dataArr, $options);

        $new_birthday = $parameters['dob'];
        $martial_status_map = array('Single' => 'S', 'Married' => 'M', 'Widow' => 'W', 'Divorce' => 'D');
        $split_dob = explode("/", $parameters['dob']);
        $parameters['dob'] = ["year"=>$split_dob[2], "month"=>$split_dob[1], "day"=>$split_dob[0]];
        $parameters['dob_h'] = ["year"=>"", "month"=>"", "day"=>""];
        $parameters["date_type"] = "g";
        $form->submit($parameters);
        if($form->isValid()){
            $profileFields = array(
                "birthdate" => array('old_value' => $user['birthdate'], 'new_value' => $new_birthday),
                "Marital_status" => array('old_value' => $martial_status_map[$user['marital_status']], 'new_value' => $parameters['maritial_status']),
                "job_no" => array('old_value' => $user['job_no'], 'new_value' => $parameters['job_no']),
                "city_no" => array('old_value' => $user['city_no'], 'new_value' => $parameters['city_no']),
                "area" => array('old_value' => $user['area'], 'new_value' => ($parameters['area_no'] == '-1') ? $parameters['area_text'] : $parameters['area_no']),
                "lang" => array('old_value' => $user['lang'], 'new_value' => ($parameters['language'])),
                "pur_grp" => array('old_value' => $user['pur_grp'], 'new_value' => ($parameters['pur_group'])),
            );
            // now pass only those fields which are changed and are not empty
            $count = 0;
            $form_data = array();
            foreach ($profileFields as $key => $val) {
                if ($val['new_value'] != '' && ($val['new_value'] != $val['old_value'])) {
                    $form_data[$count] = array(
                        'C_id' => $user['C_id'],
                        'field' => $key,
                        'new_value' => $val['new_value'],
                        'old_value' => $val['old_value'],
                        'comments' => ''
                    );
                }
                $count++;
            }
            if(empty($form_data)){
                return $this->handleView($this->view(ApiResponse::Response(true, 3, null), Response::HTTP_OK));
            }
            try {
                $result = $cardService->updateUserDetails($user['C_id'], json_encode($form_data));
                if($result['success'] && $result['status'] == 1){
                    return $this->handleView($this->view(ApiResponse::Response(true, 1, $result["message"]), Response::HTTP_OK));
                }
                else{
                    return $this->handleView($this->view(ApiResponse::Response(true, 3, $result["message"]), Response::HTTP_OK));
                }

            }
            catch (RestServiceFailedException $ex){
                return $this->handleView($this->view(ApiResponse::Response(true, 2, null), Response::HTTP_OK));
            }
            catch (Exception $e) {
                return $this->handleView($this->view(ApiResponse::Response(true, 2, null), Response::HTTP_OK));

            }

        }
        else{
            return $this->handleView($this->view(ApiResponse::Response(true, 3, $form->getErrors(true, true)), Response::HTTP_OK));
        }
    }

    public function postUpdate_mobileAction(Request $request){
        //status 1 = updated, 2=validation error, 3 = connection error
        $cardService = $this->get('app.services.iktissab_card_service');
        $parameters = $request->request->all();
        $view = null;
        try{
            $logData = ["old_value"=>$parameters["old_value"], "new_value"=>$parameters["new_value"]];
            $response = $cardService->updateMobile(json_encode($parameters));
            if($response["success"] && $response["status"] == 1){
                $view = $this->view(ApiResponse::Response(true, 1, null), Response::HTTP_OK);
                $logData["message"] = $response["message"];
                $this->get("app.activity_log")->logEvent(AppConstant::ACTIVITY_UPDATE_MOBILE_SUCCESS, $parameters['C_id'], $logData);
            }
            else{
                $view = $this->view(ApiResponse::Response(false, 2, $response["message"]), Response::HTTP_OK);
                $logData["message"] = $response["message"];
                $this->get("app.activity_log")->logEvent(AppConstant::ACTIVITY_UPDATE_MOBILE_ERROR, $parameters['C_id'], $logData);
            }

        }
        catch (RestServiceFailedException $ex){
            $view = $this->view(ApiResponse::Response(false, 3, null), Response::HTTP_OK);
        }
        catch (Exception $e){
            $view = $this->view(ApiResponse::Response(false, 3, null), Response::HTTP_OK);
        }

        return $this->handleView($view);
    }

    public function postUpdate_ssnAction(Request $request){
        //status 1 = updated, 2=validation error, 3 = connection error
        $cardService = $this->get('app.services.iktissab_card_service');
        $parameters = $request->request->all();
        $view = null;
        try{
            $logData = ["old_value"=>$parameters["old_value"], "new_value"=>$parameters["new_value"]];
            $response = $cardService->updateSSN(json_encode($parameters));
            if($response["success"] && $response["status"] == 1){
                $view = $this->view(ApiResponse::Response(true, 1, null), Response::HTTP_OK);
                $logData["message"] = $response["message"];
                $this->get("app.activity_log")->logEvent(AppConstant::ACTIVITY_UPDATE_IQAMA_SUCCESS, $parameters['C_id'], $logData);
            }
            else{
                $view = $this->view(ApiResponse::Response(false, 2, $response["message"]), Response::HTTP_OK);
                $logData["message"] = $response["message"];
                $this->get("app.activity_log")->logEvent(AppConstant::ACTIVITY_UPDATE_IQAMA_ERROR, $parameters['C_id'], $logData);
            }

        }
        catch (RestServiceFailedException $ex){
            $view = $this->view(ApiResponse::Response(false, 3, null), Response::HTTP_OK);
        }
        catch (Exception $e){
            $view = $this->view(ApiResponse::Response(false, 3, null), Response::HTTP_OK);
        }

        return $this->handleView($view);
    }

    public function postUpdate_nameAction(Request $request){
        //status 1 = updated, 2=validation error, 3 = connection error
        $cardService = $this->get('app.services.iktissab_card_service');
        $parameters = $request->request->all();
        $view = null;
        try{
            $logData = ["old_value"=>$parameters["old_value"], "new_value"=>$parameters["new_value"]];
            $response = $cardService->updateName(json_encode($parameters));
            if($response["success"] && $response["status"] == 1){
                $view = $this->view(ApiResponse::Response(true, 1, null), Response::HTTP_OK);
                $logData["message"] = $response["message"];
                $this->get("app.activity_log")->logEvent(AppConstant::ACTIVITY_UPDATE_FULLNAME_SUCCESS, $parameters['C_id'], $logData);
            }
            else{
                $view = $this->view(ApiResponse::Response(false, 2, $response["message"]), Response::HTTP_OK);
                $logData["message"] = $response["message"];
                $this->get("app.activity_log")->logEvent(AppConstant::ACTIVITY_UPDATE_FULLNAME_ERROR, $parameters['C_id'], $logData);
            }

        }
        catch (AccessDeniedException $ac){
            $view = $this->view(ApiResponse::Response(false, 3, null), Response::HTTP_FORBIDDEN);
        }
        catch (RestServiceFailedException $ex){
            $view = $this->view(ApiResponse::Response(false, 3, null), Response::HTTP_OK);
        }
        catch (Exception $e){
            $view = $this->view(ApiResponse::Response(false, 3, null), Response::HTTP_OK);
        }

        return $this->handleView($view);
    }


    public function postUpdate_emailAction(Request $request){
        //status 1 = updated, 2=validation error, 3 = connection error

        /**
         * @var AppBundle/Entity/User
         */
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $cardService = $this->get('app.services.iktissab_card_service');
        $parameters = $request->request->all();
        $parameters['C_id'] = $user->getIktCardNo();
        $parameters['field'] = 'email';
        $parameters['comment'] = '';
        $view = null;
        try{
            $logData = ["old_value"=>$parameters["old_value"], "new_value"=>$parameters["new_value"]];
            $response = $cardService->updateEmail(json_encode($parameters));
            if($response["success"] && $response["status"] == 1){
                $em = $this->getDoctrine()->getManager();
                $currentUser = $em->getRepository("AppBundle:User")->find($user->getId());
                $currentUser->setEmail($parameters["new_value"]);
                $em->persist($currentUser);
                $em->flush();
                $view = $this->view(ApiResponse::Response(true, 1, null), Response::HTTP_OK);
                $logData["message"] = $response["message"];
                $this->get("app.activity_log")->logEvent(AppConstant::ACTIVITY_UPDATE_EMAIL_SUCCESS, $parameters['C_id'], $logData);
            }
            else{
                $view = $this->view(ApiResponse::Response(false, 2, $response["message"]), Response::HTTP_OK);
                $logData["message"] = $response["message"];
                $this->get("app.activity_log")->logEvent(AppConstant::ACTIVITY_UPDATE_EMAIL_ERROR, $parameters['C_id'], $logData);
            }

        }
        catch (RestServiceFailedException $ex){
            $view = $this->view(ApiResponse::Response(false, 3, null), Response::HTTP_OK);
        }
        catch (Exception $e){
            $view = $this->view(ApiResponse::Response(false, 3, null), Response::HTTP_OK);
        }

        return $this->handleView($view);
    }

    public function postUpdate_passwordAction(Request $request){
        //status 1 = updated, 2=validation error, 3 = connection error
        $em = $this->getDoctrine()->getManager();
        /**
         * @var AppBundle/Entity/User
         */
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $parameters = $request->request->all();
        $view = null;
        try{
            if(md5($parameters['currentPassword']) != $user->getPassword()){
                $view = $this->view(ApiResponse::Response(false, 2, $this->get('translator')->trans("Your current password is not valid")), Response::HTTP_OK);
            }
            elseif(strlen($parameters["password"]) < 6 ){
                $view = $this->view(ApiResponse::Response(false, 2, $this->get('translator')->trans("Password must be at least 6 characters")), Response::HTTP_OK);
            }
            elseif(md5($parameters["password"]) == $user->getPassword() ){
                $view = $this->view(ApiResponse::Response(false, 2, $this->get('translator')->trans("New Password and old password must not be the same")), Response::HTTP_OK);
            }
            else{
                $currentUser = $em->getRepository("AppBundle:User")->find($user->getId());
                $currentUser->setPassword(md5($parameters["password"]));
                $em->persist($currentUser);
                $em->flush();
                $view = $this->view(ApiResponse::Response(true, 1, $this->get('translator')->trans("Password updated successfully")), Response::HTTP_OK);
            }


        }
        catch (Exception $e){
            $view = $this->view(ApiResponse::Response(false, 3, null), Response::HTTP_OK);
        }

        return $this->handleView($view);
    }
    public function postUpdate_lostcardAction(Request $request){
        //status 1 = updated, 2=validation error, 3 = connection error
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $cardService = $this->get('app.services.iktissab_card_service');
        $parameters = $request->request->all();
        $view = null;
        try{
            $logData = ["old_value"=>$parameters["old_value"], "new_value"=>$parameters["new_value"]];
            $response = $cardService->updateLostCard(json_encode($parameters));
            if($response["success"] && $response["status"] == 1){
                $view = $this->view(ApiResponse::Response(true, 1, null), Response::HTTP_OK);
                $logData["message"] = $response["message"];
                $this->get("app.activity_log")->logEvent(AppConstant::ACTIVITY_UPDATE_MISSINGCARD_SUCCESS, $user->getId(), $logData);
            }
            else{
                $view = $this->view(ApiResponse::Response(false, 2, $response["message"]), Response::HTTP_OK);
                $logData["message"] = $response["message"];
                $this->get("app.activity_log")->logEvent(AppConstant::ACTIVITY_UPDATE_MISSINGCARD_ERROR, $user->getId(), $logData);
            }

        }
        catch (RestServiceFailedException $ex){
            $view = $this->view(ApiResponse::Response(false, 3, null), Response::HTTP_OK);
        }
        catch (Exception $e){
            $view = $this->view(ApiResponse::Response(false, 3, null), Response::HTTP_OK);
        }

        return $this->handleView($view);
    }


    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function postPassword_reset_requestAction(Request $request)
    {
        //status 1 = user exist and code is send to mobile, 2 = user not found, 3 = exception occur
        try {
            $parameters = $request->request->all();
            $email = $parameters['email'];
            $em = $this->getDoctrine()->getManager();
            $webUser = $em->getRepository("AppBundle:User")->findOneBy(array("email" => $email));

            if ($webUser != null) {

                $userinfo = $this->get('app.services.iktissab_card_service')->getUserInfo($webUser->getIktCardNo());

                $code = rand(111111, 999999);
                $sh1 = sha1($code . $userinfo['user']['mobile'] . md5($code));
                $this->get('app.sms_service')->sendSms(
                    $userinfo['user']['mobile'],
                    $this->get('translator')->trans("Forgot Password Verification Code %code", array("%code"=>$code))
                    , $request->get("_country"));
                $this->get("app.activity_log")->logEvent(AppConstant::ACTIVITY_FORGOT_PASSWORD_SUCCESS, $webUser->getIktCardNo(), array('session' => serialize($webUser)));

                return $this->handleView(
                    $this->view(ApiResponse::Response(true, 1, $sh1), Response::HTTP_OK)
                );

            }

            return $this->handleView(
                $this->view(ApiResponse::Response(false, 2, null), Response::HTTP_OK)
            );
        }
        catch (Exception $ex1){
            return $this->handleView(
                $this->view(ApiResponse::Response(false, 3, null), Response::HTTP_OK)
            );
        }
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function postReset_passwordAction(Request $request)
    {
        //status 1 = password is changed, 2 = information is not valid, 3 = exception occur
        try {
            $parameters = $request->request->all();
            $email = $parameters["email"];
            $password = $parameters['password'];
            $code = $parameters['code'];
            $secret = $parameters['secret'];

            $em = $this->getDoctrine()->getManager();
            $webUser = $em->getRepository("AppBundle:User")->findOneBy(array("email" => $email));

            if ($webUser != null) {

                $userinfo = $this->get('app.services.iktissab_card_service')->getUserInfo($webUser->getIktCardNo());

                $sh1 = sha1($code . $userinfo['user']['mobile'] . md5($code));
                $md5Password = md5($password);
                if($sh1 == $secret && strlen($password)>=6){
                    $result = $this->get('app.services.iktissab_card_service')->changePassword('{"secret":"'.$md5Password.'"}');
                    if($result['success'] == true && $result['status']==1) {
                        $webUser->setPassword($md5Password);
                        $em->persist($webUser);
                        $em->flush();

                        $this->get("app.activity_log")->logEvent(AppConstant::ACTIVITY_UPDATE_RESETPASSWORD_SUCCESS, $webUser->getIktCardNo(), array('newPassword' => $password, 'session' => serialize($webUser)));
                        return $this->handleView(
                            $this->view(ApiResponse::Response(true, 1, null), Response::HTTP_OK)
                        );
                    }
                    else{
                        return $this->handleView(
                            $this->view(ApiResponse::Response(true, 2, null), Response::HTTP_OK)
                        );
                    }

                }

                return $this->handleView(
                    $this->view(ApiResponse::Response(false, 2, null), Response::HTTP_OK)
                );


            }

            return $this->handleView(
                $this->view(ApiResponse::Response(false, 2, null), Response::HTTP_OK)
            );
        }
        catch (Exception $ex1){
            return $this->handleView(
                $this->view(ApiResponse::Response(false, 3, null), Response::HTTP_OK)
            );
        }
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function postRetrieve_emailAction(Request $request)
    {
        //status 1 = user is validated and email is send to user mobile, 2=given information is not valid, 3 = an arbitrary error occur, 4 = iktissab service is not available
        try{

            $parameters = $request->request->all();

            $card = $parameters["card"] or "";
            $ssn = $parameters["ssn"] or "";

            if($ssn != "" && $card != "") {

                $em = $this->getDoctrine()->getManager();

                $webUser = $em->getRepository("AppBundle:User")->find($card);
                if ($webUser != null) {

                    $service = $this->get("app.services.iktissab_card_service");
                    $userinfo = $service->getUserInfo($card);

                    $iktUser = $userinfo["user"];

                    if ($iktUser["ID_no"] == $ssn) {
                        $smsMessage = $this->get('translator')->trans("Your account registration email is %s", ["%s" => $webUser->getEmail()]);

                        //send sms code
                        $this->get('app.sms_service')->sendSms($iktUser['mobile'], $smsMessage, $request->get('_country'));
                        $this->get('app.activity_log')->logEvent(AppConstant::ACTIVITY_FORGOT_EMAIL_SMS, 1, array('message' => $smsMessage, 'session' => $iktUser));

                        $length = strlen($iktUser["mobile"]);
                        $message = $this->get('translator')->trans('You will receive sms on your mobile number **** %s', ["%s" => substr($iktUser['mobile'], $length-4, $length)]);

                        return $this->handleView(
                            $this->view(ApiResponse::Response(true, 1, $message), Response::HTTP_OK)
                        );

                    }

                }

                return $this->handleView(
                    $this->view(ApiResponse::Response(false, 2, null), Response::HTTP_OK)
                );
            }
            return $this->handleView(
                $this->view(ApiResponse::Response(false, 3, null), Response::HTTP_OK)
            );

        }
        catch (RestServiceFailedException $ex){
            return $this->handleView(
                $this->view(ApiResponse::Response(false, 4, null), Response::HTTP_OK)
            );
        }
        catch (Exception $ex1){
            return $this->handleView(
                $this->view(ApiResponse::Response(false, 3, null), Response::HTTP_OK)
            );
        }

    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function postSubscribeAction(Request $request){

        //{card:132,email:true,sms:false,push:true,device:{deviceName:iphone,deviceUid:21312}}

        $view = null;
        try{

            $parameters = $request->request->all();

            $em = $this->getDoctrine()->getManager();
            $subscription = $em->getRepository("AppBundle:NotificationSubscription")->find($parameters['card']);
            if($subscription == null){
                $subscription = new NotificationSubscription();
                $subscription->setIktCard($parameters['card']);
            }

            $subscription->setEmailSubscription($parameters['email'])
                ->setSmsSubscription($parameters['sms'])
                ->setPushSubscription($parameters['push']);

            $device = null;
            if($parameters['oldDeviceToken'] != '' || $parameters['oldDeviceUID'] != '')
                $device = $em->getRepository("AppBundle:NotificationSubscriptionDevices")->findOneBy(['deviceUID'=>$parameters['oldDeviceUID'], 'deviceToken'=>$parameters['oldDeviceToken']]);

            //delete if iktissab card is not the same
            if($device != null && ($device->getIktCard() != $parameters['card'] || $parameters['push'] != 'y')){
                $em->remove($device);
                $em->flush();
                $device = null;
            }
            else if($device != null && $parameters['push'] == 'y'){
                $device->setDeviceToken($parameters['deviceToken']);
                $device->setDeviceUID($parameters['deviceUID']);
                $em->persist($device);
            }
            else if($device == null && $parameters['push'] == 'y'){
                $device = new NotificationSubscriptionDevices();
                $device->setIktCard($parameters['card'])
                    ->setSerial($em->getRepository("AppBundle:NotificationSubscriptionDevices")->GetNextSerial($parameters['card']))
                    ->setDevice($parameters['device'])
                    ->setDeviceToken($parameters['deviceToken'])
                    ->setDeviceUID($parameters['deviceUID']);
                $em->persist($device);
            }
            $em->persist($subscription);

            $em->flush();

            $view = $this->view(ApiResponse::Response(true, 1, null), Response::HTTP_OK);


        }
        catch (Exception $ex){
            $view = $this->view(ApiResponse::Response(true, 2, null), Response::HTTP_OK);
        }

        return $this->handleView($view);
    }

    /**
     * @param string $device
     * @return Response
     */
    public function getSubscribeAction($device){
        $card = $this->get('security.token_storage')->getToken()->getUser()->getId();
        $data = [];
        $subscription = $this->getDoctrine()->getRepository('AppBundle:NotificationSubscription')->findOneBy(['iktCard'=>$card]);
        if($subscription){
            $subDevice = $this->getDoctrine()->getRepository("AppBundle:NotificationSubscriptionDevices")->findOneBy(['iktCard'=>$card, 'device'=>$device]);
            $data['push'] = $subscription->getPushSubscription();
            $data['sms'] = $subscription->getSmsSubscription();
            $data['email'] = $subscription->getEmailSubscription();
            $data['card'] = $subscription->getIktCard();
            if($subDevice){
                $data['device'] = $device;
                $data['deviceToken'] = $subDevice->getDeviceToken();
            }
        }

        return $this->handleView(
            $this->view(ApiResponse::Response(true, 1, $data), Response::HTTP_OK)
        );

    }

    /**
     * @param $card
     * @param $ssn
     * @return Response
     */
    public function getSendPwdAction($card, $ssn){
        try {


            $service = $this->get('app.services.iktweb_service');
            $result = $service->Get('send_pwd', ['c_id' => $card, 'id_no' => $ssn]);
            if (trim($result) == 'تم إرسال كلمة السر بنجاح The Customer Password is sent successfully') {
                return $this->handleView(
                    $this->view(ApiResponse::Response(true, 1, $this->get('translator')->trans('The Customer Password is sent successfully')), Response::HTTP_OK)
                );
            } else {
                return $this->handleView(
                    $this->view(ApiResponse::Response(true, 2, trim($result)), Response::HTTP_OK)
                );
            }
        }
        catch (Exception $ex){
            return $this->handleView(
                $this->view(ApiResponse::Response(true, 3, null), Response::HTTP_OK)
            );
        }

    }

    /**
     * @param Request $request
     * @return Response
     */
    public function postPushUsersAction(Request $request)
    {
        $parameters = $request->request->all();
        if($parameters == '' or empty($parameters)){
            $parameters = $this->get('serializer.encoder.json')->decode($request->getContent(), 'array');
        }

        if(isset($parameters['users']) and count($parameters['users'])>0){

            $em = $this->getDoctrine()->getManager();
            $repo = $em->getRepository('AppBundle:User');
            $approved = [];
            $rejected = [];
            foreach ($parameters['users'] as $row){
                $card = $repo->find($row['card']);

                if($card != null){
                    if($row['process'] == 3){
                        $rejectedUser = new RejectedUser();
                        $rejectedUser->setIktCardNo($card->getIktCardNo())
                            ->setActivationSource($card->getActivationSource())
                            ->setEmail($card->getEmail())
                            ->setRegDate($card->getRegDate());

                        $rejected[] = $row['card'];
                        $em->remove($card);
                        $em->persist($rejectedUser);
                        $em->flush();
                    }
                    else if($row['process'] == 2){
                        $card->setStatus(1);
                        $em->persist($card);
                        $em->flush();
                        $approved[] = $row['card'];
                    }
                }

            }

            $view = $this->view(ApiResponse::Response(true, 1, ['Approved'=>$approved, "Rejected"=>$rejected]), Response::HTTP_OK);
        }
        else{
            $view = $this->view(ApiResponse::Response(true, 2, 'Nothing found to update'), Response::HTTP_OK);
        }

        return $this->handleView($view);
    }


    /**
     * @param Request $request
     * @return Response
     */
    public function postPullUsersAction(Request $request){
        $parameters = $request->request->all();
        if($parameters == '' or empty($parameters)){
            $parameters = $this->get('serializer.encoder.json')->decode($request->getContent(), 'array');
        }

        if(isset($parameters['users']) and count($parameters['users'])>0){
            $users = $this->getDoctrine()->getRepository('AppBundle:User')->GetUsersByCards($parameters['users']);

            return $this->handleView($this->view(ApiResponse::Response(true, 1, implode(',', $users)), Response::HTTP_OK));
        }

        return $this->handleView($this->view(ApiResponse::Response(true, 2, null), Response::HTTP_OK));
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function postUpdateLostCardsAction(Request $request){
        $parameters = $request->request->all();
        if($parameters == '' or empty($parameters)){
            $parameters = $this->get('serializer.encoder.json')->decode($request->getContent(), 'array');
        }

        if(isset($parameters['lostcards']) and count($parameters['lostcards'])>0){

            $em = $this->getDoctrine()->getManager();
            foreach ($parameters['lostcards'] as $lostcard){
                $user = $em->getRepository('AppBundle:User')->find($lostcard['card']);
                $user->setIktCardNo($lostcard['new_card']);
                $em->persist($user);
            }
            $em->flush();

            return $this->handleView($this->view(ApiResponse::Response(true, 1, 'Update Successful'), Response::HTTP_OK));
        }

        return $this->handleView($this->view(ApiResponse::Response(true, 2, null), Response::HTTP_OK));
    }

    public function getSalemkhanAction(){

        $card = $this->get('app.services.iktissab_card_service');
        $data = $card->isSSNUsed('2326864655');
        return $this->handleView($this->view(ApiResponse::Response(true, 1, $data), Response::HTTP_OK));
    }
}