<?php

namespace AppBundle\Controller\Front;


use AppBundle\AppConstant;
use AppBundle\Form\FaqType;
use AppBundle\Entity\Faq;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Captcha\Bundle\CaptchaBundle\Validator\Constraints as CaptchaAssert;
use Symfony\Component\Routing\Generator\UrlGenerator;
use AppBundle\Controller\Common\FunctionsController;
use Symfony\Component\HttpFoundation\Response;


class FaqsController extends Controller
{

    /**
     * @Route("/{_country}/{_locale}/faqlist", name="faqlist")
     * @param Request $request
     */
    public function faqlistAction(Request $request)
    {
        try {
            $activityLog = $this->get('app.activity_log');
            $commFunction = new FunctionsController();
         



            if ($commFunction->checkSessionCookies($request) == false) {
                return $this->redirect($this->generateUrl('landingpage'));
            } else {
                return $this->redirect($this->generateUrl('landingpage'));
            }
            $locale_cookie = $request->getLocale();
            $country_cookie = $request->get('_country');
            $userLang = trim($request->query->get('lang'));
            if ($userLang != '' && $userLang != null) {
                if ($userLang == $locale_cookie) {
                    $request->getLocale();
                    $commFunction->changeLanguage($request, $userLang);
                    $locale_cookie = $request->getLocale();
                } else {
                    if ($request->cookies->get(AppConstant::COOKIE_LOCALE)) {
                        return $this->redirect($this->generateUrl('faqs', array('_country' => $request->cookies->get(AppConstant::COOKIE_COUNTRY), '_locale' => $request->cookies->get(AppConstant::COOKIE_LOCALE))));
                    }
                }
            }
            if ($request->cookies->get(AppConstant::COOKIE_LOCALE)) {
                $cookieLocale = $request->cookies->get(AppConstant::COOKIE_LOCALE);
                $cookieCountry = $request->cookies->get(AppConstant::COOKIE_COUNTRY);
                if (isset($cookieLocale) && $cookieLocale <> '' && $cookieLocale != $locale_cookie) {
                    // modify here if the language is to be changes forom the uprl
                    return $this->redirect($this->generateUrl('faqs', array('_country' => $cookieCountry, '_locale' => $cookieLocale)));
                }
                if (isset($cookieCountry) && $cookieCountry <> '' && $cookieCountry != $country_cookie) {
                    return $this->redirect($this->generateUrl('faqs', array('_country' => $cookieCountry, '_locale' => $cookieLocale)));
                }
            }
            $show_form = true;
            $display_settings = $this->getFormSubmissionSettings($request, 'Faqs Form');
            $faqs = new Faq();
            $form = $this->createForm(FaqType::class, $faqs, array('extras' => array('country' => $commFunction->getCountryCode($request))));
            $locale = $request->getLocale();
            $country = $commFunction->getCountryCode($request);
            $form->handleRequest($request);
            $posted = array();
            $postData = $request->request->all();
            if (isset($display_settings) && $display_settings != null) {
                /***********/
                if ($form->isSubmitted() && $form->isValid()) {
                    try {
                        $data = $this->getEmailList($request, 'Faqs Form');
                        if ($data['success']) {
                            $faqs->setCreated(new \DateTime('now'));
                            $faqs->setCountry($country);
                            // saving user ip
                            $user_ip_address = $commFunction->getIP();
                            $faqs->setUser_ip($commFunction->getIP());
                            $em = $this->getDoctrine()->getManager();
                            $em->persist($faqs);
                            $em->flush();
                            if ($faqs->getId()) {
                                $message = \Swift_Message::newInstance();
                                $i = 0;
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
                                    ->setBody($this->container->get('templating')->render(':email-templates/faqs:faqs.html.twig', ['email' => $faqs->getEmail(), 'mobile' => $faqs->getMobile(), 'question' => $faqs->getQuestion()]), 'text/html');
                                $this->container->get('mailer')->send($message);
                            }
                            $message_log = $this->get('translator')->trans('Your request has been submitted');
                            $activityLog->logEvent(AppConstant::ACTIVITY_ADD_FAQ_FORM, $user_ip_address,
                                null, null,
                                array('user_ip' => $user_ip_address, 'message' => $message_log, 'Data' => $postData));

                            return $this->render('front/faqs.html.twig', array('form' => $form->createView(), 'message' => $this->get('translator')->trans('Your request has been submitted'), 'show_form' => $show_form));
                        }
                    } catch (\Exception $e) {
                        return $this->render('front/faqs.html.twig', array('form' => $form->createView(), 'message' => $e->getMessage(), 'show_form' => $show_form));
                    }
                }
                $message = '';
                return $this->render('front/faqs.html.twig', array('form' => $form->createView(), 'message' => $message, 'show_form' => $show_form));
            } else {
                if ($form->isSubmitted()) {
                    $message = $this->get('translator')->trans('Dear Customer, you have already make submission for this form.');
                } else {
                    $message = '';
                }
                $show_form = false;
                return $this->render('front/faqs.html.twig', array('form' => $form->createView(), 'message' => $message, 'show_form' => $show_form));
            }
        } catch (\Exception $e) {
            $message_log = $e->getMessage();
            $activityLog->logEvent(AppConstant::ACTIVITY_ADD_FAQ_FORM_ERROR, $commFunction->getIP(),
                null, null,
                array('user_ip' => $commFunction->getIP(), 'message' => $message_log, 'Data' => ''));

            return $this->render('front/faqs.html.twig', array('message' => $e->getMessage(), 'show_form' => $show_form));
        }
    }


    public function getEmailList(Request $request, $formtype)
    {
        try {
            $commFunction = new FunctionsController();
            $country_current = $commFunction->getCountryCode($request);
            $enguiry_email_type = 'other';
            $em = $this->getDoctrine()->getManager();
            $conn = $em->getConnection();
            $stm = $conn->prepare('SELECT * FROM email_setting WHERE country = ? AND type = ? AND ' . $enguiry_email_type . ' = ?  ');
            $stm->bindValue(1, $country_current);
            $stm->bindValue(2, $formtype);
            // here checking the others equal to 1.
            $stm->bindValue(3, 1);
            $stm->execute();
            $result = $stm->fetchAll();
            if ($result) {
                $data = array('success' => true, 'result' => $result);
            }
            return $data;
        } catch (\Exception $e) {
            $data = array('success' => false, 'result' => $e->getMessage());
            return $data;
        }
    }

    public function getFormSubmissionSettings(Request $request, $form)
    {
        try {
            $commFunction = new FunctionsController();
            $country_id = $commFunction->getCountryCode($request);
            $formSettingList = $this->getDoctrine()
                ->getRepository('AppBundle:FormSetting')
                ->findOneBy(array('status' => 1, 'formtype' => $form, 'country' => $country_id));
            $i = 0;
            if ($formSettingList == '' && $formSettingList == null) {
                return $formSettingList = false;
            } else {
                $submission_time_hours_for_checking = $formSettingList->getSubmissions();
                $number_of_entries = $formSettingList->getLimitto();
                if ($submission_time_hours_for_checking) {
                    date_default_timezone_set("Asia/Riyadh");
                    $date_now = date('Y-m-d H:i:s');
                    $date_current = explode(' ', $date_now);
                    $date_current_days = explode('-', $date_current[0]);
                    $date_current_hours = explode(':', $date_current[1]);
                    $current_time = mktime($date_current_hours[0] + 3, $date_current_hours[1], $date_current_hours[2], $date_current_days[1], $date_current_days[2], $date_current_days[0]);
                    $user_ip = $commFunction->getIP();
                    $formSettingList1 = $this->getDoctrine()
                        ->getRepository('AppBundle:Faq')
                        ->findBy(array('user_ip' => $user_ip, 'country' => $country_id), array('id' => 'DESC'), $number_of_entries);
                    if (isset($formSettingList1) && $formSettingList1 != null) {
                        $i = 0;
                        if (count($formSettingList1) < $number_of_entries) {
                            return true;
                        }
                        foreach ($formSettingList1 as $form_setting_list) {
                            if (count($formSettingList1) < $number_of_entries) {
                                return true;
                            }
                            $date_of_submission = explode(' ', $formSettingList1[$i]->getCreated()->format('Y-m-d H:i:s'));
                            $date_of_submission_days_array = explode('-', $date_of_submission[0]);
                            $date_of_sub_hours_array = explode(':', $date_of_submission[1]);
                            $submission_time = mktime($date_of_sub_hours_array[0] + 3 + $submission_time_hours_for_checking, $date_of_sub_hours_array[1], $date_of_sub_hours_array[2],
                                $date_of_submission_days_array[1], $date_of_submission_days_array[2], $date_of_submission_days_array[0]);
                            $submission_time;
                            $current_time;
                            if ($submission_time < $current_time) {
                                return true;
                            }
                            $i++;
                        }
                        return false;
                    } else {
                        return true;
                    }
                }
            }
        } catch (\Exception $e) {
            return $this->render('front/faqs.html.twig', array('form' => $form->createView(), 'message' => $e->getMessage()));
        }
    }


    /**
     * @Route("/{_country}/{_locale}/faqs", name="front_faqs")
     * @param Request $request
     */
    public function faqsAction(Request $request)
    {
        try {
            $response = new Response();
            $commFunct = new FunctionsController();
            /****************/
            $response = new Response();
            $commFunct = new FunctionsController();
            if ($commFunct->checkSessionCookies($request) == false) {
                return $this->redirect($this->generateUrl('landingpage'));
            }

            $userLang = '';
            $locale = $request->getLocale();

            if ($request->query->get('lang')) {
                $userLang = trim($request->query->get('lang'));
            }
            if ($userLang != '' && $userLang != null) {
                if ($userLang == $locale) {
                    $commFunct->changeLanguage($request, $userLang);
                    $locale = $request->getLocale();
                } else {
                    if ($request->cookies->get(AppConstant::COOKIE_LOCALE)) {
                        return $this->redirect($this->generateUrl('homepage', array('_country' => $request->cookies->get(AppConstant::COOKIE_COUNTRY), '_locale' => $request->cookies->get(AppConstant::COOKIE_LOCALE))));
                    }
                }
            }


            $userCountry = '';
            if ($request->query->get('ccid')) {
                $userCountry = $request->query->get('ccid');
            }
            $country = $request->get('_country');
            if ($userCountry != '' && $userCountry != null) {
                if ($userCountry == $country) {
                    $commFunct->changeCountry($request, $userCountry);
                    $country = $request->get('_country');
                } else {
                    if ($request->cookies->get(AppConstant::COOKIE_COUNTRY)) {
                        return $this->redirect($this->generateUrl('homepage', array('_country' => $request->cookies->get(AppConstant::COOKIE_COUNTRY), '_locale' => $request->cookies->get(AppConstant::COOKIE_LOCALE))));
                    }
                }
            }

            if ($request->cookies->get(AppConstant::COOKIE_LOCALE)) {
                $cookieLocale = $request->cookies->get(AppConstant::COOKIE_LOCALE);
                $cookieCountry = $request->cookies->get(AppConstant::COOKIE_COUNTRY);
                if (isset($cookieLocale) && $cookieLocale <> '' && $cookieLocale != $locale) {
                    return $this->redirect($this->generateUrl('homepage', array('_country' => $cookieCountry, '_locale' => $cookieLocale)));
                }
                if (isset($cookieCountry) && $cookieCountry <> '' && $cookieCountry != $country) {
                    return $this->redirect($this->generateUrl('homepage', array('_country' => $cookieCountry, '_locale' => $cookieLocale)));
                }
            }


            /****************/


            $restClient = $this->get('app.rest_client');
            $url = $request->getLocale() . '/api/faqlist.json';
            AppConstant::WEBAPI_URL . $url;
            $data = $restClient->restGet(AppConstant::WEBAPI_URL . $url, array('Country-Id' => strtoupper($request->get('_country'))));

            if (count($data['data']) > 0)
            {
                return $this->render('front/faqslist.html.twig', array('message' => '', 'data' => $data['data']));

            } else {
                return $this->render('front/faqslist.html.twig', array(
                    'message' => $this->get('translator')->trans('No record found'),
                    'data' => null));

            }

        } catch (\Exception $e) {
            return $this->render('front/faqslist.html.twig', array('message' => $e->getMessage(), 'data' => null));
        }
    }

    private function validateCard($card, $country)
    {
        if ($country == 'sa') {
            $pattern = self::IKTCARD_SA_PATTERN;  // "/^9[0-9]{7}$/";
        } else {
            $pattern = self::IKTCARD_EG_PATTERN;  // "/^5[0-9]{7}$/";
        }
        $iktissab_id = $card;
        $country_id = $country;
        $data_validate['success'] = true;
        $data_validate['message'] = '';
        if ($country_id == 'eg') {
            if (!preg_match(self::IKTCARD_EG_PATTERN, $iktissab_id)) {
                $data_validate = array('success' => false, 'message' => $this->get('translator')->trans('Please provide valid Iktissab Id'));
                return $data_validate;
            } else {
                $first_ch = substr($iktissab_id, 0, 1);
                if ($first_ch != 5) {
                    $data_validate = array('success' => false, 'message' => $this->get('translator')->trans('Iktissab Id must start with 5 for Egypt'));
                    return $data_validate;
                }
            }
        } else {
            if (!preg_match(self::IKTCARD_SA_PATTERN, $iktissab_id)) {
                return $data_validate = array('success' => false, 'message' => $this->get('translator')->trans('Please provide valid Iktissab Id'));
            } else {
                $first_ch = substr($iktissab_id, 0, 1);
                if ($first_ch != 9) {
                    return $data_validate = array('success' => false, 'message' => $this->get('translator')->trans('Iktissab Id must start with 9 for Saudi Arabia'));
                }
            }
        }
        return $data_validate;
    }
    
}




