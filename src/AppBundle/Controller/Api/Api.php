<?php
/**
 * Created by PhpStorm.
 * User: s.aman
 * Date: 25/03/2018
 * Time: 3:35 PM
 */

namespace AppBundle\Controller\Api;


use FOS\RestBundle\Controller\FOSRestController;

class Api extends FOSRestController
{
    protected function isRoleApi(){
        $roles = $this->get('security.token_storage')->getToken()->getUser()->getRoles();
        if(in_array('ROLE_API', $roles)) return true;
        return false;
    }
    protected function isRoleUser(){
        $roles = $this->get('security.token_storage')->getToken()->getUser()->getRoles();
        if(in_array('ROLE_API_CUSTOMER', $roles)) return true;
        return false;
    }
}