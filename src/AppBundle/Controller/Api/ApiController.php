<?php
/**
 * Created by PhpStorm.
 * User: abdulali
 * Date: 1/11/17
 * Time: 1:23 PM
 */

namespace AppBundle\Controller\Api;


use AppBundle\AppConstant;

use AppBundle\Entity\User;
use AppBundle\Exceptions\RestServiceFailedException;
use AppBundle\Form\IktRegType;
use AppBundle\HttpCode;

use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;

use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
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
    public function getDo_loginAction(Request $request)
    {
        if(!$this->isRoleApi() || !$this->isRoleUser()) throw new UnauthorizedHttpException("Authorization");
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $view = $this->view(['Value' => $user->getId()], HttpCode::HTTP_OK);
        return $this->handleView($view);
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getTerms_and_conditionsAction(Request $request){

        $view = $this->view(["success"=>true, "Terms"=>$this->get('translator')->trans("These are the test terms and condition services. that will return terms and conditions")], HttpCode::HTTP_OK);

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

            $user = $this->getDoctrine()->getEntityManager()->getRepository('AppBundle:User')->find($post['email']);
            if($user != null){
                return $this->handleView($this->view(["Value"=>"EmailExist"], HttpCode::HTTP_OK));
            }
            $user = $this->getDoctrine()->getEntityManager()->getRepository('AppBundle:User')->find($post['card']);
            if($user != null){
                return $this->handleView($this->view([ "Value"=>"CardExist"], HttpCode::HTTP_OK));
            }

            return $this->handleView($this->view(["Value"=>"New"], HttpCode::HTTP_OK));

        }
        return $this->handleView($this->view([], HttpCode::HTTP_BADE_REQUEST));
    }

    /**
     * @param Request $request
     * @param $mobile
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getSend_activation_codeAction(Request $request, $mobile)
    {
        $code = rand(111111, 999999);
        //return $this->handleView($this->view(["Value"=>$code], HttpCode::HTTP_OK));
        $message = $this->get('translator')->trans("Please insert this temporary code %s , to continue with IKTISSAB Card registration.", ["%s"=>$code]);
        $is_sms_sended = $this->get("app.sms_service")->sendSms($mobile, $message, $request->get("_country") );
        if($is_sms_sended){
            return $this->handleView($this->view(["Value"=>sha1($code . $mobile . md5($code))], HttpCode::HTTP_OK));
        }

        return $this->handleView($this->view(["Value"=>"NotSend"], HttpCode::HTTP_OK));

        //$activityLog = $this->get('app.activity_log');
        //$activityLog->logEvent(AppConstant::ACTIVITY_SEND_SMS, 1, array('message' => $message, 'session' => $data['user']));

    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getSalem_khanAction(Request $request){
        $statuses = array(
            'INVALID_DATA'=>0,
            'PENDING'=>1,
            'SERVICE_CONNECTION'=>2
        );

        if($request->headers->get('ApiResp', 'false') == 'true'){

            return $this->handleView(
                $this->view(array('ResponseTemplate'=>array('success'=>'false/true', 'status'=>'null/0/1/2'), 'status'=>$statuses)));

        }

        return $this->handleView(
            $this->view());
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

        $parameters = $request->request->all();

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

            $birthDay = new \DateTime($parameters['dob']);
            $newCustomer = array(
                "C_id" => $parameters['iktCardNo'],
                "cname" => $parameters['fullName'],
                "street" => $parameters['street'],
                "area" => $parameters['area_no'],
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
                    return $this->handleView($this->view(array('Success'=>false, 'ResponseCode'=>$responseCodes['SERVICE_CONNECTION']), HttpCode::HTTP_OK));
                }
            }
            catch (RestServiceFailedException $e) {
                return $this->handleView($this->view(array('Success'=>false, 'ResponseCode'=>$responseCodes['SERVICE_CONNECTION']), HttpCode::HTTP_OK));
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
            return $this->handleView($this->view(array("Success"=>true, "ResponseCode"=>null), HttpCode::HTTP_OK));
        }

        return $this->handleView($this->view(array("Success"=>false, "ResponseCode"=>$responseCodes['INVALID_DATA']), HttpCode::HTTP_OK));
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
            return $this->handleView($this->view(array('Success'=>false, 'ResponseCode'=>$responseCodes['INVALID_DATA']), HttpCode::HTTP_OK));
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
                    'invalid_message' => 'The password fields must match',
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
            $smsText = $this->get('translator')->trans('Welcome to iktissab website your Username:{username} password:{password}', array('{username}'=>$parameters['email'], '{password}'=>$parameters['password']['first']));
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

            return $this->handleView($this->view(array("Success"=>true, "ResponseCode"=>null), HttpCode::HTTP_OK));
        }

        return $this->handleView($this->view(array("Success"=>false, "ResponseCode"=>$responseCodes['INVALID_DATA']), HttpCode::HTTP_OK));

    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function postUpdate_user_details(Request $request){
        $translator = $this->get('translator');
        $cardService = $this->get('app.services.iktissab_card_service');
        $parameters = $request->request->all();

        //$this->createForm()
    }

}