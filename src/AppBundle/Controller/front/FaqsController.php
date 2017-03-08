<?php

namespace AppBundle\Controller\Front;


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

use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Validator\Constraints\Length;
use Doctrine\ORM\Query\ResultSetMapping;
use AppBundle\Controller\Common\FunctionsController;

class FaqsController extends Controller
{


    /**
     * @Route("/{_country}/{_locale}/faqs", name="faqs")
     * @param Request $request
     */
    public function faqsAction(Request $request)
    {
        try
        {
            $commFunction = new FunctionsController();
            $show_form = true;
            $form = 'Faqs Form';
            echo '--->>>>'.$display_settings = $this->getFormSubmissionSettings($request, $form);

            $faqs = new Faqs();
            $form = $this->createForm(FaqsType::class, $faqs ,array(
                'extras' => array(
                    'country' => $commFunction->getCountryCode($request)
            )));
            $locale   = $request->getLocale();
            $country  = $commFunction->getCountryCode($request);
            $form->handleRequest($request);
            $posted   = array();
            $postData = $request->request->all();
            if(isset($display_settings) && $display_settings != null)
            {
                    /***********/
                    if ($form->isSubmitted() && $form->isValid()) {
                        try {
                            $data = $this->getEmailList($request, 'Faqs Form');
                            if ($data['success']) {
                                $faqs->setCreated(new \DateTime('now'));
                                $faqs->setCountry($country);
                                // saving user ip
                                $faqs->setUser_ip($commFunction->getIP());
                                $em = $this->getDoctrine()->getManager();
                                $em->persist($faqs);
                                $em->flush();
                                if ($faqs->getId()) {
                                    //send email to users
                                    $message = \Swift_Message::newInstance();
                                    $i = 0;
                                    //$message->addTo('sa1.aspire@gmail.com');
                                    foreach ($data['result'] as $email_list) {
                                        if ($i == 0) {
                                            $message->addTo($email_list['email']);
                                            $i++;
                                        } else {
                                            $message->addCC($email_list['email']);
                                        }
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
                                $message = $this->get('translator')->trans('Your request has been submitted');
                                return $this->render('front/faqs.html.twig', array(
                                    'form' => $form->createView(), 'message' => $message,'show_form' => $show_form
                                ));
                            }
                        } catch (\Exception $e) {
                            $message = $e->getMessage();
                            return $this->render('front/faqs.html.twig', array(
                                'form' => $form->createView(), 'message' => $message,'show_form' => $show_form
                            ));
                        }
                    }
                $message = '';
                return $this->render('front/faqs.html.twig', array(
                    'form' => $form->createView(), 'message' => $message,'show_form' => $show_form
                ));
            }
            else
            {
                if($form->isSubmitted())
                {
                    $message = $this->get('translator')->trans('Dear Customer, you have already make submission for this form.');
                }
                else
                {
                    $message = ''; //  0 
                }

                $show_form  = false;
                return $this->render('front/faqs.html.twig',
                    array('form' => $form->createView(), 'message' => $message , 'show_form' => $show_form));
            }
        }
        catch (\Exception $e)
        {
            $message = $e->getMessage();
            return $this->render('front/faqs.html.twig', array(
                'message' => $message,'show_form' => $show_form
            ));
        }
    }



    public function getEmailList(Request $request , $formtype)
    {
        try
        {
            $commFunction = new FunctionsController();
            $country_current = $commFunction->getCountryCode($request);
            // defualt category for the FAQS form is "others"
            // here other is the field name in the database table settings
            // will retrieve all the emal list in the given country with other is equal to 1.
            $enguiry_email_type = 'other';
            $language = $commFunction->getCountryLocal($request);
            $em   = $this->getDoctrine()->getManager();
            $conn = $em->getConnection();
            $stm  = $conn->prepare('SELECT * FROM email_setting WHERE country = ? AND type = ? AND ' . $enguiry_email_type . ' = ?  ');
            $stm->bindValue(1, $country_current);
            $stm->bindValue(2, $formtype);
            // here checking the others equal to 1.
            $stm->bindValue(3, 1);
            $stm->execute();
            $result = $stm->fetchAll();
            if($result) {
                $data = array(
                    'success' => true,
                    'result' => $result
                );
            }
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

    public function getFormSubmissionSettings(Request $request , $form)
    {
        // $form = 'Inquiries And Suggestion';
        // $form    = 'Faqs Form';
        try
        {
            $commFunction    = new FunctionsController();
            $country_id      = $commFunction->getCountryCode($request);
            $formSettingList = $this->getDoctrine()
                ->getRepository('AppBundle:FormSettings')
                ->findOneBy(    array('status' => 1, 'formtype' => $form, 'country' => $country_id));
            // print_r($formSettingList);exit;

            $i = 0;
            if($formSettingList == '' && $formSettingList == null)
            {
                return $formSettingList = false;
            }
            else
            {
                // get number of submission for the user IP
                // if the user has submitted the form
                // 'Every Hour' => '1', 'Every Day' => '24', 'Weekly' => '168', 'Monthly' => '720'
                // This is the time that will add with the user submission time so that we know
                // we can prevent the user from submitting the form agian.
                // now we will add these housr to the sumbission time of the form
                $submission_time_hours_for_checking = $formSettingList->getSubmissions();
                $number_of_entries = $formSettingList->getLimitto();
                if($submission_time_hours_for_checking)
                {
                    date_default_timezone_set("Asia/Riyadh");
                    echo 'current_time'.$date_now = date('Y-m-d H:i:s');

                    $date_current = explode(' ', $date_now );
                    //print_r($date_now);
                    $date_current_days      = explode('-', $date_current[0] );
                    $date_current_year      = $date_current_days[0];
                    $date_current_month     = $date_current_days[1];
                    $date_current_day       = $date_current_days[2];
                    //echo '<br>';
                    $date_current_hours     = explode(':' ,$date_current[1]);
                    $date_current_hour      = $date_current_hours[0];
                    $date_current_minutes   = $date_current_hours[1];
                    $date_current_seconds   = $date_current_hours[2];
                    //echo '<br>';
                    $current_time =  mktime($date_current_hour + 3, $date_current_minutes, $date_current_seconds, $date_current_month, $date_current_day , $date_current_year);
                    //  echo '<br>';
                    //  get_user_ip
                    $user_ip = $commFunction->getIP();
                    //  get the datetime of the form the user has submitted

                    $formSettingList1 = $this->getDoctrine()
                        ->getRepository('AppBundle:Faqs')
                        ->findBy(array('user_ip' => $user_ip, 'country' => $country_id), array('id' => 'DESC'), $number_of_entries );
                    // echo '<br>====>> ';  print_r($formSettingList1);
                    echo '<br>====>><< ';
                    if(isset($formSettingList1) && $formSettingList1 != null)
                    {
                        $i=0;
                        // If the number of submissions are less then number_of_entries then return false
                        //
                        // echo '=======> '.count($formSettingList1). '-----' .$number_of_entries;
                        if(count($formSettingList1) < $number_of_entries){
                            return true;
                        }


                        foreach ($formSettingList1 as $form_setting_list)
                        {
                            //echo '<br>'.'===> >> >>>';
                            if(count($formSettingList1) < $number_of_entries){
                                return true;
                            }
                            echo $date_of_submission = $formSettingList1[$i]->getCreated()->format('Y-m-d H:i:s');
                            //echo '<br>';

                            $date_of_submission = explode(' ', $date_of_submission);
                            //print_r($date_of_submission);
                            $date_of_submission[0];
                            $date_of_submission[1];
                            $date_of_submission_days_array = explode('-', $date_of_submission[0]);
                            //print_r($date_of_submission_days_array);
                            $date_of_sub_year  = $date_of_submission_days_array[0];
                            $date_of_sub_month = $date_of_submission_days_array[1];
                            $date_of_sub_days  = $date_of_submission_days_array[2];
                            $date_of_sub_hours_array = explode(':', $date_of_submission[1]);
                            //print_r($date_of_sub_hours_array);
                            $date_of_sub_hour    = $date_of_sub_hours_array[0];
                            $date_of_sub_minutes = $date_of_sub_hours_array[1];
                            $date_of_sub_seconds = $date_of_sub_hours_array[2];
                            // final mktime of the user submitted form
                            echo 'submission time';
                            echo '<br>'.$submission_time = mktime($date_of_sub_hour + 3 + $submission_time_hours_for_checking, $date_of_sub_minutes, $date_of_sub_seconds, $date_of_sub_month, $date_of_sub_days, $date_of_sub_year);
                            // here we will add the time with the submitted time for the user so that we know he still can submit the form or not
                            // ie afer one hour or after one day the user will submit the form
                            $submission_time;
                            // echo '<br>';
                            echo 'current time' . $current_time;
                            // ct = 9 ; st = 8 ; gt = 2
                            if ($submission_time < $current_time) {
                                return true;
                            }
                            $i++;
                        }
                        // change it to false
                        return false;
                    }else{
                        return true;
                    }
                }
            }
        }
        catch (\Exception $e)
        {
            $message = $e->getMessage(); // exit;
            return $this->render('front/faqs.html.twig', array(
                'form' => $form->createView(), 'message' => $message,
            ));
        }
    }


    public function getFormSubmissionSettingsss(Request $request , $form)
    {
         $form = 'Faqs Form';
         try
         {
                $commFunction    = new FunctionsController();
                $formSettingList = $this->getDoctrine()
                    ->getRepository('AppBundle:FormSettings')
                    ->findOneBy(array('status' => 1, 'formtype' => $form));
                 print_r($formSettingList);
                $country_id = $commFunction->getCountryCode($request);
                $i = 0 ;
                if( $formSettingList == '' && $formSettingList == null )
                {
                    return $formSettingList = false;
                }
                else
                {
                    echo $submission_time_hours_for_checking = $formSettingList->getSubmissions();
                    echo '<br>';
                    if($submission_time_hours_for_checking)
                    {
                        // date_default_timezone_set("Asia/Riyadh");
                        echo $date_now = date('Y-m-d H:i:s');

                        $date_current = explode(' ', $date_now );
                        // print_r($date_now);
                        $date_current_days  = explode('-', $date_current[0] );
                        $date_current_year  = $date_current_days[0];
                        $date_current_month = $date_current_days[1];
                        $date_current_day   = $date_current_days[2];
                        // echo '<br>';


                        $date_current_hours  = explode(':' , $date_current[1]);
                        $date_current_hour   = $date_current_hours[0];
                        $date_current_minutes   = $date_current_hours[1];
                        $date_current_seconds   = $date_current_hours[2];
                        //echo '<br>';

                        $current_time =  mktime($date_current_hour + 3, $date_current_minutes, $date_current_seconds, $date_current_month, $date_current_day , $date_current_year);

                        //  echo '<br>';
                        //  get_user_ip
                        $user_ip = $commFunction->getIP();
                        //  get the datetime of the form the user has submitted
                        $formSettingList1 = $this->getDoctrine()
                            ->getRepository('AppBundle:Faqs')
                            ->findOneBy(array('user_ip' => $user_ip, 'country' => $country_id),array('id' => 'DESC'));
                        echo '<br>';
                        print_r($formSettingList1);
                        if(isset($formSettingList1) && $formSettingList1 != null) {
                            $i=0;
                            echo '<br>===';
                            print_r($formSettingList1);
                            foreach ($formSettingList1 as $form_setting_list)
                            {
                                echo '<br>==='; exit;
                                echo $date_of_submission = $formSettingList1->getCreated()->format('Y-m-d H:i:s');
                                $date_of_submission      = explode(' ', $date_of_submission);
                                //print_r($date_of_submission);
                                $date_of_submission[0];
                                $date_of_submission[1];
                                $date_of_submission_days_array = explode('-', $date_of_submission[0]);
                                //print_r($date_of_submission_days_array);
                                $date_of_sub_year  = $date_of_submission_days_array[0];
                                $date_of_sub_month = $date_of_submission_days_array[1];
                                $date_of_sub_days  = $date_of_submission_days_array[2];
                                $date_of_sub_hours_array = explode(':', $date_of_submission[1]);
                                print_r($date_of_sub_hours_array);
                                $date_of_sub_hour    = $date_of_sub_hours_array[0];
                                $date_of_sub_minutes = $date_of_sub_hours_array[1];
                                $date_of_sub_seconds = $date_of_sub_hours_array[2];
                                // final mktime of the user submitted form
                                $submission_time = mktime($date_of_sub_hour + 3 + $submission_time_hours_for_checking, $date_of_sub_minutes, $date_of_sub_seconds, $date_of_sub_month, $date_of_sub_days, $date_of_sub_year);
                                // here we will add the time with the submitted time for the user so that we know he still can submit the form or not
                                // ie afer one hour or after one day the user will submit the form
                                // $submission_time;
                                // echo '<br>';
                                // echo 'current time' . $current_time;
                                // ct = 9 ; st = 8 ; gt = 2
                                if ($submission_time < $current_time) {
                                    return true;
                                }
                                $i++;
                            }
                            return false;
                        }
                        else{
                            return true;
                        }
                    }
                }
            }
            catch (\Exception $e)
            {

                $message = $e->getMessage();
                echo $message;
                die('--');
                return $this->render('front/faqs.html.twig', array(
                    'form' => $form->createView(), 'message' => $message,
                ));
            }
    }

    /**
     * @Route("/{_country}/{_locale}/faqlist", name="front_faqlist")
     * @param Request $request
     */
    public function getFaqList(Request $request)
    {
        $restClient = $this->get('app.rest_client');
        $url  = $request->getLocale() . '/api/faqlist.json';
        // echo AppConstant::WEBAPI_URL.$url;
        $data = $restClient->restGet(AppConstant::WEBAPI_URL.$url, array('Country-Id' => strtoupper($request->get('_country'))));
        // var_dump($data);$data['data']
        echo 'testing';
        $i = 0;
        $message = '';
        return $this->render( 'front/faqslist.html.twig', array('message' => $message, 'Data' => $data['data'] ));
    }





   /* private function getIP()
    {
        return $_SERVER['REMOTE_ADDR'];
    }

    private function getCountryCode11(Request $request)
    {
        return $country_id = $request->get('_country');
    }

    private function getCountryLocal(Request $request)
    {
        return $locale = $request->getLocale();
    }*/



}
