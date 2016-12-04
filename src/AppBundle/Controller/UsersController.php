<?php
namespace AppBundle\Controller;

use FOS\RestBundle\Controller\Annotations\Route;
use FOS\RestBundle\Controller\FOSRestController;

class UsersController extends FOSRestController
{
    /**
     * @Route("/getusers.{_format}", name="getusers", defaults={"_format"="json"})
     */
    public function getUsersAction()
    {
        $data =array('user' => array('name' => 'Abdul basit',
            'age' => '44',
            'address' => 'office colony '
        )
        );
        $view = $this->view($data, 200)
            ->setTemplate("default/getUsers.html.twig")
            ->setTemplateVar('data')
            ->setTemplateData($data)
            ;

        return $this->handleView($view);
    }
}
?>