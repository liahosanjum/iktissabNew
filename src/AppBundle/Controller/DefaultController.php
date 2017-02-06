<?php

namespace AppBundle\Controller;

use AppBundle\AppConstant;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
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
                     'captchaConfig' => 'ContactCaptcha',
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
}

