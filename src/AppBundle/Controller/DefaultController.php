<?php

namespace AppBundle\Controller;

use AppBundle\AppConstant;
use AppBundle\Entity\Subscription;
use AppBundle\Entity\User;
use Captcha\Bundle\CaptchaBundle\Form\Type\CaptchaType;
use Captcha\Bundle\CaptchaBundle\Validator\Constraints as CaptchaAssert;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Doctrine\ORM\Query\ResultSetMapping;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;


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
        $locale = $request->getLocale();
        $country = $request->get('_country');

        $response->headers->setCookie(new Cookie(AppConstant::COOKIE_LOCALE, $locale, time() + AppConstant::COOKIE_EXPIRY, '/', null, false, false));
        $response->headers->setCookie(new Cookie(AppConstant::COOKIE_COUNTRY, $country, time() + AppConstant::COOKIE_EXPIRY, '/', null, false, false));
        $response->sendHeaders();
        //TODO:: add template for home page display
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


    /**
     * @Route("/{_country}/{_locale}/list/{id}/{type}", name="front_cmscontent")
     * @param Request $request
     * @param $id
     * @param $type
     */
    public function getCmsContent(Request $request , $id , $type )
    {
        echo 'asdf';
        $em = $this->getDoctrine()->getEntityManager();
        $cmspages = $this->getDoctrine()
            ->getRepository('AppBundle:CmsPages')
            ->findOneBy(array('id' => $id , 'status' => 1 , 'type' => 'CMS'));
        $data = array();
        $i = 0;
        echo $cmspages->getId();
        echo $cmspages->getAtitle();
        echo $cmspages->getEtitle();
        //$cmspages->getgetStatus();

        if (!$cmspages)
        {
            return $this->render('front/cms/contents.html.twig', array(
                'data' => $data , 'message' => 'No record found' ));
        }
        else
        {
            return $this->render('front/cms/contents.html.twig', array(
                'data' => $data ,  'message' => '' , 'Data' => array(
                    'id' => $cmspages->getId() ,'Atitle' => $cmspages->getAtitle() ,'Etitle' => $cmspages->getEtitle())));
        }
    }


    /**
     * @Route("/{_country}/{_locale}/forgotpassword", name="forgotpassword")
     */
    public function forgotPasswordAction(Request $request, $message)
    {
        $form = $this->createFormBuilder(null, ['attr' => ['novalidate' => 'novalidate']])
            ->add('email', EmailType::class, array('label' => 'E-mail address',
                'constraints' => array(
                    new NotBlank(array('message' => 'This field is required')),
                )))
            ->add('captchaCode', CaptchaType::class, array('constraints' => array(
                new NotBlank(array('message' => 'This field is required')),
                new CaptchaAssert\ValidCaptcha (array('message' => 'Invalid captcha code'))),
                'captchaConfig' => 'FormCaptcha',
                'label' => 'Captcha Code'
            ))
            ->add('forgot_password', SubmitType::class, array(
                'attr' => array('class' => 'btn btn-primary', 'id' => 'forgot_password'),
                'label' => $this->get('translator')->trans('E-mail new password'),
            ))->getForm();
        $activityLog = $this->get('app.activity_log');
        $form->handleRequest($request);
        // $posted = array();
        // $postData = $request->request->all();
        if ($form->isSubmitted() && $form->isValid()) {
            // 1 get user password according to the email provided
            $em = $this->getDoctrine()->getManager();
            $conn = $em->getConnection();
            echo "====> " . $email = $form->get('email')->getData();

            $country_id = $this->getCountryCode($request);
            $locale = $this->getCountryLocal($request);

            $stm = $conn->prepare('SELECT * FROM user WHERE country = ? AND email = ?   ');
            $stm->bindValue(1, $country_id);
            $stm->bindValue(2, $email);
            // here checking the others equal to 1.
            $stm->execute();
            $result = $stm->fetchAll();
            if ($result) {
                // send email
                $email = $email; //'sa.aspire@gmail.com';
                //--> $email = $result[0]['email'];
                //--> print_r($result);
                $user_id = $result[0]['ikt_card_no'];
                $user_id_en = $this->encrypt($user_id, AppConstant::SECRET_KEY_FP);
                $time = time();
                $token = uniqid() . md5($email . time() . rand(111111, 999999));
                echo $link = $this->generateUrl('resetpassword', array('_country' => $country_id, '_locale' => $locale, 'time' => $time, 'token' => $token, 'id' => $user_id_en), UrlGenerator::ABSOLUTE_URL);
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
                if ($stm == 1) {
                    $message = \Swift_Message::newInstance();
                    $request = new Request();
                    //--> $email   = 'sa.aspire@gmail.com';
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

                    if ($this->container->get('mailer')->send($message)) {
                        $message = $this->get('translator')->trans('Further instructions have been sent to your e-mail address');
                        $activityLog->logEvent(AppConstant::ACTIVITY_FORGOT_PASSWORD_SUCCESS, $user_id, array('iktissab_card_no' => $user_id, 'message' => $message, 'session' => serialize($result)));

                        return $this->render('front/forgotpassword.html.twig', array(
                            'form' => $form->createView(), 'message' => $message,
                        ));
                    } else {
                        $message = $this->get('translator')->trans('Email has not been sent');
                        $activityLog->logEvent(AppConstant::ACTIVITY_FORGOT_PASSWORD_ERROR, $user_id, array('iktissab_card_no' => $user_id, 'message' => $message, 'session' => $result));

                        return $this->render('front/forgotpassword.html.twig', array(
                            'form' => $form->createView(), 'message' => $message,
                        ));
                    }

                }
            } else {
                $message = $this->get('translator')->trans('Sorry , ') . $email . $this->get('translator')->trans(' is not recognized as a user name or an e-mail address');
                $activityLog->logEvent(AppConstant::ACTIVITY_FORGOT_PASSWORD_ERROR, 'unknownuser ' . $email, array('iktissab_card_no' => 'unknownuser ' . $email, 'message' => $message, 'session' => $result));

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

    private function getCountryCode(Request $request)
    {
        return $country_id = $request->get('_country');
    }

    private function getCountryLocal(Request $request)
    {
        return $locale = $request->getLocale();
    }

    public function encrypt($data, $key)
    {
        return base64_encode(
            mcrypt_encrypt(MCRYPT_RIJNDAEL_128,
                $key,
                $data,
                MCRYPT_MODE_CBC,
                "\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0"
            )
        );
    }

    /**
     * @Route("/{_country}/{_locale}/resetpassword/{time}/{token}/{id}", name="resetpassword")
     * @param $time
     * @param $token
     */

    public function resetPasswordAction(Request $request, $time, $token, $id)
    {
        $activityLog = $this->get('app.activity_log');
        $em = $this->getDoctrine()->getManager();
        $time = (integer)$time;
        $dataValue = serialize(array('time' => $time, 'token' => $token));
        $id    = $this->decrypt($id, AppConstant::SECRET_KEY_FP);
        $user  = $em->getRepository('AppBundle:User')->findOneBy(array("data" => $dataValue , 'iktCardNo' => $id));
        // print_r($user);
        if(isset($user) && $user != null) {
            $user_email = $user->getEmail();
        } else {
            $user_email = '';
        }

        $form = $this->createFormBuilder(array('attr' => array('novalidate' => 'novalidate')))
            ->add('email', EmailType::class, array('label' => 'E-mail address', 'disabled' => true,
                'constraints' => array(
                    new NotBlank(array('message' => 'This field is required')),
                )))
            ->add('password', RepeatedType::class, array(
                'type' => PasswordType::class,
                'constraints' => array(
                    new NotBlank(),
                    new Length(array('minMessage' => 'Password must be at least 8 characters', 'maxMessage' => 'Password must not be greater then 40 characters', 'min' => 8, 'max' => 40))
                ),
                'invalid_message' => 'Password and confirm password must match',
                'first_options' => array('label' => 'Enter New Password'),
                'second_options' => array('label' => "Confirm Password"),
                'second_name' => 'confirmPassword'
            ))
            ->add('submit', SubmitType::class, array('label' => 'Reset Password'))
            ->getForm();
        $form->get('email')->setData($user_email);
        // echo $data['time'] ;
        // echo '<br>';
        // echo time();
        $data_form['show_form'] = 1;
        if ($user && $user != null) {
            $data = unserialize($user->getData());
            $currentPassword = $user->getPassword();
            if (strtotime('+1 day', $data['time']) > time()) {
                $form->handleRequest($request);
                if ($form->isSubmitted() && $form->isValid()) {
                    $formData = $form->getData();
                    $newPassword = $formData['password'];
                    if ($currentPassword == md5($newPassword)) {
                        $message = $this->get('translator')->trans('New Password and old password must not be the same');
                        $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_RESETPASSWORD_ERROR, $id, array('iktissab_card_no' => $id, 'message' => $message, 'session' => $user));

                        return $this->render('front/resetpassword.html.twig', array(
                            'form' => $form->createView(), 'message' => $message, 'data' => $data_form
                        ));
                    } else {
                        $user->setPassword(md5($formData['password']));
                        $user->setData('');
                        $em->persist($user);
                        $em->flush();
                        $message = $this->get('translator')->trans('Your password has been reset successfully');
                        $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_RESETPASSWORD_SUCCESS, $id, array('iktissab_card_no' => $id, 'message' => $message, 'session' => $user));
                        return $this->render('front/resetpassword.html.twig', array(
                            'form' => $form->createView(), 'message' => $message, 'data' => $data_form
                        ));
                    }
                } else {
                    $message = $this->get('translator')->trans('Please reset your password');
                    return $this->render('front/resetpassword.html.twig', array(
                        'form' => $form->createView(), 'message' => $message, 'data' => $data_form
                    ));
                }
            } else {
                $message = $this->get('translator')->trans('Your link to reset password has been expired');
                $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_RESETPASSWORD_ERROR, $id, array('iktissab_card_no' => $id, 'message' => $message, 'session' => $user));
                return $this->render('front/resetpassword.html.twig', array(
                    'form' => $form->createView(), 'message' => $message, 'data' => $data_form
                ));
            }
        } else {
            $message = $this->get('translator')->trans('Your link to reset password has been expired');
            $data_form['show_form'] = 0;
            $activityLog->logEvent(AppConstant::ACTIVITY_UPDATE_RESETPASSWORD_ERROR, $id, array('iktissab_card_no' => $id, 'message' => $message, 'session' => $user));
            return $this->render('front/resetpassword.html.twig', array(
                'form' => $form->createView(), 'message' => $message, 'data' => $data_form
            ));
        }
    }

    public function decrypt($data, $key)
    {
        $decode = base64_decode($data);
        return mcrypt_decrypt(
            MCRYPT_RIJNDAEL_128,
            $key,
            $decode,
            MCRYPT_MODE_CBC,
            "\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0"
        );
    }

    /**
     * @Route("/{_country}/{_locale}/setpassword", name="setpassword")
     */
    public function setPasswordAction(Request $request)
    {
        //$this->get('session')->set('passwordrest', 'abc@123456');
        //$response = new Response();
        //$response->headers->setCookie(new Cookie(AppConstant::COOKIE_RESET_PASSWORD, 'reset_password',time()+AppConstant::COOKIE_EXPIRY_REST_PASSWORD,'/',null,false,false));
        //echo '==='.$cookieResetPassword = $request->cookies->get(AppConstant::COOKIE_RESET_PASSWORD);
        //$response->sendHeaders();
        //$this->get('session')->get('passwordrest');
        //return $this->redirect($this->generateUrl('resetpassword', array('_country'=>'sa','_locale'=>'en')));
    }

    /**
     * @Route("/{_country}/{_locale}/checkFormsOption", name="checkFormsOption")
     * @param $time
     */

    public function checkFormsOptionAction(Request $request, $form)
    {
        $current_time = date('Y-m-d');
        $days_difference = 1;


        $em = $this->getDoctrine()->getEntityManager();
        $formsettingsList = $this->getDoctrine()
            ->getRepository('AppBundle:FormSettings')
            ->findOneBy(array('status' => 1, 'formtype' => 'Contact Us'));
        print_r($formsettingsList);
        echo '<br>';
        echo $formsettingsList->getId();
        $data = array();
        $i = 0;
        $time = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
        echo '<br>';
        $timeN = mktime(0, 0, 0, date('m'), date('d') + $days_difference, date('Y'));


        $status_from_db = true;
        if ($time > $timeN && $status_from_db == true) {
            return true;
        } else {
            return false;

        }
    }

    /**
     * @Route("/{_country}/{_locale}/getDataa", name="getDataa")
     * @param $time
     */


    public function getDataaAction()
    {
        $em = $this->getDoctrine()->getManager('default');
        $this->getDoctrine()->getManager();
        // $conn = $em->getConnection();
        // $em = $this->getDoctrine()->getEntityManager();
        $rsm = new ResultSetMapping();
        //$rsm->addScalarResult('name', 'name');
        $rsm->addScalarResult('name', 'name');
        // $em->createNativeQuery(, $rsm)
        $query = $em->createNativeQuery('SELECT * from test', $rsm);
        echo $query->getSQL();
        //print_r($query);
        $users = $query->getResult();
        print_r($users);
        // $this->get('session')->set('passwordrest', 'abc@123456');
        // $response = new Response();
        // $response->headers->setCookie(new Cookie(AppConstant::COOKIE_RESET_PASSWORD, 'reset_password',time()+AppConstant::COOKIE_EXPIRY_REST_PASSWORD,'/',null,false,false));
        // echo '==='.$cookieResetPassword = $request->cookies->get(AppConstant::COOKIE_RESET_PASSWORD);
        // $response->sendHeaders();
        // $this->get('session')->get('passwordrest');
        // return $this->redirect($this->generateUrl('resetpassword', array('_country'=>'sa','_locale'=>'en')));
    }
    /**
     * @Route("{_country}/{_locale}/subscription/", name="subscription")
     * @param Request $request
     * @return Response
     */
    public function subscriptionAction(Request $request)
    {
        $country = $request->get('_country');
        $em = $this->getDoctrine()->getEntityManager();
        $formEmail = $this->createFormBuilder(null, ['attr' => ['novalidate' => 'novalidate']])
            ->add('email', EmailType::class, array('label' => 'Email Id', 'constraints' => array(

                    new Assert\NotBlank(array('message' => 'Email is required')),
                    new Assert\Email(array('message' => 'Invalid email'))

                ))
            )
            ->add('submit', SubmitType::class)
            ->getForm();
// form for mobile subscription
        $formMobile = $this->get('form.factory')->createNamedBuilder('subs_mob','Symfony\Component\Form\Extension\Core\Type\FormType',null, ['attr' => ['novalidate' => 'novalidate']])
            ->add('mobile', TextType::class, array(
                'label' => 'Mobile',
                'attr' => array('maxlength'=> ($request->get('country') == 'sa') ? 9 : 14),
                'constraints' => array(
                    new Assert\NotBlank(array('message' => 'This field is required')),
                    new Assert\Regex(
                        array(
                            'pattern' => ($country == 'sa') ? '/^[5]([0-9]){8}$/' : '/^([0-9]){14}$/',
                            'match' => true,
                            'message' => "Mobile Number Must be ".($country == 'sa' ? '9' : '14' )." digits")
                    ),

                )
            ))
            ->add('submit', SubmitType::class)
            ->getForm();

        $formEmail->handleRequest($request);
        if ($formEmail->isSubmitted()) {
            if (!$formEmail->isValid()) {
                //throw error
                $errormsg = '';
                foreach ($formEmail->getErrors(true, true) as $key => $value) {
                    $errormsg .= $value->getMessage();
                }
                $return = array('error' => true, 'message' => $errormsg);
            }

            if ($formEmail->isValid()) {
                $formEmailData = $formEmail->getData();
                $subscription = new Subscription();
                // check if already exist
                $emailExist = $em->getRepository('AppBundle:Subscription')->findOneBy(array('subsVal' => $formEmailData['email'], 'subsType' => 'email'));
                if (!$emailExist) {
                    $subscription->setSubsVal($formEmailData['email']);
                    $subscription->setSubsType('email');
                    $em->persist($subscription);
                    $em->flush();
                    $return = array('error' => false, 'message' => $this->get('translator')->trans('Thankyou'));

                } else {
                    $return = array('error' => true, 'message' => $this->get('translator')->trans('You have already subscribed'));
                }

            }
            echo json_encode($return);
            die();
        }
        // handle request for mobile subscription
        $formMobile->handleRequest($request);
        if ($formMobile->isSubmitted()) {
            if (!$formMobile->isValid()) {
                //throw error
                $errormsg = '';
                foreach ($formMobile->getErrors(true, true) as $key => $value) {
                    $errormsg .= $value->getMessage();
                }
                $return = array('error' => true, 'message' => $errormsg);
            }

            if ($formMobile->isValid()) {
                $formMobData = $formMobile->getData();
                $subscription = new Subscription();
                // check if already exist
                $mobileExist = $em->getRepository('AppBundle:Subscription')->findOneBy(array('subsVal' => $formMobData['mobile'], 'subsType' => 'mobile'));
                if (!$mobileExist) {
                    $subscription->setSubsVal($formMobData['mobile']);
                    $subscription->setSubsType('mobile');
                    $em->persist($subscription);
                    $em->flush();
                    $return = array('error' => false, 'message' =>  $this->get('translator')->trans('Thankyou'));

                } else {
                    $return = array('error' => true, 'message' => $this->get('translator')->trans('You have already subscribed'));
                }
            }
            echo json_encode($return);
            die();
        }
        return $this->render('/default/subscription.html.twig',
            array(
                'form' => $formEmail->createView(),
                'form_mob' => $formMobile->createView(),
                'country' => $country
            )
        );
    }
   




    private function getIP()
    {
        return $_SERVER['REMOTE_ADDR'];
    }

    /**
     * @Route("{_country}/{_locale}/promotions/", name="promotions")
     * @Route("{_country}/{_locale}/promotions/{id}", name="promotions_id")
     */
    public function promotionsAction(Request $request)
    {

        $restClient = $this->get('app.rest_client');
        if($request->get('_country') == 'eg'){
            $promoUrl = AppConstant::PROMOTIONS_PATH_EG;
        }else {
            $promoUrl = AppConstant::PROMOTIONS_PATH;
        }
        
        $promotions = $restClient->restGet($promoUrl);
        $promotions = json_decode($promotions,true);
        $enabledPromo = array();
        foreach ($promotions as $promo) {
            if ($promo['is_active'] == 1)
                $enabledPromo[] = $promo['id'];
        }
        if (in_array($request->get('id'), $enabledPromo)) {
            $id = $request->get('id');
        } else {
            $id = $enabledPromo[0];
        }
        return $this->render('/default/promotions.html.twig',
            array(
                'promotions' => $promotions,
                'id' => $id
            )
        );
    }

}

