<?php

namespace AppBundle\Controller;

use AppBundle\AppConstant;
use AppBundle\Controller\Common\FunctionsController;
use AppBundle\Entity\Subscription;
use AppBundle\Form\SendPwdType;
use Doctrine\ORM\Query\ResultSetMapping;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\RedirectResponse;

// use Symfony\Component\Form\Extension\Core\Type\IntegerType;


class TestServicesController extends Controller
{
    /**
     * @Route("/{_country}/{_locale}/testservices/getTestRS")
     * @param Request $request
     * @return Response
     */

    public function getTestRSAction(Request $request , $form_param)
    {
        $country_id  = $request->get('_country');
        $restClient  = $this->get('app.rest_client')->IsAdmin(true);
        // print_r($form_param);
        /************************/
        // echo $url  = $request->getLocale() . '/api/create_offlineuser.json';
        if($_POST['url'] != "" )
        {
            $url = $_POST['url'];
        }
        else
        {
            $url = $_POST['url2'];
        }
        $url = $request->getLocale().'/api/'.$url ;
        if(!empty($_POST))
        {
            // exit;
            $data = $restClient->restGetForm(AppConstant::WEBAPI_URL . $url, array('Country-Id' => $country_id));
            // print_r($data);
            // $data = ($data);
            dump( $data );
            return $this->render('front/test/gettestRS.html.twig',
                array
                (
                    'data'    => $data,
                    'errorcl' => 'alert-danger'
                )
            );
        }
        $data = "";
        return $this->render('front/test/gettestRS.html.twig',
            array
            (
                'data'    => $data,
                'errorcl' => 'alert-danger',
            )
        );
    }

    /**
     * @Route("/{_country}/{_locale}/testservices/postTestRSEmail")
     * @param Request $request
     * @return Response
     */

    public function postTestRSEmailAction(Request $request , $form_param)
    {
        $country_id  = $request->get('_country');
        $restClient  = $this->get('app.rest_client')->IsAuthorized(true);
        echo $url    = $request->getLocale().'/api/'.$_POST['url'] ;
        if(!empty($_POST['url']))
        {
            $postData  = $_POST['data'];
            $data = '';
            if($postData != "") {
                $postData = '{ "C_id":"91000153", "field":"email" , "old_value":"sa.aspiresc@gmail.com" , "new_value":"sa.aspiresc@gmail.com", "comments":"testinh"}';
                $data     = $restClient->restPostForm(AppConstant::WEBAPI_URL . $url,$postData , array('Country-Id' => strtoupper($request->get('_country'))));
                dump($data);exit;
            }
            //dump($data);exit;
            return $this->render('front/test/testRS.html.twig',
                array
                (
                    'data'    => $data,
                    'errorcl' => 'alert-danger'
                )
            );
        }
        $data = "";
        return $this->render('front/test/testRS.html.twig',
            array
            (
                'data'    => $data,
                'errorcl' => 'alert-danger',
            )
        );
    }


    /**
     * @Route("/{_country}/{_locale}/testservices/postTestRSName")
     * @param Request $request
     * @return Response
     */

    public function postTestRSNameAction(Request $request , $form_param)
    {
        $country_id  = $request->get('_country');
        $restClient  = $this->get('app.rest_client')->IsAuthorized(true);
        $url = "update_user_name";
        echo $url    = $request->getLocale().'/api/'.$url ;
        if(!empty($url))
        {
            $postData  = $_POST['data'];
            $data = '';
            if($postData != "") {
                $postData = '{ "C_id":"91000153", "field":"cname" , "old_value":"test name" , "new_value":"test name new", "comments":"testing testing tesiting"}';
                $data     = $restClient->restPostForm(AppConstant::WEBAPI_URL . $url,$postData , array('Country-Id' => strtoupper($request->get('_country'))));
                dump($data);exit;
            }
            //dump($data);exit;
            return $this->render('front/test/testRSname.html.twig',
                array
                (
                    'data'    => $data,
                    'errorcl' => 'alert-danger'
                )
            );
        }
        $data = "";
        return $this->render('front/test/testRS.html.twig',
            array
            (
                'data'    => $data,
                'errorcl' => 'alert-danger',
            )
        );
    }

    /**
     * @Route("/{_country}/{_locale}/testservices/chkbrowser")
     * @param Request $request
     * @return Response
     */

    public function chkbrowserAction(Request $request , $form_param)
    {
        echo 'asdf';
        $browserAgent = $_SERVER['HTTP_USER_AGENT'];
        echo $browserAgent;
        echo $date = date('Y-m-d H:i:s');
        exit;
    }





}

