<?php
/**
 * Created by PhpStorm.
 * User: abdulali
 * Date: 1/11/17
 * Time: 1:23 PM
 */

namespace AppBundle\Controller;


use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ApiController
 * @package AppBundle\Controller
 * @RouteResource("User", pluralize=false)
 */
class ApiController extends FOSRestController
{
    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getTestAction(Request $request)
    {
        $view = $this->view(['TestArray' => array("name" => "abdul", "marks" => "20", "country"=>'sa', "locale" => 'en'), 'base_url' => "baseurl"], 200);
    return $this->handleView($view);
    }

}