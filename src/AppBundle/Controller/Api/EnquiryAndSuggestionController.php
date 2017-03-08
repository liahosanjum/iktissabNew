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
        $formType = $this->getDoctrine()->getManager()
            ->getRepository("AppBundle:FormSettings")
            ->findBy(["country"=>$request->get("_country")]);

        $view = $this->view(ApiResponse::Response(true, 1, $formType), HttpCode::HTTP_OK);

        return $this->handleView($view);

    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function postEnquiry_and_suggestionAction(Request $request){

        $postData = $request->request->all();
        $enquiryAndSuggestion = new EnquiryAndSuggestion();
        $form = $this->createForm(EnquiryAndSuggestionType::class, $enquiryAndSuggestion);
        $view = null;
        try{
            $form->submit($postData);
            if($form->isValid()){
                $enquiryService = $this->get("app.services.enquiry_and_suggestion");
               // $enquiryService->save($enquiryAndSuggestion);


                $view = $this->view(ApiResponse::Response(true, 1, null), HttpCode::HTTP_OK);
            }
            else {
                $view = $this->view(ApiResponse::Response(false, 2, null), HttpCode::HTTP_OK);
            }
        }
        catch(Exception $e){
             $view = $this->view(ApiResponse::Response(false, 3, null), HttpCode::HTTP_OK);

        }

        return $this->handleView($view);
    }

}
