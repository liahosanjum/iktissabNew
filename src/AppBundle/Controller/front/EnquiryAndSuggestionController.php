<?php

namespace AppBundle\Controller\Front;

use AppBundle\Entity\EnquiryAndSuggestion;
use AppBundle\Form\EnquiryAndSuggestionType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
        if($form->isValid()){
            \AppBundle\Controller\Common\EnquiryAndSuggestion::save($this->getDoctrine()->getManager(), $enquiryAndSuggestion);
        }
        return $this->render(
            'front/default/add-enquiry-and-suggestion.html.twig',
            array('form' => $form->createView())
        );
    }
}
