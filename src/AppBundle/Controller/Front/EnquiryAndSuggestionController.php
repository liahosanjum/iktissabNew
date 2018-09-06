<?php

namespace AppBundle\Controller\Front;

use AppBundle\Controller\Common\FunctionsController;
use AppBundle\Controller\Front\CaptchaController;
use AppBundle\Entity\EnquiryAndSuggestion;
use AppBundle\Form\EnquiryAndSuggestionType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\AppConstant;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\RedirectResponse;

class EnquiryAndSuggestionController extends Controller
{
    /**
     * @Route("/{_country}/{_locale}/enquiry-and-suggestion", name="front_enquiry_and_suggestion_add")
     * @param Request $request
     * @return Response
     */
    public function addAction(Request $request)
    {
        $response = new Response();
        $activityLog  = $this->get('app.activity_log');
        $comFunction = new FunctionsController();
        $comFunction->setContainer($this->container);
        if($comFunction->checkSessionCookies($request) == false){
            return $this->redirect($this->generateUrl('landingpage'));
        }

        $userLang = '';
        $locale = $request->getLocale();
        if($request->query->get('lang')) {
            $userLang = trim($request->query->get('lang'));
        }
        if ($userLang != '' && $userLang != null) {
            // we will only modify cookies if the both the params are same for the langauge
            // this means that the query is modified from the change language link
            if($userLang == $locale)
            {
                $comFunction->changeLanguage($request, $userLang);
                $locale = $request->getLocale();
            }
            else
            {
                if($request->cookies->get(AppConstant::COOKIE_LOCALE)){
                    return $this->redirect($this->generateUrl('homepage', array('_country' => $request->cookies->get(AppConstant::COOKIE_COUNTRY), '_locale' => $request->cookies->get(AppConstant::COOKIE_LOCALE))));
                }
            }
        }
        $userCountry = '';
        if($request->query->get('ccid')) { $userCountry = $request->query->get('ccid'); }
        $country = $request->get('_country');
        if ($userCountry != '' && $userCountry != null) {
            if($userCountry == $country) {
                $comFunction->changeCountry($request, $userCountry);
                $country = $request->get('_country');}
            else {
                if($request->cookies->get(AppConstant::COOKIE_COUNTRY)) {
                    return $this->redirect($this->generateUrl('homepage', array('_country' => $request->cookies->get(AppConstant::COOKIE_COUNTRY), '_locale' => $request->cookies->get(AppConstant::COOKIE_LOCALE))));
                }
            }
        }
        if($request->cookies->get(AppConstant::COOKIE_LOCALE))
        {
            $cookieLocale  = $request->cookies->get(AppConstant::COOKIE_LOCALE);
            $cookieCountry = $request->cookies->get(AppConstant::COOKIE_COUNTRY);
            if (isset($cookieLocale) && $cookieLocale <> '' && $cookieLocale != $locale) {
                return $this->redirect($this->generateUrl('homepage', array('_country' => $cookieCountry, '_locale' => $cookieLocale)));
            }
            if(isset($cookieCountry) && $cookieCountry <> '' && $cookieCountry != $country) {
                return $this->redirect($this->generateUrl('homepage', array('_country' => $cookieCountry, '_locale' => $cookieLocale)));
            }
        }
        $enquiryAndSuggestion = new EnquiryAndSuggestion();
        $form = $this->createForm(EnquiryAndSuggestionType::class, $enquiryAndSuggestion, array('extras' => array('country' => $comFunction->getCountryCode($request))));
        $display_settings = $this->getFormSubmissionSettings($request, 'Inquiries And Suggestion');
        $show_form = true;
        $form->handleRequest($request);
        /*****************/
        $captcha      = new FunctionsController();
        $response      = new Response();
        $captcha->setContainer($this->container);
        $form->get('captchaCode')->getData();
        $this->get('session')->get('_CAPTCHA');
        /*****************/
        if(isset($display_settings) && $display_settings != null) {
            if ($form->isValid() && $form->isSubmitted())
            {
                $captchaCode           = trim($this->get('session')->get('_CAPTCHA'));
                $captchaCodeSubmitted  = trim($form->get('captchaCode')->getData());
                if($captchaCodeSubmitted != $captchaCode)
                {
                    $config        = array();
                    $filename      = $captcha->saveTextAsImage($config);
                    $comFunction->setCsrfToken('front_inq_suggestion');
                    $session = new Session();
                    $token = $session->get('front_inq_suggestion');

                    $response->setContent($filename['filename']);
                    $captcha_image = $filename['image_captcha'];
                    $error_cl      = 'alert-danger';
                    return $this->render('front/enquiry/add-enquiry-and-suggestion.html.twig',
                        array('form' => $form->createView(),'message' => $this->get('translator')->trans('Invalid captcha code'),
                            'show_form' => $show_form ,
                            'data' => $captcha_image , 'error_cl'=> $error_cl , 'token' => $token));
                }
                try
                {
                    if($comFunction->checkCsrfToken($form->get('token')->getData(), 'front_inq_suggestion' ))
                    {
                        $name = $form->get('name')->getData();
                        $email = $form->get('email')->getData();
                        $job = $form->get('job')->getData();
                        $reason = $form->get('reason')->getData();
                        $mobile = $form->get('mobile')->getData();
                        $comments = $form->get('comments')->getData();
                        $captchaCode = $form->get('captchaCode')->getData();
                        $errors = "";
                        $validate_data = array($name ,$email,$job,$reason,$mobile,$comments,$captchaCode);
                        $errors = $comFunction->validateData($validate_data);
                        if($errors > 0)
                        {
                            $message = $this->get('translator')->trans('Please provide valid data');
                            $errorcl = 'alert-danger';
                            $config        = array();
                            $filename      = $captcha->saveTextAsImage($config);
                            $comFunction->setCsrfToken('front_inq_suggestion');
                            $session = new Session();
                            $token = $session->get('front_inq_suggestion');

                            $response->setContent($filename['filename']);
                            $captcha_image = $filename['image_captcha'];
                            $error_cl      = 'alert-danger';
                            return $this->render('front/enquiry/add-enquiry-and-suggestion.html.twig',
                                array('form' => $form->createView(),'message' => $this->get('translator')->trans('Please provide valid data'),
                                    'show_form' => $show_form ,
                                    'data' => $captcha_image , 'error_cl'=> $error_cl , 'token' => $token));
                        }
                        $data = $this->getEmailList($request, 'Inquiries And Suggestion', strip_tags($form->get('reason')->getData()));
                        if ($data['success']) {
                            $comFunction->setCsrfToken('front_inq_suggestion');
                            $session = new Session();
                            $token   = $session->get('front_inq_suggestion');
                            $enquiryAndSuggestion->setCountry($request->get('_country'))->setSource("W");
                            $rest    = $this->get('app.services.enquiry_and_suggestion')->save($enquiryAndSuggestion, $data);
                            if ($rest) {
                                $config = array();
                                $filename = $captcha->saveTextAsImage($config);
                                $response->setContent($filename['filename']);
                                $captcha_image = $filename['image_captcha'];
                                $activityLog->logEvent(AppConstant::ACTIVITY_ADD_INQUIRY_FORM, 0, 
                                    array('user_ip' => $comFunction->getIP(), 'message' => 'Form is submitted Successfully', 'Data' => ''),null,null);
                                return $this->render('front/enquiry/add-enquiry-and-suggestion_success.html.twig',
                                    array('form' => $form->createView(),
                                        'message' => $this->get('translator')->trans('Form is submitted Successfully'),
                                        'show_form' => $show_form, 'data' => $captcha_image , 'token' => $token ));
                            }
                        }
                        else
                        {
                            $comFunction->setCsrfToken('front_inq_suggestion');
                            $session  = new Session();
                            $token    = $session->get('front_inq_suggestion');
                            $config   = array();
                            $filename = $captcha->saveTextAsImage($config);
                            $error_cl = 'alert-danger';
                            $response->setContent($filename['filename']);
                            $captcha_image = $filename['image_captcha'];
                            return $this->render('front/enquiry/add-enquiry-and-suggestion.html.twig', array('form' => $form->createView(),
                                'message' => $this->get('translator')->trans('Email has not been sent'), 'show_form' => $show_form,
                                'data' => $captcha_image, 'error_cl' => $error_cl, 'token' => $token));
                        }
                    }
                    else
                    {
                        $this->get('security.token_storage')->setToken(null);
                        $response = new RedirectResponse($this->generateUrl('landingpage'));
                        return $response;
                    }
                }
                catch (\Exception $e)
                {
                    $comFunction->setCsrfToken('front_inq_suggestion');
                    $session = new Session();
                    $token = $session->get('front_inq_suggestion');
                    $error_cl      = 'alert-danger';
                    $config        = array();
                    $filename      = $captcha->saveTextAsImage($config);
                    $response->setContent($filename['filename']);
                    $captcha_image = $filename['image_captcha'];
                    $activityLog->logEvent(AppConstant::ACTIVITY_ADD_INQUIRY_FORM_ERROR, 0, 
                        array('user_ip' => $comFunction->getIP(), 'message' => $e->getMessage(), 'Data' => '' , 'data' => $captcha_image),null,null);
                    return $this->render('front/enquiry/add-enquiry-and-suggestion.html.twig', array('form' => $form->createView(),
                        'message' => $e->getMessage() ,'show_form' => $show_form , 'data' => $captcha_image ,'error_cl'=> $error_cl, 'token' => $token ));
                }
            }
        }
        else
        {
            $comFunction->setCsrfToken('front_inq_suggestion');
            $session = new Session();
            $token = $session->get('front_inq_suggestion');

            if($form->isSubmitted()) {
                $activityLog->logEvent(AppConstant::ACTIVITY_ADD_INQUIRY_FORM_ERROR, 0, 
                    array('user_ip' => $comFunction->getIP(), 'message' => $this->get('translator')->trans('Dear Customer, you have already make submission for this form'),
                    'Data' => '', 'token' => $token,
                ),null,null);
                $error_cl     = 'alert-danger';
                $message = $this->get('translator')->trans('Dear Customer, you have already make submission for this form');
            } else {
                $error_cl     = 'alert-danger';
                $message      = $this->get('translator')->trans('Dear Customer, you have already make submission for this form');}
            $show_form        = false;
            return $this->render('front/enquiry/add-enquiry-and-suggestion.html.twig', array('form' => $form->createView(),
                'message' => $message , 'show_form' => $show_form , 'data' => '' ,'error_cl'=> $error_cl , 'token' => $token,
            ));
        }


        $comFunction->setCsrfToken('front_inq_suggestion');
        $session = new Session();
        $token   = $session->get('front_inq_suggestion');
        $config        = array();
        $filename      = $captcha->saveTextAsImage($config);
        $response->setContent($filename['filename']);
        $captcha_image = $filename['image_captcha'];
        return $this->render('front/enquiry/add-enquiry-and-suggestion.html.twig', array('form' => $form->createView(),'message' => "" ,'show_form' => $show_form , 'data' => $captcha_image , 'token' => $token ));
    }


    public function getEmailList(Request $request , $formtype , $enquiry_type){
        try
        {
            $commFunction    = new FunctionsController();
            $country_current      = $commFunction->getCountryCode($request);
            if ($enquiry_type == 'T') { $enguiry_email_type = 'technical';
            } else {
                $enguiry_email_type = 'other';
            }
            $language = $request->getLocale();
            $em = $this->getDoctrine()->getManager();
            $conn = $em->getConnection();
            $stm = $conn->prepare('SELECT * FROM email_setting WHERE country = ? AND type = ? AND ' . $enguiry_email_type . ' = ?  ');
            $stm->bindValue(1, $country_current);
            $stm->bindValue(2, $formtype);
            $stm->bindValue(3, 1);
            $stm->execute();
            $result = $stm->fetchAll();
            $data = array('success' => true , 'result'  => $result);
            return $data;
        }
        catch (\Exception $e) {
            $data = array('success' => false , 'result'  => $e->getMessage());
            return $data;
        }
    }

    /**
     * @Route("/{_country}/{_locale}/getFormSubmissionSettings", name="getFormSubmissionSettings")
     * @param $form
     * @param Request $request
     */

    public function getFormSubmissionSettings(Request $request , $form)
    {
        try
        {

            $commFunction    = new FunctionsController();
            $country_id      = $commFunction->getCountryCode($request);
            $formSettingList = $this->getDoctrine()
                ->getRepository('AppBundle:FormSetting')
                ->findOneBy(array('status' => 1, 'formtype' => $form , 'country' => $country_id));
            $country_id = $commFunction->getCountryCode($request);
            $i = 0;
            if($formSettingList == '' && $formSettingList == null) {
                return $formSettingList = false;
            } else
            {
                $submission_time_hours_for_checking = $formSettingList->getSubmissions();
                $number_of_entries = $formSettingList->getLimitto();
                if($submission_time_hours_for_checking)
                {
                    date_default_timezone_set("Asia/Riyadh");
                    $date_now = date('Y-m-d H:i:s');
                    $date_current = explode(' ', $date_now );
                    $date_current_days   = explode('-', $date_current[0] );
                    $date_current_hours  = explode(':' ,$date_current[1]);
                    $current_time =  mktime($date_current_hours[0] + 3, $date_current_hours[1], $date_current_hours[2],
                    $date_current_days[1], $date_current_days[2] , $date_current_days[0]);
                    $user_ip = $commFunction->getIP();
                    $formSettingList1 = $this->getDoctrine()
                        ->getRepository('AppBundle:EnquiryAndSuggestion')
                        ->findBy(array('user_ip' => $user_ip, 'country' => $country_id), array('id' => 'DESC'), $number_of_entries );
                    if(isset($formSettingList1) && $formSettingList1 != null)
                    {
                        if(count($formSettingList1) < $number_of_entries){
                            return true;
                        }
                        $i=0;
                          foreach ($formSettingList1 as $form_setting_list)
                          {
                              $date_of_submission = $formSettingList1[$i]->getCreated()->format('Y-m-d H:i:s');
                              $date_of_submission = explode(' ', $date_of_submission);
                              $date_of_submission[0];
                              $date_of_submission[1];
                              $date_of_submission_days_array = explode('-', $date_of_submission[0]);
                              $date_of_sub_hours_array = explode(':', $date_of_submission[1]);
                              $submission_time = mktime($date_of_sub_hours_array[0] + 3 + $submission_time_hours_for_checking, $date_of_sub_hours_array[1], $date_of_sub_hours_array[2],
                                      $date_of_submission_days_array[1], $date_of_submission_days_array[2], $date_of_submission_days_array[0]);
                              $submission_time;
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
            return false;
        }
    }

    public function setCsrfToken($token_name){
        $csrf = $this->get('security.csrf.token_manager');
        $guid = sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 61535), mt_rand(0, 61535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
        $d = new \DateTime("NOW");
        $currentDate = $d->format("Y/m/d H:i:s");
        $nonce = md5($guid);
        $passwordHash   = sha1(base64_encode($nonce) . $currentDate . AppConstant::IKTISSAB_API_SECRET);
        $passwordDigest =  base64_encode($passwordHash);
        $token = $csrf->refreshToken($passwordDigest);
        $session = new Session();
        $token_name = $token_name;
        $session->set($token_name, $token);
    }

    public function checkCsrfToken($csf_token , $token_name){
        $session = new Session();
        $token_name = $token_name;
        // csrf_admin_token
        $token_val = $session->get($token_name);
        $csf_token;
        if($token_val == $csf_token)
        {
            return true;
        }
        else
        {
            return false;
        }
    }




}
