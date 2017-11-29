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
                            $activityLog->logEvent(AppConstant::ACTIVITY_ADD_FAQ_FORM, $user_ip_address, array('user_ip' => $user_ip_address, 'message' => $message_log, 'Data' => $postData));

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
            $activityLog->logEvent(AppConstant::ACTIVITY_ADD_FAQ_FORM_ERROR, $commFunction->getIP(), array('user_ip' => $commFunction->getIP(), 'message' => $message_log, 'Data' => ''));

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

            $data = $restClient->restGet(AppConstant::WEBAPI_URL . $url, array('Country-Id' => strtoupper($request->get('_country'))));

            if (count($data) > 0) {

                return $this->render('front/faqslist.html.twig', array('message' => '', 'data' => $data['data']));
            } else {
                return $this->render('front/faqslist.html.twig', array('message' => $this->get('translator')->trans('No record found'), 'data' => $data));

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

    function simple_php_captcha($config = array()) {

        // Check for GD library
        if( !function_exists('gd_info') )
        {
            throw new Exception('Required GD library is missing');
        }
        echo $bg_path = dirname(__FILE__) . '/backgrounds/';
        echo $font_path = dirname(__FILE__) . '/fonts/';
        echo $bg_path = 'http://localhost/iktissabNew/src/AppBundle/Controller/Front/backgrounds/';
        echo $font_path = 'localhost/iktissabNew/src/AppBundle/Controller/Front/fonts/';
        // Default values
        $captcha_config = array(
            'code' => '',
            'min_length' => 4,
            'max_length' => 4,
            'backgrounds' => array(
                $bg_path . '45-degree-fabric.png',
                $bg_path . 'cloth-alike.png',
                $bg_path . 'grey-sandbag.png',
                $bg_path . 'kinda-jean.png',
                $bg_path . 'polyester-lite.png',
                $bg_path . 'stitched-wool.png',
                $bg_path . 'white-carbon.png',
                $bg_path . 'white-wave.png'
            ),
            'fonts' => array(
                $font_path . 'times_new_yorker.ttf'
            ),
            'characters' => 'ABCDEFGHJKLMNPRSTUVWXYZabcdefghjkmnprstuvwxyz23456789',
            'min_font_size' => 28,
            'max_font_size' => 28,
            'color' => '#666',
            'angle_min' => 0,
            'angle_max' => 10,
            'shadow' => true,
            'shadow_color' => '#fff',
            'shadow_offset_x' => -1,
            'shadow_offset_y' => 1
        );

        // Overwrite defaults with custom config values
        if( is_array($config) ) {
            foreach( $config as $key => $value ) $captcha_config[$key] = $value;
        }

        // Restrict certain values
        if( $captcha_config['min_length'] < 1 ) $captcha_config['min_length'] = 1;
        if( $captcha_config['angle_min'] < 0 ) $captcha_config['angle_min'] = 0;
        if( $captcha_config['angle_max'] > 10 ) $captcha_config['angle_max'] = 10;
        if( $captcha_config['angle_max'] < $captcha_config['angle_min'] ) $captcha_config['angle_max'] = $captcha_config['angle_min'];
        if( $captcha_config['min_font_size'] < 10 ) $captcha_config['min_font_size'] = 10;
        if( $captcha_config['max_font_size'] < $captcha_config['min_font_size'] ) $captcha_config['max_font_size'] = $captcha_config['min_font_size'];

        // Generate CAPTCHA code if not set by user
        if( empty($captcha_config['code']) ) {
            $captcha_config['code'] = '';
            $length = mt_rand($captcha_config['min_length'], $captcha_config['max_length']);
            while( strlen($captcha_config['code']) < $length ) {
                $captcha_config['code'] .= substr($captcha_config['characters'], mt_rand() % (strlen($captcha_config['characters'])), 1);
            }
        }

        // Generate HTML for image src
        if ( strpos($_SERVER['SCRIPT_FILENAME'], $_SERVER['DOCUMENT_ROOT']) ) {
            $image_src = substr(__FILE__, strlen( realpath($_SERVER['DOCUMENT_ROOT']) )) . '?_CAPTCHA&amp;t=' . urlencode(microtime());
            $image_src = '/' . ltrim(preg_replace('/\\\\/', '/', $image_src), '/');
        } else {
            $_SERVER['WEB_ROOT'] = str_replace($_SERVER['SCRIPT_NAME'], '', $_SERVER['SCRIPT_FILENAME']);
            $image_src           = substr(__FILE__, strlen( realpath($_SERVER['WEB_ROOT']) )) . '?_CAPTCHA&amp;t=' . urlencode(microtime());
            $image_src           = '/' . ltrim(preg_replace('/\\\\/', '/', $image_src), '/');
        }
        $_SESSION['_CAPTCHA']['config'] = serialize($captcha_config);
        return array(
            'code' => $captcha_config['code'],
            'image_src' => $image_src
        );

    }

    /**
     * @Route("/{_country}/{_locale}/testbk", name="front_testbk")
     * @param Request $request
     */
    public function testbkAction(){


        if( !function_exists('hex2rgb') ) {
            function hex2rgb($hex_str, $return_string = false, $separator = ',') {
                $hex_str = preg_replace("/[^0-9A-Fa-f]/", '', $hex_str); // Gets a proper hex string
                $rgb_array = array();
                if( strlen($hex_str) == 6 ) {
                    $color_val = hexdec($hex_str);
                    $rgb_array['r'] = 0xFF & ($color_val >> 0x10);
                    $rgb_array['g'] = 0xFF & ($color_val >> 0x8);
                    $rgb_array['b'] = 0xFF & $color_val;
                } elseif( strlen($hex_str) == 3 ) {
                    $rgb_array['r'] = hexdec(str_repeat(substr($hex_str, 0, 1), 2));
                    $rgb_array['g'] = hexdec(str_repeat(substr($hex_str, 1, 1), 2));
                    $rgb_array['b'] = hexdec(str_repeat(substr($hex_str, 2, 1), 2));
                } else {
                    return false;
                }
                return $return_string ? implode($separator, $rgb_array) : $rgb_array;
            }
        }


        if( isset($_GET['_CAPTCHA']) ) {
            $captcha_config = unserialize($_SESSION['_CAPTCHA']['config']);
            if( !$captcha_config ) exit();

            unset($_SESSION['_CAPTCHA']);

            // Pick random background, get info, and start captcha
            $background = $captcha_config['backgrounds'][mt_rand(0, count($captcha_config['backgrounds']) -1)];
            list($bg_width, $bg_height, $bg_type, $bg_attr) = getimagesize($background);

            $captcha = imagecreatefrompng($background);

            $color = hex2rgb($captcha_config['color']);
            $color = imagecolorallocate($captcha, $color['r'], $color['g'], $color['b']);

            // Determine text angle
            $angle = mt_rand( $captcha_config['angle_min'], $captcha_config['angle_max'] ) * (mt_rand(0, 1) == 1 ? -1 : 1);

            // Select font randomly
            $font = $captcha_config['fonts'][mt_rand(0, count($captcha_config['fonts']) - 1)];

            // Verify font file exists
            if( !file_exists($font) ) throw new Exception('Font file not found: ' . $font);

            //Set the font size.
            $font_size = mt_rand($captcha_config['min_font_size'], $captcha_config['max_font_size']);
            $text_box_size = imagettfbbox($font_size, $angle, $font, $captcha_config['code']);

            // Determine text position
            $box_width  = abs($text_box_size[6] - $text_box_size[2]);
            $box_height = abs($text_box_size[5] - $text_box_size[1]);
            $text_pos_x_min = 0;
            $text_pos_x_max = ($bg_width) - ($box_width);
            $text_pos_x = mt_rand($text_pos_x_min, $text_pos_x_max);
            $text_pos_y_min = $box_height;
            $text_pos_y_max = ($bg_height) - ($box_height / 2);
            if ($text_pos_y_min > $text_pos_y_max) {
                $temp_text_pos_y = $text_pos_y_min;
                $text_pos_y_min = $text_pos_y_max;
                $text_pos_y_max = $temp_text_pos_y;
            }
            $text_pos_y = mt_rand($text_pos_y_min, $text_pos_y_max);

            // Draw shadow
            if( $captcha_config['shadow'] ){
                $shadow_color = hex2rgb($captcha_config['shadow_color']);
                $shadow_color = imagecolorallocate($captcha, $shadow_color['r'], $shadow_color['g'], $shadow_color['b']);
                imagettftext($captcha, $font_size, $angle, $text_pos_x + $captcha_config['shadow_offset_x'], $text_pos_y + $captcha_config['shadow_offset_y'], $shadow_color, $font, $captcha_config['code']);
            }
            // Draw text
            imagettftext($captcha, $font_size, $angle, $text_pos_x, $text_pos_y, $color, $font, $captcha_config['code']);
            // Output image
            header("Content-type: image/png");
            imagepng($captcha);


        }

        $_SESSION['captcha'] = $this->simple_php_captcha();
        $_SESSION['captcha']['code'];
        echo '<img src="' . $_SESSION['captcha']['image_src'] . '" alt="CAPTCHA code">';
        return $this->render('front/test.html.twig', array('message' => $this->get('translator')->trans('No record found'), 'data' => $_SESSION['captcha']['code']));

    }





    /**

     * @param type $text
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("/{_country}/{_locale}/test", name="front_test")
     * @param Request $request
     */
    public function testAction(Request $request)
    {
        //$response->headers->set('Cache-Control', 'private');
        //$response->headers->set('Content-type', mime_content_type($filename));
        //$response->headers->set('Content-Disposition', 'attachment; filename="' . basename($filename) . '";');
        //$response->headers->set('Content-length', filesize($filename));
        // Send headers before outputting anything
        //$response->sendHeaders();
        $config       = array();

        $filename     = $this->saveTextAsImage($config);
        // $filename   = $this->saveTextAsImage($config);
        $response     = new Response();
        $file_name    = $response->setContent($filename);
        // $response;
        return $this->render('front/test.html.twig', array('message' => $this->get('translator')->trans('No record found'), 'data' => $file_name));
    }

    /**
     * Method convert given text to PNG image and returs
     * file name
     * @param type $text Text
     * @return string File Name
     */
    public function saveTextAsImage($config = array()) {
        // Create the image
        $config = array();
        $bg_path   = $this->get('kernel')->getRootDir()  . '/../web/backgrounds/';
        $font_path = $this->get('kernel')->getRootDir()  . '/../web/fonts/captcha-fonts/';
        // Default values
        $captcha_config = array(
            'code'        => '',
            'min_length'  => 4,
            'max_length'  => 4,
            'backgrounds' => array(
                $bg_path . '45-degree-fabric.png',
                $bg_path . 'cloth-alike.png',
                $bg_path . 'grey-sandbag.png',
                $bg_path . 'kinda-jean.png',
                $bg_path . 'polyester-lite.png',
                $bg_path . 'stitched-wool.png',
                $bg_path . 'white-carbon.png',
                $bg_path . 'white-wave.png'
            ),
            'fonts'     => array(
                $font_path . 'times_new_yorker.ttf'
            ),
            'characters' => 'ABCDEFGHJKLMNPRSTUVWXYZabcdefghjkmnprstuvwxyz23456789',
            'min_font_size' => 28,
            'max_font_size' => 28,
            'color' => '#666',
            'angle_min' => 0,
            'angle_max' => 10,
            'shadow' => true,
            'shadow_color' => '#fff',
            'shadow_offset_x' => -1,
            'shadow_offset_y' => 1
        );

        // Overwrite defaults with custom config values
        if( is_array($config) ) {
            foreach( $config as $key => $value ) $captcha_config[$key] = $value;
        }

        // Restrict certain values
        if( $captcha_config['min_length'] < 1 ) $captcha_config['min_length'] = 1;
        if( $captcha_config['angle_min'] < 0 ) $captcha_config['angle_min'] = 0;
        if( $captcha_config['angle_max'] > 10 ) $captcha_config['angle_max'] = 10;
        if( $captcha_config['angle_max'] < $captcha_config['angle_min'] ) $captcha_config['angle_max'] = $captcha_config['angle_min'];
        if( $captcha_config['min_font_size'] < 10 ) $captcha_config['min_font_size'] = 10;
        if( $captcha_config['max_font_size'] < $captcha_config['min_font_size'] ) $captcha_config['max_font_size'] = $captcha_config['min_font_size'];

        // Generate CAPTCHA code if not set by user
        if( empty($captcha_config['code']) ) {
            $captcha_config['code'] = '';
            $length = mt_rand($captcha_config['min_length'], $captcha_config['max_length']);
            while( strlen($captcha_config['code']) < $length ) {
                $captcha_config['code'] .= substr($captcha_config['characters'], mt_rand() % (strlen($captcha_config['characters'])), 1);
            }
        }
        $imageCreator = imagecreatetruecolor(100, 30);
        // Create some colors
        $white        = imagecolorallocate($imageCreator, 255, 255, 255);
        $grey         = imagecolorallocate($imageCreator, 128, 128, 128);
        $black        = imagecolorallocate($imageCreator, 0, 0, 0);
        imagefilledrectangle($imageCreator, 0, 0, 399, 29, $white);
        $this->get('session')->set('_CAPTCA', $captcha_config['code']);
        $this->get('kernel')->getRootDir() . '/../web';
        $font = $this->get('kernel')->getRootDir() . '/../web/fonts/times_new_yorker.ttf'; //'arial.ttf';
        // Add some shadow to the text
        imagettftext($imageCreator, 20, 0, 11, 21, $grey, $font, $captcha_config['code']);
        // Add the text
        imagettftext($imageCreator, 20, 0, 10, 20, $black, $font, $captcha_config['code']);
        // Using imagepng() results in clearer text compared with imagejpeg()
         $file_name = $this->get('kernel')->getRootDir() .'/../web/img/ikt22.png';
        imagepng($imageCreator, $file_name);
        imagedestroy($imageCreator);
        return $file_name;
    }





}




