<?php

namespace AppBundle\Controller\Front;

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
        $form = $this->createForm(EnquiryAndSuggestionType::class, $enquiryAndSuggestion);
        $form->handleRequest($request);

        if($form->isValid())
        {
            try {


                $data = $this->getEmailList($request, 'Inquiries And Suggestion', $form->get('reason')->getData());
                //print_r($data);
                //exit;
                if($data['success'])
                {
                    $enquiryAndSuggestion->setCountry($request->get('_country'));
                    $rest =   $this->get('app.services.enquiry_and_suggestion')->save($enquiryAndSuggestion, $data);
                    if($rest)
                    {

                        echo $message = $this->get('translator')->trans('Record Added successfully');
                        return $this->render(

                            'front/enquiry/add-enquiry-and-suggestion.html.twig',
                            array('form' => $form->createView(),'message' => $message)
                        );

                    }
                }
                else
                {
                    echo $message = $this->get('translator')->trans('');
                    return $this->render(

                        'front/enquiry/add-enquiry-and-suggestion.html.twig',
                        array('form' => $form->createView(),'message' => $message)
                    );
                }

            }
            catch (\Exception $e)
            {
                $message = $e->getMessage();
                return $this->render(

                    'front/enquiry/add-enquiry-and-suggestion.html.twig',
                    array('form' => $form->createView(),'message' => $message)
                );
            }
        }
        //



        $message = '';
        return $this->render(

            'front/enquiry/add-enquiry-and-suggestion.html.twig',
            array('form' => $form->createView(),'message' => $message)
        );
    }


    public function getEmailList(Request $request , $formtype , $enquiry_type){
        try {
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
     * @Route("/{_country}/{_locale}/time", name="front_time")
     * @param Request $request
     * @return Response
     */
    public function getSubmissionCriteriaAction(){

        echo $currentTime =  date('Y-m-d HH');
        //print_r($currentTime);
        return $this->render(

            'front/enquiry/add-enquiry-and-suggestion.html.twig',
            array('form' => $form->createView(),'message' => $message)
        );

    }




}
