<?php
/**
 * Created by PhpStorm.
 * User: abdulali
 * Date: 12/28/16
 * Time: 12:44 PM
 */

namespace AppBundle\Controller;


use AppBundle\AppConstant;
use AppBundle\Entity\User;
use AppBundle\Form\ActivateCardone;
use AppBundle\Form\IktReg;
use FOS\RestBundle\Controller\Annotations\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;

class ActivationController extends Controller
{
    /**
     * @Route("/{_country}/{_locale}/card-activation", name="card_activation")
     */
    public function cardActivationAction(Request $request)
    {

        echo $request->getLocale();
        echo "card activation action";
        $error = array('success' => true);
        $form = $this->createForm(ActivateCardone::class);
        $form->handleRequest($request);
        $pData = $form->getData();
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->checkOnline($pData);
                // check if card valid from local/office ikt database
                $scenerio = $this->checkScenerio($pData['iktCardNo']);
                // proceed to next step with full form registration
                $this->get('session')->set('scenerio', $scenerio);
                $this->get('session')->set('iktCardNo', $pData['iktCardNo']);
                $this->get('session')->set('email', $pData['email']);
                if ($this->get('session')->get('scenerio') == AppConstant::IKT_REG_SCENERIO_1) {
                    // proceed to full form registration
                    return $this->redirectToRoute('customer_information', array('_locale' => $request->getLocale(), '_country' => $request->get('_country')));
                } elseif ($this->get('session')->get('scenerio') == AppConstant::IKT_REG_SCENERIO_2) {
                    echo "inside elseif";
                    // proceed to only update email page to login
                } else {
                    echo "inside else";
                }


            } catch (Exception $e) {
                $error['success'] = false;
                $error['message'] = $e->getMessage();
            }
        }
        return $this->render('/activation/activation.twig',
            array(
                'form' => $form->createView(),
                'error' => $error,
            )
        );
    }

    public function checkOnline($pData)
    {
        $em = $this->getDoctrine()->getEntityManager();
        // First step to check the  email in mysql db
        $checkEmail = $em->getRepository('AppBundle:User')->findOneByEmail($pData['email']);
        if (!is_null($checkEmail)) {
            Throw new Exception($this->get('translator')->trans('You have registered previously. If you have forgot password please click this link.'), 1);
        }
        // Second step to check the  ikt card in mysql db
        $checkIktCard = $em->getRepository('AppBundle:User')->find($pData['iktCardNo']);
        if (!is_null($checkIktCard)) {
            Throw new Exception($this->get('translator')->trans('This Card is already registered.'), 1);
        }

    }

    public function checkScenerio($iktCardNo)
    {
        $restClient = $this->get('app.rest_client');
        $request = Request::createFromGlobals();
        $locale = $request->getLocale();
        $url = $request->getLocale() . '/api/' . $iktCardNo . '/card_status.json';
        $data = $restClient->restGet(AppConstant::WEBAPI_URL.$url, array('Country-Id' => strtoupper($request->get('_country'))));
//       echo "here the data is"; var_dump($data); die('---');
        if ($data['success'] == false) {
            Throw new Exception($this->get('translator')->trans('Iktissab Card is invalid.'), 1);
        } else {
            if ($data['data']['cust_status'] == 'Active' || $data['data']['cust_status'] == 'In-Active') {
                return AppConstant::IKT_REG_SCENERIO_2;
            } elseif ($data['data']['cust_status'] == 'NEW' || $data['data']['cust_status'] == 'Distributed') {
                return AppConstant::IKT_REG_SCENERIO_1;
            }
        }
        return true;

    }


    /**
     * @Route("/{_country}/{_locale}/customer-information", name="customer_information")
     *
     */
    public function customerInformationAction(Request $request)
    {
        // check referal
        if (!$this->isReferalValid('card_activation')) {
//            return $this->redirectToRoute('card_activation',array('_locale'=> $request->getLocale(),'_country' => $request->get('_country')));
        }
        // get all cities
        $restClient = $this->get('app.rest_client');
        $url = $request->getLocale() . '/api/get_cities.json';
        $cities = $restClient->restGet(AppConstant::WEBAPI_URL.$url, array('Country-Id' => strtoupper($request->get('_country'))));
        $citiesArranged = array();
        foreach ($cities['data'] as $key => $value) {
            $citiesArranged[($request->getLocale() == 'en') ? $value['ename'] : $value['aname']] = $value['city_no'];
        }
        //TODO::get jobs once the api for jobs is created for now lets add cities
        //TODO::get all regions and display in javascript array and then upon change of city update the regions accordingly getallcities API call is not ready yet
        $pData = array('iktCardNo' => $this->get('session')->get('iktCardNo'), 'email' => $this->get('session')->get('email'));
        $form = $this->createForm(IktReg::class, $pData, array(
                'additional' => array(
                    'locale' => $request->getLocale(),
                    'country' => $request->get('_country'),
                    'cities' => $citiesArranged,
                    'jobs' => $citiesArranged,
                    'areas' => $citiesArranged

                )
            )
        );
        $form->handleRequest($request);
        $pData = $form->getData();
        $error = array('success' => true);
        if ($form->isValid() && $form->isSubmitted()) {
            try {
                // check iqama in local SQl db
//                $this->checkIqamaLocal($pData['iqama']);
                // save the provided data to session
                var_dump($pData); echo "<hr />";
                $newCustomer = array(
                    "C_id" => $pData['iktCardNo'],
                    "cname" => $pData['fullName'],
                    "street" => $pData['street'],
                    "area" => $pData['area_no'],
                    "houseno" => $pData['houseno'],
                    "pobox" => $pData['pobox'],
                    "zip" => $pData['zip'],
                    "city_no" => $pData['city_no'],
                    "tel_home" => $pData['tel_home'],
                    "tel_office" => $pData['tel_office'],
                    "mobile" => $pData['mobile'],
                    "email" => $pData['mobile'],
                    "nat_no" => $pData['nationality']->getId(),
                    "Marital_status" => $pData['maritial_status'],
                    "ID_no" => $pData['iqama'],
                    "job_no" => $pData['job_no'],
                    "gender" => $pData['gender'],
                    "pur_grp" => $pData['pur_group'],
                    "additional_mobile" => '',
                    "G_birthdate" => $pData['dob']->date,
                    "pincode" => mt_rand(1000, 9999),
                );
                $this->get('session')->set('new_customer',$newCustomer);

                //send sms code
                $otp = rand(111111,999999);
                $msgID = rand(19,990);
                $message = "new messss $otp";
                
                $url = 'HTTP://www.mobily.ws/api/msgSend.php';
                $payload = "mobile=".$this->getParameter('mobily_user')."&password=".$this->getParameter('mobily_pass')."&numbers=966569858396"."&sender=Iktissab&msg=ASDF989000welcomewelcome&timeSend=0&dateSend=0&applicationType=68&domainName=".$_SERVER['SERVER_NAME']."&msgId=".$msgID."&deleteKey=152485&lang=3";
                $payload = "mobile=othaim&password=0557554443&numbers=966569858396&sender=Iktissab&msg=ty989000welcomewelcome&timeSend=0&dateSend=0&applicationType=68&domainName=othaimmarkets.com&msgId=3183&deleteKey=109485&lang=3";
                echo "<br /> payload == ". $payload;
                $sms = $restClient->restPost($url, $payload,array());
                var_dump($sms);
                echo "mobily url is".$url;
                var_dump($sms);
                die('---');


//                $url = $request->getLocale() . '/api/add_new_user.json';
//                var_dump($newCustomer);
//                $cData = json_encode($newCustomer);
//                $saveCustomer = $restClient->restPost(AppConstant::WEBAPI_URL.$url, $cData, array('Country-Id' => strtoupper($request->get('_country'))));
//                var_dump($saveCustomer);
                die('---');

            } catch (Exception $e) {
                $error['success'] = false;
                $error['message'] = $e->getMessage();
            }


        }
        return $this->render('/activation/customer_information.twig',
            array('form' => $form->createView(),
                'error' => $error)
        );


    }

    function isReferalValid($url)
    {
        $request = Request::createFromGlobals();
        $referer = $request->headers->get('referer');
        $baseUrl = $request->getBaseUrl();
        $lastPath = substr($referer, strpos($referer, $baseUrl) + strlen($baseUrl));
        $matcher = $this->get('router')->getMatcher();
        $parameters = $matcher->match($lastPath);
        if ($parameters['_route'] != $url) {
            return false;
        }
        return true;

    }

    /**
     * @Route("/checkiqama")
     */
    function checkiqamaAction()
    {                       // function to validate iqama this code will be implemented in the FormType as callback validation
        $this->checkIqamaLocal('2374777710');
        $iqama = '2309121604';
        $iqama = '1001744588';
        $evenSum = 0;
        $oddSum = 0;
        for ($i = 0; $i < strlen($iqama); $i++) {
            $temp = '';
            if ($i % 2) { // odd number

                $oddSum = $oddSum + $iqama[$i];

            } else {
                //even
                $multE = $iqama[$i] * 2;
                if (strlen($multE) > 1) {
                    $temp = (string)$multE;
                    $evenSum = $evenSum + ($temp[0] + $temp[1]);
                } else {
                    $evenSum = $evenSum + $multE;
                }
            }


        }
        $entireSum = $evenSum + $oddSum;
        echo "entire sum is" . $entireSum;
        echo $entireSum % 10;
        if (($entireSum % 10) == 0) {
            echo "Iqama is valid";
        } else {
            echo "invalid iqama";
        }


        die('---');
    }

    function checkIqamaLocal($iqama)
    { // iqama validation in local MSSQL db
        $restClient = $this->get('app.rest_client');
        $request = Request::createFromGlobals();
        $url = $request->getLocale() . '/api/' . $iqama . '/is_ssn_used.json';
        $data = $restClient->restGet(AppConstant::WEBAPI_URL.$url, array('Country-Id' => strtoupper($request->get('_country'))));
        if ($data['success'] == false) { // this iqama is not registered previously
            return true;
        } else {
            Throw new Exception($this->get('translator')->trans($data['message']), 1);
        }


    }

    /**
     * @Route("/testsms")
     */
    function testsmsAction()
    {
        $restClient = $this->get('app.rest_client');
        $payload = "mobile=Othaim&password=0557554443&numbers=966569858396&sender=Iktissab&msg=rrereewelcome&timeSend=0&dateSend=0&applicationType=68&domainName=othaimmarkets.com&msgId=3813&deleteKey=101115&lang=3";
        $url = 'http://www.mobily.ws/api/msgSend.php?'.$payload;
        echo "<br /> payload == ". $payload;
        $sms = $restClient->restGet($url,array());
        var_dump($sms);
    }

}