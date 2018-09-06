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
        $url = $request->getLocale().'/api/'.$url."/is_ssn_used.json" ;
        if(!empty($_POST))
        {
            $data = $restClient->restGetForm(AppConstant::WEBAPI_URL . $url , array('Country-Id' => $country_id));
            return $this->render('front/test/gettestRS.html.twig', array('data' => $data, 'errorcl' => 'alert-danger'));
        }
        $data = "";
        return $this->render('front/test/gettestRS.html.twig',
            array ( 'data' => $data, 'errorcl' => 'alert-danger')
        );
    }

    /**
     * @Route("/{_country}/{_locale}/testservices/getCardStatus")
     * @param Request $request
     * @return Response
     */

    public function getCardStatusAction(Request $request , $form_param)
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
        $url = $request->getLocale().'/api/'.$url."/card_status.json" ;
        if(!empty($_POST))
        {
            $data = $restClient->restGetForm(AppConstant::WEBAPI_URL . $url , array('Country-Id' => $country_id));
            return $this->render('front/test/getcardstatus.html.twig', array('data' => $data, 'errorcl' => 'alert-danger'));
        }
        $data = "";
        return $this->render('front/test/getcardstatus.html.twig',
            array ( 'data' => $data, 'errorcl' => 'alert-danger')
        );

        // -- values for new card registration
        /*
         {"C_id":"50000250","cname":"test test","area":"test","city_no":"2","mobile":"00201211111111","email":"sa50000250@gmail.com",
        "nat_no":9,"Marital_status":"S","ID_no":"12231121222455","job_no":"1","gender":"M","pur_grp":"3","birthdate":"2002-06-06 12:00:00","pincode":2949,"source":"W",
        "browser":"Mozilla-5.0 (Macintosh; Intel Mac OS X 10.13; rv:46.0) Gecko-20100101 Firefox-46.0"}
         */

    }





}

