<?php

namespace AppBundle\Controller\Api;

use AppBundle\Entity\EnquiryAndSuggestion;
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
    public function postEnquiry_and_suggestionAction(Request $request){

        $postData = $request->request->all();
        $enquiryAndSuggestion = new EnquiryAndSuggestion();
        $form = $this->createForm(EnquiryAndSuggestionType::class, $enquiryAndSuggestion);
        $view = null;
        try{
            $form->submit($postData);
            if($form->isValid()){

                $enquiryAndSuggestion->setCountry($request->get('_country'));
                \AppBundle\Controller\Common\EnquiryAndSuggestion::save($this->getDoctrine()->getManager(), $enquiryAndSuggestion);

                $view = $this->view(array("Value"=>true), HttpCode::HTTP_OK);
            }
            else {
                $view = $this->view(array("Value" => false), HttpCode::HTTP_OK);
            }
        }
        catch(Exception $e){
             $view = $this->view(array("Value"=>false ), HttpCode::HTTP_OK);

        }

        return $this->handleView($view);
    }

}
