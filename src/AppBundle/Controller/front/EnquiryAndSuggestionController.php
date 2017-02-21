<?php

namespace AppBundle\Controller\Front;

use AppBundle\Controller\Common\FunctionsController;
use AppBundle\Entity\EnquiryAndSuggestion;
use AppBundle\Form\EnquiryAndSuggestionType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\AppConstant;

class EnquiryAndSuggestionController extends Controller
{
    /**
     * @Route("/{_country}/{_locale}/enquiry-and-suggestion", name="front_enquiry_and_suggestion_add")
     * @param Request $request
     * @return Response
     */
    public function addAction(Request $request)
    {
        $enquiryAndSuggestion = new EnquiryAndSuggestion();
        $comFunction = new FunctionsController();

        $form = $this->createForm(EnquiryAndSuggestionType::class, $enquiryAndSuggestion, array(
            'extras' => array(
                'country' => $comFunction->getCountryCode($request)
        )));
        echo "===>>".$display_settings = $this->getFormSubmissionSettings($request, $form);
        $show_form = true;

        $form->handleRequest($request);

        if(isset($display_settings) && $display_settings != null) {
            if ($form->isValid())
            {
                try {
                    $data = $this->getEmailList($request, 'Inquiries And Suggestion', $form->get('reason')->getData());
                    //print_r($data);exit;
                    if ($data['success'])
                    {
                        $enquiryAndSuggestion->setCountry($request->get('_country'));
                        $rest = $this->get('app.services.enquiry_and_suggestion')->save($enquiryAndSuggestion, $data);
                        if ($rest)
                        {
                            $message = $this->get('translator')->trans('Record Added Successfully');
                            return $this->render(
                                   'front/enquiry/add-enquiry-and-suggestion_success.html.twig',
                                    array('form' => $form->createView(), 'message' => $message ,'show_form' => $show_form)
                            );
                        }
                    }
                    else
                    {
                        $message = $this->get('translator')->trans('');
                        return $this->render('front/enquiry/add-enquiry-and-suggestion.html.twig', array('form' => $form->createView(), 'message' => $message ,'show_form' => $show_form));
                    }

                } catch (\Exception $e) {
                    $message = $e->getMessage();
                    return $this->render(
                        'front/enquiry/add-enquiry-and-suggestion.html.twig',
                        array('form' => $form->createView(), 'message' => $message ,'show_form' => $show_form)
                    );
                }
            }
            else
            {
                $message = 'testing testing11';
                return $this->render('front/enquiry/add-enquiry-and-suggestion.html.twig',
                    array('form' => $form->createView(),'message' => $message,'show_form' => $show_form )
                );
            }
            //
        }
        else
        {
            if($form->isSubmitted())
            {
                $message = $this->get('translator')->trans('You have already submitted the form');
            }
            else
            {   $message = 'You have already submitted the form.You cannot submit the form right now'; }

            $show_form  = false;
            return $this->render('front/enquiry/add-enquiry-and-suggestion.html.twig',
                array('form' => $form->createView(), 'message' => $message , 'show_form' => $show_form ));

        }

        $message = '';

        return $this->render('front/enquiry/add-enquiry-and-suggestion.html.twig',
            array('form' => $form->createView(),'message' => $message,'show_form' => $show_form )
        );
    }


    public function getEmailList(Request $request , $formtype , $enquiry_type){
        try
        {
            $country_current = $this->getCountryCode($request);
            if ($enquiry_type == 'T') {
                $enguiry_email_type = 'technical';
            } else {
                // default email are all others but for technical we have to choose T
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
            $data = array(
                'success' => true ,
                'result'  => $result
            );
            //var_dump($data);
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

    private function getCountryCode(Request $request)
    {
        return $country_id = $request->get('_country');
    }

    /**
     * @Route("/{_country}/{_locale}/getFormSubmissionSettings", name="getFormSubmissionSettings")
     * @param $form
     */

    public function getFormSubmissionSettings(Request $request , $form)
    {
        $form = 'Inquiries And Suggestion';
        try
        {
            $commFunction    = new FunctionsController();
            $formSettingList = $this->getDoctrine()
                ->getRepository('AppBundle:FormSettings')
                ->findOneBy(array('status' => 1, 'formtype' => $form));
            // print_r($formSettingList);
            $country_id = $commFunction->getCountryCode($request);

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
                    $date_current_days  = explode('-', $date_current[0] );
                    $date_current_year  = $date_current_days[0];
                    $date_current_month = $date_current_days[1];
                    $date_current_day   = $date_current_days[2];
                    //echo '<br>';
                    $date_current_hours  = explode(':' ,$date_current[1]);
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
                        ->getRepository('AppBundle:EnquiryAndSuggestion')
                        ->findBy(array('user_ip' => $user_ip, 'country' => $country_id), array('id' => 'DESC'), $number_of_entries );
                    //  print_r($formSettingList1);

                    if(isset($formSettingList1) && $formSettingList1 != null)
                    {
                          $i=0;
                          foreach ($formSettingList1 as $form_setting_list)
                          {
                              echo '<br>'.'===>'.$date_of_submission = $formSettingList1[$i]->getCreated()->format('Y-m-d H:i:s');

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
                              echo '<br>'.$submission_time = mktime($date_of_sub_hour + 3 + $submission_time_hours_for_checking, $date_of_sub_minutes, $date_of_sub_seconds, $date_of_sub_month, $date_of_sub_days, $date_of_sub_year);
                              // here we will add the time with the submitted time for the user so that we know he still can submit the form or not
                              // ie afer one hour or after one day the user will submit the form
                              $submission_time;
                              // echo '<br>';
                              // echo 'current time' . $current_time;
                              // ct = 9 ; st = 8 ; gt = 2
                              if ($submission_time < $current_time) {
                                  return true;
                              }
                              $i++;
                          }
                          // change it to false
                          return false;
                    }
                }
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
