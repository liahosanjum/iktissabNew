<?php
/**
 * Created by PhpStorm.
 * User: abdulali
 * Date: 2/19/17
 * Time: 8:55 AM
 */
namespace AppBundle\Controller\Admin;

use AppBundle\AppConstant;
use AppBundle\Entity\User;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints as Assert;


class UserController extends Controller
{
    /**
     * @Route("/admin/users", name= "admin_users")
     * @Method("GET")
     * @Cache(smaxage="10")
     */

//* @Route("admin/users/{page}", requirements={"page": "[1-9]\d*"}, name="admin_users_paginated")

    public function adminUsersController(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $ikt = $request->query->get('ikt', '');
        $email = $request->query->get('email', '');
        $page = $request->query->get('page', 1);
        $users = $em->getRepository('AppBundle:User')->searchUsers($ikt, $email);
        $pager = new Pagerfanta(new DoctrineORMAdapter($users, true));
        $pager->setMaxPerPage(User::NUM_ITEMS);
        $routeGenerator = function ($page) {
            return '?pager=' . $page . '&email=eg';

        };
        if ($pager->haveToPaginate())
            $pager->setCurrentPage($page);
        return $this->render(
            '/admin/cms/users.html.twig',
            array(
                'users' => $pager,
                'ikt' => $ikt,
                'email' => $email
            )
        );
    }

    /**
     * @Route("/admin/sendotp", name="send_otp")
     */
    public function sendOtpAction(Request $request)
    {
//        sleep(3);
//        return new Response("true");
        //get iqama number from iktissab number
        $restClient = $this->get('app.rest_client');
        $url = 'en/api/' . $request->request->get('ikt') . '/userinfo.json';
        $data = $restClient->restGet(AppConstant::WEBAPI_URL . $url, array('Country-Id' => strtoupper($request->get('_country'))));
        if ($data['success'] == "true") {
            if ($data['user']['lang'] == "E") {
                $lang = 'en';
            } else {
                $lang = 'ar';
            }
            $url = $lang . "/api/" . $request->request->get('ikt') . "/sendsms/" . $data['user']['ID_no'] . ".json";
            $data = $restClient->restGet(AppConstant::WEBAPI_URL . $url, array('Country-Id' => strtoupper($request->get('_country'))));
            if ($data['success'] == true) {
                return new Response("true");
            }

        } else {
            return new Response("false");
        }
        return new Response("false");
    }

    /**
     * @Route("admin/{ikt}/details", name="iktissab_details_admin")
     */
    public function iktissabDetailsAction(Request $request, $ikt)
    {
        $restClient = $this->get('app.rest_client');
        $url = 'en/api/' . $ikt . '/userinfo.json';
        if ($ikt[0] == '5') {
            $country = 'eg';
        } else {
            $country = 'sa';
        }
        // echo AppConstant::WEBAPI_URL.$url;
        $data = $restClient->restGet(AppConstant::WEBAPI_URL . $url, array('Country-Id' => strtoupper($country)));
//        var_dump($data);

        if ($data['success'] == "true") {
            return $this->render('/admin/cms/userdetails.html.twig',
                array('iktData' => $data['user'])
            );


        }else{
            return new Response("Failed to load data");
        }
    }

}