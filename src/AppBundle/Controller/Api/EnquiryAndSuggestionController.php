<?php

namespace AppBundle\Controller\Api;

use AppBundle\Entity\EnquiryAndSuggestion;
use AppBundle\Entity\FormSettings;
use AppBundle\Form\EnquiryAndSuggestionType;
use AppBundle\HttpCode;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;


/**
 * Class ApiController
 * @package AppBundle\Controller
 * @RouteResource("services", pluralize=false)
 */
class EnquiryAndSuggestionController extends FOSRestController
{

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getEnquiry_and_suggestion_settingAction(Request $request){
        //stat 1= allowed, 2=not allowed
        $country = $request->get("_country");
        $em = $this->getDoctrine()->getManager();
        $formSetting = $em->getRepository("AppBundle:FormSettings")->findByCountryAndType($country, FormSettings::Inquiries_And_Suggestion);

        $currentDate = new \DateTime("now");
        $currentDate->sub(new \DateInterval("PT".$formSetting->getSubmissions()."H"));

        $submissions = $em->getRepository("AppBundle:EnquiryAndSuggestion")->findByUserIPAndCreated($currentDate->format("Y-m-d H:i:s"));

        if(count($submissions) < $formSetting->getLimitto()){
            $view = $this->view(ApiResponse::Response(true, 1, "Allowed"), HttpCode::HTTP_OK);
        }
        else{
            $view = $this->view(ApiResponse::Response(false, 2, "Not Allowed"), HttpCode::HTTP_OK);
        }
        return $this->handleView($view);

    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function postEnquiry_and_suggestionAction(Request $request){
        //status 1 = saved, 2= pending request, 3 = exception while saving
        $postData = $request->request->all();
        //$postData["captchaCode"] = "abcd";
        $country = $request->get("_country");
        $enquiryAndSuggestion = new EnquiryAndSuggestion();

        $form = $this->createForm(EnquiryAndSuggestionType::class, $enquiryAndSuggestion, ['extras' =>['country' => $country, "mobile"=>true]]);

        $view = null;
        try{
            $form->submit($postData);
            if($form->isValid()){
                //$enquiryAndSuggestion->setSource("M");
                $fieldTechnicalOROther = $postData["reason"] == "T"?"technical":"other";

                $em = $this->getDoctrine()->getManager();
                $formSetting = $em->getRepository("AppBundle:FormSettings")->findByCountryAndType($country, FormSettings::Inquiries_And_Suggestion);

                $currentDate = new \DateTime("now");
                $currentDate->sub(new \DateInterval("PT".$formSetting->getSubmissions()."H"));

                $submissions = $em->getRepository("AppBundle:EnquiryAndSuggestion")->findByUserIPAndCreated($currentDate->format("Y-m-d H:i:s"));

                if(count($submissions) < $formSetting->getLimitto()){

                    $emailSetting = $em->getRepository("AppBundle:Settings")
                        ->findBy(["country"=>$country, $fieldTechnicalOROther=>1, "type"=>FormSettings::Inquiries_And_Suggestion ]);
                    $es = $emailSetting[0];
                    $data = array( 'success' => true , 'result'  => [
                        ["id"=>$es->getId(),"email"=>$es->getEmail(), "type"=>$es->gettype(), "country"=>$es->getCountry(), "technical"=>$es->getTechnical(), "other"=>$es->getOther()]
                    ]);

                    $enquiryAndSuggestion->setSource("M")->setCountry($country);
                    $enquiryService = $this->get("app.services.enquiry_and_suggestion");

                    if($enquiryService->save($enquiryAndSuggestion, $data)){
                        $view = $this->view(ApiResponse::Response(true, 1, $formSetting), HttpCode::HTTP_OK);
                    }
                    else{
                        $view = $this->view(ApiResponse::Response(false, 3, null), HttpCode::HTTP_OK);
                    }
                }
                else{
                    $view = $this->view(ApiResponse::Response(false, 2, null), HttpCode::HTTP_OK);
                }

            }
            else {
                $view = $this->view(ApiResponse::Response(false, 3, $form->getErrors(true, true)), HttpCode::HTTP_OK);
            }
        }
        catch(Exception $e){
             $view = $this->view(ApiResponse::Response(false, 3, null), HttpCode::HTTP_OK);

        }

        return $this->handleView($view);
    }

}
