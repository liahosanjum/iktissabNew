<?php

namespace AppBundle\Controller;

use AppBundle\AppConstant;


use AppBundle\Form\FaqsType;
use AppBundle\Entity\Faqs;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\SecurityBundle\Tests\Functional\Bundle\CsrfFormLoginBundle\Form\UserLoginType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Captcha\Bundle\CaptchaBundle\Form\Type\CaptchaType;
use Captcha\Bundle\CaptchaBundle\Validator\Constraints as CaptchaAssert;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use AppBundle\Entity\User;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Validator\Constraints\Length;


class DefaultController extends Controller
{
    /**
     * @Route("/", name="landingpage")
     */
    public function indexAction(Request $request)
    {
        $cookieLocale = $request->cookies->get(AppConstant::COOKIE_LOCALE);
        $cookieCountry = $request->cookies->get(AppConstant::COOKIE_COUNTRY);
        

        if(isset($cookieLocale) && $cookieLocale <> '' && isset($cookieCountry) && $cookieLocale <> ''){
            return $this->redirect($this->generateUrl('homepage', array('_country'=>$cookieCountry,'_locale'=>$cookieLocale)));
        }

        return $this->render('default/index.html.twig', [
            'base_dir' => realpath(
                    $this->getParameter('kernel.root_dir') . '/..') . DIRECTORY_SEPARATOR,
        ]);
    }
    

    /**
     * @Route("/{_country}/{_locale}/", name="homepage")
     * @param Request $request
     */
    public function homepageAction(Request $request)
    {
        $response = new Response();
        $locale   = $request->getLocale();
        $country  = $request->get('_country');

        $response->headers->setCookie(new Cookie(AppConstant::COOKIE_LOCALE, $locale,time()+AppConstant::COOKIE_EXPIRY,'/',null,false,false));
        $response->headers->setCookie(new Cookie(AppConstant::COOKIE_COUNTRY, $country,time()+AppConstant::COOKIE_EXPIRY,'/',null,false,false));
        $response->sendHeaders();
        //TODO:: add template for home page display

        

    }

    /**
     * @Route("/{_country}/{_locale}/inquiries", name="inquiries")
     * @param Request $request
     */
    public function inquiriesAction(Request $request)
    {
        $response = new Response();
        $locale = $request->getLocale();
        $country = $request->get('_country');
        $form = $this->createFormBuilder(null, ['attr' => ['novalidate' => 'novalidate']])
            ->add('fullName', TextType::class, array('label' => 'Full name',
                    'constraints' => array(
                        new NotBlank(array('message' =>  'This field is required')),
                    )))
            ->add('job', TextType::class, array('label' => 'Job',
                    'constraints' => array(
                        new NotBlank(array('message' =>  'This field is required')),
                    )))

            ->add('mobile', TextType::class, array(
                'label' => 'Mobile',
                'attr' => array('maxlength'=> ($country == 'sa') ? 9 : 14),
                'constraints' => array(
                    new NotBlank(array('message' => 'This field is required')),
                    new Regex(
                        array(
                            'pattern' => ($country == 'sa') ? '/^[5]([0-9]){8}$/' : '/^([0-9]){14}$/',
                            'match' => true,
                            'message' => "Mobile Number Must be ".($country == 'sa' ? '9' : '14' )." digits")
                    ),)))
            ->add('email', TextType::class, array('label' => 'Email',

                    'constraints' => array(
                        new NotBlank(array('message' =>  'This field is required')),
                        new Email(array("message"=> 'Invalid email address'))
                    )))

            ->add('reason', ChoiceType::class, array(
                    'label' => 'Reason',
                    'expanded' => true,
                    'choices' => array('Inquiries' => '1', 'Suggestion' => '2'),
                    'constraints' => array(
                        new NotBlank(array('message' => 'This field is required')),
                    )))

            ->add('email', TextareaType::class, array('label' => 'Details',

                'constraints' => array(
                    new NotBlank(array('message' =>  'This field is required')),

                )))



            ->add('captchaCode', CaptchaType::class, array('constraints' => array(
                new NotBlank(array('message' =>  'This field is required')),
                new CaptchaAssert\ValidCaptcha ( array('message' => 'Invalid captcha code'))),
                     'captchaConfig' => 'FormCaptcha',
                'label' => 'Retype the characters from the picture'
            ))



            ->add($this->get('translator')->trans('Update'), SubmitType::class ,array(
                'attr' => array('class' => 'btn btn-primary'),
            ))

            ->getForm();



        $form->handleRequest($request);
        $posted = array();
        $postData = $request->request->all();
        //print_r($postData);
        //exit;
        if (!empty($postData))
        {
            /***********/
            if ($form->isSubmitted() && $form->isValid() )
            {
                print_r($postData);
                $message = 'Form is submitted';
                
                
                return $this->render('default/inquiries.html.twig',
                    array('form1' => $form->createView(), 'message' => $message));
                
                
            }
        }



        //$response->headers->setCookie(new Cookie(AppConstant::COOKIE_LOCALE, $locale,time()+AppConstant::COOKIE_EXPIRY,'/',null,false,false));
        //$response->headers->setCookie(new Cookie(AppConstant::COOKIE_COUNTRY, $country,time()+AppConstant::COOKIE_EXPIRY,'/',null,false,false));
        //$response->sendHeaders();
        //TODO:: add template for home page display
        $message = '';
        return $this->render('default/inquiries.html.twig',
            array('form1' => $form->createView(), 'message' => $message));



    }



    /**
     * @Route("/{_country}/{_locale}/faqs", name="faqs")
     * @param Request $request
     */
    public function faqsAction(Request $request)
    {
        $response = new Response();
        $faqs     = new Faqs();
        $locale   = $request->getLocale();
        $country  = $this->getCountryCode($request);
        $form     = $this->createForm(FaqsType::class,$faqs);
        $form->handleRequest($request);
        $posted   = array();
        $postData = $request->request->all();

        // print_r($postData);
        // exit;

        if (!empty($postData))
        {
            /***********/
            if ($form->isSubmitted() && $form->isValid() )
            {
                try
                {
                    $data = $this->getEmailList($request, 'Contact Us');
                    if ($data['success']) {
                        $faqs->setCreated(new \DateTime('now'));
                        $faqs->setCountry($country);
                        $faqs->setUser_ip($this->getIP($request));
                        $em = $this->getDoctrine()->getManager();
                        $em->persist($faqs);
                        $em->flush();
                        if($faqs->getId()) {
                            //send email to users
                            $message = \Swift_Message::newInstance();
                            $i = 0;

                            $message->addTo('sa1.aspire@gmail.com');
                            foreach ($data['result'] as $email_list) {
                                $message->addCC($email_list['email']);
                            }
                            $message->addFrom($this->container->getParameter('mailer_user'))
                                ->setSubject(AppConstant::EMAIL_SUBJECT)
                                ->setBody(
                                    $this->container->get('templating')->render(':email-templates/faqs:faqs.html.twig', ['email' => $faqs->getEmail()
                                        , 'mobile' => $faqs->getMobile()
                                        , 'question' => $faqs->getQuestion()
                                    ]),
                                    'text/html'
                                );

                            $this->container->get('mailer')->send($message);
                        }
                        $message = $this->get('translator')->tran('Your request has been submitted');
                        return $this->render('front/faqs.html.twig', array(
                            'form' => $form->createView(), 'message' => $message,
                        ));
                    }
                }
                catch (\Exception $e)
                {
                    $message = $e->getMessage();
                    return $this->render('front/faqs.html.twig', array(
                        'form' => $form->createView(), 'message' => $message,
                    ));

                }

            }
        }

        $message = '';
        return $this->render('front/faqs.html.twig',
            array('form' => $form->createView(), 'message' => $message));
    }



    public function getEmailList(Request $request , $formtype){
        try
        {
            $country_current = $this->getCountryCode($request);
            // defualt category for the FAQS form is others
            // here other is the field name in the database table settings
            // will retrieve all the emal list in the given country with other is equal to 1.
            $enguiry_email_type = 'other';
            $language = $request->getLocale();
            $em = $this->getDoctrine()->getManager();
            $conn = $em->getConnection();
            $stm = $conn->prepare('SELECT * FROM email_setting WHERE country = ? AND type = ? AND ' . $enguiry_email_type . ' = ?  ');
            $stm->bindValue(1, $country_current);
            $stm->bindValue(2, $formtype);
            // here checking the others equal to 1.
            $stm->bindValue(3, 1);
            $stm->execute();
            $result = $stm->fetchAll();
            $data = array(
                'success' => true ,
                'result'  => $result
            );
            return $data;
        }
        catch (\Exception $e)
        {
            $data = array(
                'success' => false ,
                'result'  => $e->getMessage()
            );
            return $data;
        }
    }


   
    /**
     * @Route("/new", name="new")
     */
    public function newAction(Request $request)
    {
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());

        $serializer = new Serializer($normalizers, $encoders);
        echo "i am in the new action";

        $person = array('name' => 'Abdul basit',
            'age' => '44',
            'address' => 'office colony '
        );
        $jsonContent = $serializer->serialize($person, 'json');
        echo $jsonContent;
        return new Response();

    }

    private function getIP(Request $request)
    {
        return $_SERVER['REMOTE_ADDR'];
    }

    private function getCountryCode(Request $request)
    {
        return $country_id = $request->get('_country');
    }

    private function getCountryLocal(Request $request)
    {
        return $locale = $request->getLocale();
    }


    /**
     * @Route("/{_country}/{_locale}/forgotpassword", name="forgotpassword")
     */
    public function forgotPasswordAction(Request $request , $message)
    {
        $form = $this->createFormBuilder(null, ['attr' => ['novalidate' => 'novalidate']])
            ->add('email', EmailType::class, array('label' => 'Email',
                'constraints' => array(
                    new NotBlank(array('message' =>  'This field is required')),
            )))
            ->add('captchaCode', CaptchaType::class, array('constraints' => array(
                new NotBlank(array('message' =>  'This field is required')),
                new CaptchaAssert\ValidCaptcha ( array('message' => 'Invalid captcha code'))),
                'captchaConfig' => 'FormCaptcha',
                'label' => 'Captcha Code'
            ))
            ->add($this->get('translator')->trans('Send Email'), SubmitType::class ,array(
                'attr' => array('class' => 'btn btn-primary'),
            ))
            ->getForm();
        $country_id = $this->getCountryCode($request);
        $form->handleRequest($request);
        $posted = array();
        $postData = $request->request->all();



        if ($form->isSubmitted() && $form->isValid() )
        {
            // 1 get user password according to the email provided
            $em   = $this->getDoctrine()->getManager();
            $conn = $em->getConnection();
            $email   = $form->get('email')->getData();

            $country_id = $this->getCountryCode($request);
            $locale     = $this->getCountryCode($request);

            $stm = $conn->prepare('SELECT * FROM user WHERE country = ? AND email = ?   ');
            $stm->bindValue(1, $country_id);
            $stm->bindValue(2, $email);
            // here checking the others equal to 1.
            $stm->execute();
            $result = $stm->fetchAll();
            if($result)
            {
                // send email
                $email = 'sa.aspire@gmail.com';
                //--> $email = $result[0]['email'];
                //--> print_r($result);
                $user_id = $result[0]['ikt_card_no'];
                //$token = hash('sha256', uniqid(mt_rand(), true), true);
                $time = time();
                $token = uniqid() . md5($email . time() . rand(111111, 999999));
                $link = $this->generateUrl('resetpassword', array('_country' => $country_id ,'_locale' => $locale , 'time' => $time, 'token' => $token), UrlGenerator::ABSOLUTE_URL);
                $data = serialize(array('time' => $time, 'token' => $token));
                $data_values = array(
                    $data,
                    $country_id,
                    $user_id,
                );
                //print_r($data_values);
                $stm = $conn->executeUpdate('UPDATE user  SET  
                                                    data    = ?
                                                    WHERE country = ?  AND ikt_card_no = ? ', $data_values);
                // here checking the others equal to 1.
                //$stm->execute();



                if($stm == 1) {
                    $message = \Swift_Message::newInstance();
                    $request = new Request();
                    $email = 'sa.aspire@gmail.com';
                    $message->addTo($email);
                    $message->addFrom($this->container->getParameter('mailer_user'))
                        ->setSubject(AppConstant::EMAIL_SUBJECT)
                        ->setBody(
                            $this->container->get('templating')->render(':email-templates/forgot_password:forgot_password.html.twig', [
                                'email' => $email,
                                'link' => $link
                            ]),
                            'text/html'
                        );

                    $this->container->get('mailer')->send($message);
                    $this->get('session')->set('passwordrest', 'abc@123456');
                    $response = new Response();

                    $message = $this->get('translator')->trans('Further instructions have been sent to your e-mail address');
                    return $this->render('front/forgotpassword.html.twig', array(
                        'form' => $form->createView(), 'message' => $message,
                    ));

                }

            }
            else
            {
                $message = $this->get('translator')->trans('Sorry , ').$email.$this->get('translator')->trans(' is not recognized as a user name or an e-mail address');
                return $this->render('front/forgotpassword.html.twig', array(
                    'form' => $form->createView(), 'message' => $message,
                ));
            }
        }
        //$message = "";
        return $this->render('front/forgotpassword.html.twig', array(
            'form' => $form->createView(), 'message' => $message,
        ));
    }

    /**
     * @Route("/{_country}/{_locale}/resetpassword/{time}/{token}/", name="resetpassword")
     * @param $time
     * @param $token
     */
    public function resetPasswordAction(Request $request , $time , $token)
    {
        $em    = $this->getDoctrine()->getManager();
        $time  = (integer)$time;
        $dataValue = serialize(array('time' => $time, 'token' => $token));

        $user = $em->getRepository('AppBundle:User')->findOneBy(array("data" => $dataValue));
        $form = $this->createFormBuilder(array('attr' => array('novalidate' => 'novalidate')))
            ->add('password', RepeatedType::class, array(
                'type' => PasswordType::class,
                'constraints' => array(
                    new NotBlank(),
                    new Length(array('minMessage' => 'Password must be at least 8 characters', 'maxMessage' => 'Password must not be greater then 40 characters', 'min' => 8, 'max' => 40))
                ),
                'invalid_message'   => 'Password and confirm password must match',
                'first_options'     => array('label' => 'Enter New Password'),
                'second_options'    => array('label' => 'Confirm Password'),
                'second_name'       => 'confirmPassword'
            ))
            ->add('submit', SubmitType::class, array('label' => 'Reset Password'))
            ->getForm();
        if($user && $user != null) {


            $data = unserialize($user->getData());
            $currentPassword = $user->getPassword();


            if (strtotime('+1 day', $data['time']) > time()) {


                $form->handleRequest($request);
                if ($form->isSubmitted() && $form->isValid()) {

                    $formData = $form->getData();
                    $newPassword = $formData['password'];

                    if ($currentPassword == md5($newPassword)) {
                        $message = $this->get('translator')->trans('New Password and old password must not be the same');
                        return $this->render('front/resetpassword.html.twig', array(
                            'form' => $form->createView(), 'message' => $message,
                        ));
                    } else {
                        $user->setPassword(md5($formData['password']));
                        $user->setData('');
                        $em->persist($user);
                        $em->flush();
                        $message = $this->get('translator')->trans('Your password has been reset successfully');

                        return $this->render('front/resetpassword.html.twig', array(
                            'form' => $form->createView(), 'message' => $message,
                        ));
                    }

                }
                else
                {
                    $message = $this->get('translator')->trans('Reset your password here');
                    return $this->render('front/resetpassword.html.twig', array(
                        'form' => $form->createView(), 'message' => $message,
                    ));
                }

            }
            else
            {

                $message = $this->get('translator')->trans('Link expired');
                return $this->render('front/resetpassword.html.twig', array(
                    'form' => $form->createView(), 'message' => $message,
                ));
            }


        }
        else
        {
            $message = $this->get('translator')->trans('Link expired');
            return $this->render('front/resetpassword.html.twig', array(
                'form' => $form->createView(), 'message' => $message,
            ));
        }
    }



    /**
     * @Route("/{_country}/{_locale}/setpassword", name="setpassword")
     */
    public function setPasswordAction(Request $request)
    {
        $this->get('session')->set('passwordrest', 'abc@123456');
        $response = new Response();
        $response->headers->setCookie(new Cookie(AppConstant::COOKIE_RESET_PASSWORD, 'reset_password',time()+AppConstant::COOKIE_EXPIRY_REST_PASSWORD,'/',null,false,false));
        echo '==='.$cookieResetPassword = $request->cookies->get(AppConstant::COOKIE_RESET_PASSWORD);
        $response->sendHeaders();
        $this->get('session')->get('passwordrest');
        return $this->redirect($this->generateUrl('resetpassword', array('_country'=>'sa','_locale'=>'en')));
    }




}

