<?php
/**
 * Created by PhpStorm.
 * User: abdulali
 * Date: 12/4/16
 * Time: 9:47 AM
 */

namespace AppBundle\Controller;

use AppBundle\AppConstant;
use FOS\RestBundle\Controller\Annotations\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Serializer;


class AccountController extends Controller
{

    /**
     * @Route("/{_country}/{_locale}/account/home", name="account_home")
     */
    public function myAccountAction(Request $request)
    {
        $restClient = $this->get('app.rest_client');

        if(!$this->get('session')->get('iktUserData')) {
            $url = $request->getLocale() . '/api/' . $this->getUser()->getIktCardNo() . '/userinfo.json';
            $data = $restClient->restGet(AppConstant::WEBAPI_URL.$url, array('Country-Id' => strtoupper($request->get('_country'))));
            if($data['success'] == "true") {
                $this->get('session')->set('iktUserData', $data['user']);
            }
        }
        $iktUserData = $this->get('session')->get('iktUserData');
        var_dump($iktUserData);
        return $this->render('/account/home.twig',
            array('iktData' => $iktUserData)
            );
    }


}