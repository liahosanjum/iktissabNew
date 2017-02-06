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
    public function postFirst_time_registerationAction(Request $request)
    {

        $translator = $this->get('translator');
        $cardService = $this->get('app.services.iktissab_card_service');
        
        try{
            $citesAreasAndJobs = $cardService->getCitiesAreasAndJobs();
        }
        catch (RestServiceFailedException $e){
            return $this->handleView($this->view(['success'=>false, 'Value'=>$translator->trans('Connection to the service failed try latter')]));
        }


        $citiesArranged = array();
        foreach ($citesAreasAndJobs['cities'] as $key => $value) {
            $citiesArranged[$value['name']] = $value['city_no'];
        }

        $jobsArranged = array();
        foreach ($citesAreasAndJobs['jobs'] as $key => $value) {
            $jobsArranged[$value['name']] = $value['job_no'];
        }

        $parameters = $request->request->all();
        
        $form = $this->getFirsTimeRegistrationForm(['locale' => $request->getLocale(), 'country' => $request->get('_country'),
            'cities' => $citiesArranged, 'jobs' => $jobsArranged, 'areas' => $citiesArranged]);
        $form->submit($parameters, true);

        if($form->isValid()){

            $birthDay = new \DateTime($parameters['dob']);
            $newCustomer = array(
                "C_id" => $parameters['iktCardNo'],
                "cname" => $parameters['fullName'],
                "street" => $parameters['street'],
                "area" => $parameters['area_no'],
                "houseno" => $parameters['houseno'],
                "pobox" => $parameters['pobox'],
                "zip" => $parameters['zip'],
                "city_no" => $parameters['city_no'],
                "tel_home" => $parameters['tel_home'],
                "tel_office" => $parameters['tel_office'],
                "mobile" => '0' . $parameters['mobile'],
                "email" => $parameters['email'],
                "nat_no" => $parameters['nationality'],
                "Marital_status" => $parameters['maritial_status'],
                "ID_no" => $parameters['iqama'],
                "job_no" => $parameters['job_no'],
                "gender" => $parameters['gender'],
                "pur_grp" => $parameters['pur_group'],
                "additional_mobile" => '',
                "G_birthdate" => $birthDay->format('Y-m-d h:i:s'),
                "pincode" => mt_rand(1000, 9999),
            );

            try {
                $staging = $cardService->isCardInStaging($parameters['iktCardNo']);
                if($staging == true){
                    return $this->handleView($this->view(['success'=>false, 'Value'=>$translator->trans('You Have pending request')]));
                }
                $saveCustomer = $cardService->saveCard($newCustomer);
                if ($saveCustomer['success'] != true) {
                    return $this->handleView($this->view(['success'=>false, 'Value'=>$this->get('translator')->trans($saveCustomer['message'])], HttpCode::HTTP_OK));
                }
            }
            catch (RestServiceFailedException $e) {
                return $this->handleView($this->view(['success'=>false, 'Value'=>$translator->trans('Connection to the service failed try latter')]));
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
            return $this->handleView($this->view(["success"=>true, "Value"=>"Registration successful"], HttpCode::HTTP_OK));
        }

        return $this->handleView($this->view(["success"=>false, "Value"=>AppConstant::INVALID_DATA], HttpCode::HTTP_OK));
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function postWebsite_registerationAction(Request $request)
    {
        $parameters = $request->request->all();
        $cardService = $this->get('app.services.iktissab_card_service');
       
        try{
            $userInformation = $cardService->getUserInfo($parameters['iktCardNo']);
        }
        catch(RestServiceFailedException $ex){
            return $this->handleView($this->view(['success'=>false, 'Value'=>$this->get('translator')->trans('Connection to the service failed try latter')]));
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
            return $this->handleView($this->view(["success"=>true, "Value"=>"Registration successful"], HttpCode::HTTP_OK));
        }

        return $this->handleView($this->view(["success"=>false, "Value"=>"Registration unsuccessful"], HttpCode::HTTP_OK));

    }


    /**
     * @param $lookupData
     * @return \Symfony\Component\Form\Form|\Symfony\Component\Form\FormInterface
     */
    private function getFirsTimeRegistrationForm($lookupData){


        $allNations = $this->getDoctrine()->getManager()->getRepository("AppBundle:Nationality")->findAll();

        $nationalityIds = [];
        foreach ($allNations as $nation){
            $nationalityIds[$lookupData['locale'] == 'en'? $nation->getEdesc(): $nation->getAdesc()] = $nation->getId();
        }

        $builder = $this->createFormBuilder(null, ['method'=>'POST', 'csrf_protection'=>false]);

        $builder->add('iktCardNo', TextType::class, ['label' => 'Iktissab ID', 'attr' =>array('maxlength'=>8),
            'constraints' => [
                new Assert\NotBlank(['message' => 'Iktissab id  is required']),
                new Assert\Regex([
                        'pattern' => '/^[9,5]([0-9]){7}$/',
                        'match' => true,
                        'message' => 'Invalid Iktissab Card Number'
                    ])
                ]
            ])
            ->add('fullName', TextType::class, array('label' => 'Full name', 'attr' =>['maxlength' => 50],
                    'constraints' => [
                        new Assert\NotBlank(['message' => 'This field is required']),
                        new Assert\Length(['min' => 5, 'max'=>50, 'minMessage'=> "Length is too small", 'maxMessage' => "Length is too big"])
                    ]
                )
            )
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
            )
            ->add('gender', ChoiceType::class, [
                    'label' => 'Gender',
                    'choices' => ['Gender' => '', 'Male' => 'M', 'Female' => 'F'],
                    'constraints' =>[
                        new Assert\NotBlank(['message' => 'This field is required']),
                        new Assert\Choice(['M', 'F'])
                    ]
                ])
            ->add('nationality', ChoiceType::class, [
                    'choices' => $nationalityIds,
                    'label' => 'Nationality',
                    'empty_data' => null,
                    'placeholder' => 'Select Nationality',
                    'constraints' => [
                        new Assert\NotBlank(['message' => 'This field is required']),
                        new Assert\Choice(array_values($nationalityIds))
                    ]
                ]
            )
            ->add('dob', DateType::class, array(
                'years' => range(date('Y') - 5, date('Y') - 77),
                'widget' => 'single_text',
                'label' => 'Birthdate',
                'format'=>"yyyy-M-d",
                'constraints' => [new Assert\NotBlank(['message' => 'This field is required'])]

            ))
            ->add('maritial_status', ChoiceType::class, array(
                'label' => 'Marital Status',
                'choices' => ['Single' => 'S', 'Married' => 'M', 'Widow' => 'W', 'Divorce' => 'D'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'This field is required']),
                    new Assert\Choice(['S', 'M', 'W', 'D'])
                ]
            ))
            ->add('iqama', TextType::class, array(
                'label' => 'Iqama/SSN Number',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'This field is required']),
                    new Assert\Regex([
                            'pattern' => ($lookupData['country'] == 'sa') ? '/^[1,2]([0-9]){9}$/' : '/^([0-9]){14}$/',
                            'match' => true,
                            'message' => 'Invalid Iqama/SSN Number']
                        ),

//                    new Assert\Callback([
//                        'callback' => [$this, 'validateIqama']
//                    ])
                ]
            ))
            ->add('job_no', ChoiceType::class, array(
                'choices' => $lookupData['jobs'],
                'label' => 'Job',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'This field is required']),
                    new Assert\Choice( array_values($lookupData['jobs']))
                ]
            ))
            ->add('city_no', ChoiceType::class, array(
                'choices' => $lookupData['cities'],
                'label' => 'City',
                'placeholder' => 'Select City',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'This field is required']),
                    new Assert\Choice(array_values($lookupData['cities']))
                ]
            ))
            ->add('area_no', ChoiceType::class, array(
                'choices' => $lookupData['areas'],
                'label' => 'Area',
                'placeholder' => 'Select Area'
            ))
            ->add('language', ChoiceType::class, array(
                    'label' => 'Preffered Language',
                    'choices' => array('Select Language' => '', 'Arabic' => 'A', 'English' => 'E'),
                    'constraints' => [
                        new Assert\NotBlank(['message' => 'This field is required']),
                        new Assert\Choice(['A','E'])
                    ]
                )
            )
            ->add('street', TextType::class, array('label' => 'Street', 'attr' => array('maxlength' => 100)))
            ->add('houseno', TextType::class, array('label' => 'House Number'))
            ->add('pobox', TextType::class, array('label' => 'PO Box'))
            ->add('zip', TextType::class, array('label' => 'Zip Code'))
            ->add('tel_office', TextType::class, array('label' => 'Telephone (Office)'))
            ->add('tel_home', TextType::class, array('label' => 'Telephone (Home)'))
            ->add('mobile', TextType::class, array(
                'label' => 'Mobile',
                'attr' => array('maxlength'=> ($lookupData['country'] == 'sa') ? 9 : 14),
                'constraints' => array(
                    new Assert\NotBlank(array('message' => 'This field is required')),
                    new Assert\Regex(
                        array(
                            'pattern' => ($lookupData['country'] == 'sa') ? '/^[5]([0-9]){8}$/' : '/^([0-9]){14}$/',
                            'match' => true,
                            'message' => "Mobile Number Must be ".($lookupData['country'] == 'sa' ? '9' : '14' )." digits")
                    ),

                )
            ))
            ->add('pur_group', ChoiceType::class, array(
                'label' => 'Shoppers',
                'placeholder' => 'Select Shopper',
                'choices' => array('Husband' => '1', 'Wife' => '2', 'Children' => '3', 'Relative' => '4', 'Applicant' => '5', 'Servent' => '6'),
                'constraints' => array(
                    new Assert\NotBlank(array('message' => 'This field is required')),
                    new Assert\Choice(['1','2', '3', '4', '5', '6'])
                )
            ));

        return $builder->getForm();

    }
}