<?php

namespace AppBundle\Controller\Api;

use AppBundle\HttpCode;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Controller\FOSRestController;

/**
 * Class ApiController
 * @package AppBundle\Controller
 * @RouteResource("services", pluralize=false)
 */
class EnquiryAndSuggestionController extends FOSRestController
{

    public function postEnquiryAndSuggestionAction($user){
        $view = $this->view(['Value' => $user->getId()], HttpCode::HTTP_OK);
        return $this->handleView($view);
    }

}
