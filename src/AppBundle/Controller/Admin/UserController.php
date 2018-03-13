<?php
/**
 * Created by PhpStorm.
 * User: abdulali
 * Date: 2/19/17
 * Time: 8:55 AM
 */
namespace AppBundle\Controller\Admin;

use AppBundle\AppConstant;
use AppBundle\Controller\Common\FunctionsController;
use AppBundle\Entity\User;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\RedirectResponse;


class UserController extends Controller
{
    /**
     * @Route("/admin/users2_old", name= "admin_users2_old")
     * @Method("GET")
     * @Cache(smaxage="10")
     */

    public function adminUsersController(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY'))
        {
            return $this->redirectToRoute('admin_admin');
        }
        $em = $this->getDoctrine()->getManager();
        $ikt = $request->query->get('ikt', '');
        $email = $request->query->get('email', '');
        $page = $request->query->get('page', 1);
        /***/
        if($email != "" and $email != null) {


            if (!preg_match("/[-0-9a-zA-Z.+_]+@[-0-9a-zA-Z.+_]+.[a-zA-Z]{2,4}/", $email)) {
                //Email address is invalid.
                $email = "";
                $message = 'invalid email address';
            }
            else{
                $message = '';
            }
        }

        else if($ikt != "") {
            if (!preg_match("/^([0-9]){8}$/", $ikt)) {
                $ikt = "";
                $message = 'invalid iktissab number';
            }
             else{
                 $message = '';
             }
        }



            /***/

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
                    'email' => $email,
                    'message' => $message,
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
     * @Route("admin/details/{ikt}", name="iktissab_details_admin")
     */
    public function iktissabDetailsAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY'))
        {
            return $this->redirectToRoute('admin_admin');
        }
        $funcController = new FunctionsController();
        $funcController->setContainer($this->container);
        $tokenStorage  = $this->get('security.token_storage');
        $roleName = $tokenStorage->getToken()->getUser()->getRoleName();
        if(!$funcController->isValidRule('MANAGE_USER'))
        {
            return $response = new RedirectResponse($this->generateUrl('admin_accessdenied'));
        }





        $ikt = $request->get('ikt');
        $partialUser = $this->getDoctrine()->getManager()->getRepository("AppBundle:User")->find($ikt);
        if(!empty($partialUser))
        {
            try
            {
                $restClient = $this->get('app.rest_client')->IsPartialUser($partialUser->getEmail(), $partialUser->getPassword());
                $url = 'en/api/' . $ikt . '/userinfo.json';
                //exit;
                if ($ikt[0] == '5') {
                    $country = 'eg';
                } else {
                    $country = 'sa';
                }
                // echo AppConstant::WEBAPI_URL.$url;
                $data = $restClient->restGetForm(AppConstant::WEBAPI_URL . $url, array('Country-Id' => strtoupper($country)));


                if ($data['success'] == "true") {
                    return $this->render('/admin/cms/userdetails.html.twig',
                        array('iktData' => $data['user'],
                            'message' => '',
                        )
                    );


                } else {
                    $message = 'unable to load data';
                    return $this->render('/admin/cms/userdetails.html.twig',
                        array('iktData' => $data['user'],
                            'message' => $message,
                        )
                    );

                }
            }
            catch (\Exception $e){
                $message = 'unable to load data';
                return $this->render('/admin/cms/userdetails.html.twig',
                    array('iktData' => $data['user'],
                        'message' => $message,
                    )
                );
            }

        }else{
            $message = 'unable to load data';
            return $this->render('/admin/cms/userdetails.html.twig',
                array('iktData' => "",
                    'message' => $message,
                    )
            );
        }
    }

    /**
     * @Route("/admin/activity-logs11", name= "admin_activitylogs11")
     *
     */

    public function adminLogsAction(Request $request)
    {
            $ikt = $request->query->get('ikt', '');
            $action = $request->query->get('action', '');
            $email = $request->query->get('email', '');

            if ($email != "" and $email != null) {
                if (!preg_match("/[-0-9a-zA-Z.+_]+@[-0-9a-zA-Z.+_]+.[a-zA-Z]{2,4}/", $email)) {
                    //Email address is invalid.
                    $email = "";
                    $message = 'invalid email address';
                } else {
                    $message = '';
                }
            } else if ($ikt != "") {
                if (!preg_match("/^([0-9]){8}$/", $ikt)) {
                    $ikt = "";
                    $message = 'invalid iktissab number';
                } else {
                    $message = '';
                }
            } else if ($action != "") {
                if (!preg_match("/^([a-zA-Z])*$/", $action)) {
                    $action = "";
                    $message = 'invalid action ';
                } else {
                    $message = '';
                }
            }


            $em = $this->getDoctrine()->getManager();

            //$ikt = $request->query->get('ikt', '');
            //$action = $request->query->get('action', '');
            //$email = $request->query->get('email', '');

            $page = $request->query->get('page', 1);
            $logs = $em->getRepository('AppBundle:ActivityLog')->searchActivityLog($ikt, $action, $email);
            $pager = new Pagerfanta(new DoctrineORMAdapter($logs, true));
            $pager->setMaxPerPage(User::NUM_ITEMS);
            $routeGenerator = function ($page) {
                return '?pager=' . $page . '&email=eg';

            };



            if ($pager->haveToPaginate())
                $pager->setCurrentPage($page);
            return $this->render(
                '/admin/cms/activitylogs.html.twig',
                array(
                    'logs'    => $pager,
                    'ikt'     => $ikt,
                    'action'  => $action,
                    'email'   => $email,
                    'message' => $message,

                )
            );




    }


    /**
    * @Route("/admin/activity-logsdetails/{ikt}", name= "admin_activitylogsdetails")
    */
    public function activityLogsDetailAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY'))
        {
            return $this->redirectToRoute('admin_admin');
        }

        $funcController = new FunctionsController();
        $funcController->setContainer($this->container);

        if(!$funcController->isValidRule('MANAGE_LOGS')) {
            return $response = new RedirectResponse($this->generateUrl('admin_accessdenied'));
        }



        $id  = $request->get('ikt');
        $em  = $this->getDoctrine()->getManager();

        // $ikt = $request->query->get('ikt', '');
        // $action = $request->query->get('action', '');
        // $email = $request->query->get('email', '');
        $logs = $em->getRepository('AppBundle:ActivityLog')->findBy(array('id' => $id ));

        if(!empty($logs)) {
            $log_data = unserialize($logs[0]->getActionData());
            $i = 0;
            $log_other_data['actionType'] = $logs[0]->getActionType();
            $log_other_data['id'] = $logs[0]->getId();
            $log_other_data['actionDate'] = date('Y-m-d H:i:s',$logs[0]->getActionDate());
            $log_other_data['Iktissab'] = $logs[0]->getIktCardNo();
        }
        $session_log = "";
        if(!empty($log_data)){
            if(!empty($log_data['session']))
            {
                $session_log = $log_data['session'];
            }
        }

        // var_dump($session_log);
        return $this->render(
            '/admin/cms/activitylogs2-details.html.twig',
            array(
                'logs'          => $log_data,
                'logs_other'    => $log_other_data,
                'logs_session'  => $session_log,
            )
        );

    }


    /**
     * @Route("/admin/activity-logs", name= "admin_activitylogs")
     */
    public function activityLogsAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY'))
        {
            return $this->redirectToRoute('admin_admin');
        }

        $funcController = new FunctionsController();
        $funcController->setContainer($this->container);

        if(!$funcController->isValidRule('MANAGE_LOGS')) {
            return $response = new RedirectResponse($this->generateUrl('admin_accessdenied'));
        }
        
        
        if(!empty($_POST))
        {
            if($funcController->checkCsrfToken($_POST['token'] , 'admin_log'))
            {

                if($_POST['ikt'] != "")
                {
                    $ikt = $_POST['ikt'];

                }else
                {

                    $ikt = "";
                }

                if($_POST['action'] != "")
                {
                    $action = $_POST['action'];
                }else
                {
                    $action = "";
                }

                if($_POST['email'] != "")
                {
                    $email = $_POST['email'];
                }else
                {
                    $email = "" ;
                }

                if ($email != "" and $email != null) {

                    if (!preg_match("/[-0-9a-zA-Z.+_]+@[-0-9a-zA-Z.+_]+.[a-zA-Z]{2,4}/", $email)) {
                        //Email address is invalid.
                        $email = "";
                        $message = 'invalid email address';
                    } else {
                        $message = '';
                    }
                } else if ($ikt != "") {

                    if (!preg_match("/^([0-9]){8}$/", $ikt)) {
                        $ikt = "";
                        $message = 'invalid iktissab number';
                    } else {
                        $message = '';
                    }
                } else if ($action != "") {
                    if (!preg_match("/^([a-zA-Z])*$/", $action)) {
                        $action = "";
                        $message = 'invalid action ';
                    } else {
                        $message = '';
                    }
                }


                $em = $this->getDoctrine()->getManager();

                //$ikt = $request->query->get('ikt', '');
                //$action = $request->query->get('action', '');
                //$email = $request->query->get('email', '');

                $page = $request->query->get('page', 1);
                $logs = $em->getRepository('AppBundle:ActivityLog')->searchActivityLog($ikt, $action, $email);
                $pager = new Pagerfanta(new DoctrineORMAdapter($logs, true));
                $pager->setMaxPerPage(User::NUM_ITEMS);
                $routeGenerator = function ($page) {
                    return '?pager=' . $page . '&email=eg';

                };


                $funcController->setCsrfToken('admin_log');
                $session = new Session();
                $token = $session->get('admin_log');

                if ($pager->haveToPaginate())
                    $pager->setCurrentPage($page);
                return $this->render(
                    '/admin/cms/activitylogs2.html.twig',
                    array(
                        'logs' => $pager,
                        'ikt' => $ikt,
                        'action' => $action,
                        'email' => $email,
                        'message' => $message,
                        'token' => $token,

                    )
                );
            }
            else
            {
                $this->get('security.token_storage')->setToken(null);
                $response = new RedirectResponse($this->generateUrl('admin_admin'));
                return $response;
            }

        }
        else {


            if($_POST['token'] != "")
            {
                $ikt    = $_POST['token'];
            }
            else
            {
                $ikt = "";
            }

            if($_POST['action'] != "")
            {
                $action = $_POST['action'];
            }
            else
            {
                $action = "";
            }

            if($_POST['email'] != "")
            {
                $email = $_POST['email'];
            }
            else
            {
                $email = "" ;
            }

            if ($email != "" and $email != null) {
                if (!preg_match("/[-0-9a-zA-Z.+_]+@[-0-9a-zA-Z.+_]+.[a-zA-Z]{2,4}/", $email)) {
                    //Email address is invalid.
                    $email = "";
                    $message = 'invalid email address';
                } else {
                    $message = '';
                }
            } else if ($ikt != "") {
                if (!preg_match("/^([0-9]){8}$/", $ikt)) {
                    $ikt = "";
                    $message = 'invalid iktissab number';
                } else {
                    $message = '';
                }
            } else if ($action != "") {
                if (!preg_match("/^([a-zA-Z])*$/", $action)) {
                    $action = "";
                    $message = 'invalid action ';
                } else {
                    $message = '';
                }
            }


            $em = $this->getDoctrine()->getManager();

            //$ikt = $request->query->get('ikt', '');
            //$action = $request->query->get('action', '');
            //$email = $request->query->get('email', '');

            $page = $request->query->get('page', 1);
            $logs = $em->getRepository('AppBundle:ActivityLog')->searchActivityLog($ikt, $action, $email);
            $pager = new Pagerfanta(new DoctrineORMAdapter($logs, true));
            //var_dump($pager);

            $pager->setMaxPerPage(User::NUM_ITEMS);

            $routeGenerator = function ($page) {
                return '?pager=' . $page . '&email=eg';
            };

            $funcController->setCsrfToken('admin_log');
            $session = new Session();
            $token = $session->get('admin_log');
            if ($pager->haveToPaginate())
                $pager->setCurrentPage($page);
            return $this->render(
                '/admin/cms/activitylogs2.html.twig',
                array(
                    'logs'    => $pager,
                    'ikt'     => $ikt,
                    'action'  => $action,
                    'email'   => $email,
                    'message' => $message,
                    'token'   => $token,
                )
            );
        }
    }





    /**
     * @Route("/admin/users", name= "admin_users")
     *
     */
    public function adminUsersAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY'))
        {
            return $this->redirectToRoute('admin_admin');
        }

        $funcController = new FunctionsController();
        $funcController->setContainer($this->container);
        $tokenStorage  = $this->get('security.token_storage');
        $roleName = $tokenStorage->getToken()->getUser()->getRoleName();
        if(!$funcController->isValidRule('MANAGE_USER'))
        {
            return $response = new RedirectResponse($this->generateUrl('admin_accessdenied'));
        }


        $em = $this->getDoctrine()->getManager();
        if(!empty($_POST)) {

            if($funcController->checkCsrfToken($_POST['token'] , 'admin_user_log')) {
                if ($_POST['ikt'] != "") {
                    $ikt = $_POST['ikt'];
                } else {
                    $ikt = "";
                }


                if ($_POST['email'] != "") {
                    $email = $_POST['email'];
                } else {
                    $email = "";
                }

                if ($_POST['page'] != "") {
                    $page = $_POST['page'];
                } else {
                    $page = 1;
                }


                /***/
                if ($email != "" and $email != null) {


                    if (!preg_match("/[-0-9a-zA-Z.+_]+@[-0-9a-zA-Z.+_]+.[a-zA-Z]{2,4}/", $email)) {
                        //Email address is invalid.
                        $email = "";
                        $message = 'invalid email address';
                    } else {
                        $message = '';
                    }
                } else if ($ikt != "") {
                    if (!preg_match("/^([0-9]){8}$/", $ikt)) {
                        $ikt = "";
                        $message = 'invalid iktissab number';
                    } else {
                        $message = '';
                    }
                }
                /***/
                $users = $em->getRepository('AppBundle:User')->searchUsers($ikt, $email);
                $pager = new Pagerfanta(new DoctrineORMAdapter($users, true));
                $pager->setMaxPerPage(User::NUM_ITEMS);
                $routeGenerator = function ($page) {
                    return '?pager=' . $page . '&email=eg';
                };
                if ($pager->haveToPaginate())
                    $pager->setCurrentPage($page);

                $funcController->setCsrfToken('admin_user_log');
                $session = new Session();
                $token   = $session->get('admin_user_log');

                return $this->render(
                    '/admin/cms/users.html.twig',
                    array(
                        'users' => $pager,
                        'ikt' => $ikt,
                        'email' => $email,
                        'message' => $message,
                        'token' => $token,
                    )
                );
            }
            else
            {
                $this->get('security.token_storage')->setToken(null);
                $response = new RedirectResponse($this->generateUrl('admin_admin'));
                return $response;
            }
        }
        else
        {


            if ($_POST['ikt'] != "") {
                $ikt = $_POST['ikt'];

            } else {

                $ikt = "";
            }


            if ($_POST['email'] != "") {
                $email = $_POST['email'];
            } else {
                $email = "";
            }

            if ($_POST['page'] != "") {
                $page = $_POST['page'];
            } else {
                $page = 1;
            }
            /***/
            if ($email != "" and $email != null) {


                if (!preg_match("/[-0-9a-zA-Z.+_]+@[-0-9a-zA-Z.+_]+.[a-zA-Z]{2,4}/", $email)) {
                    //Email address is invalid.
                    $email = "";
                    $message = 'invalid email address';
                } else {
                    $message = '';
                }
            } else if ($ikt != "") {
                if (!preg_match("/^([0-9]){8}$/", $ikt)) {
                    $ikt = "";
                    $message = 'invalid iktissab number';
                } else {
                    $message = '';
                }
            }
            /***/
            $users = $em->getRepository('AppBundle:User')->searchUsers($ikt, $email);
            $pager = new Pagerfanta(new DoctrineORMAdapter($users, true));
            $pager->setMaxPerPage(User::NUM_ITEMS);
            $routeGenerator = function ($page) {
                return '?pager=' . $page . '&email=eg';
            };
            if ($pager->haveToPaginate())
                $pager->setCurrentPage($page);

            $funcController->setCsrfToken('admin_user_log');
            $session = new Session();
            $token   = $session->get('admin_user_log');

            return $this->render(
                '/admin/cms/users.html.twig',
                array(
                    'users'   => $pager,
                    'ikt'     => $ikt,
                    'email'   => $email,
                    'message' => $message,
                    'token'   => $token,
                )
            );
        }

    }


    /**
     * @Route("/admin/accessdenied", name= "admin_accessdenied")
     *
     */
     public function accessDeniedTemplateAction(Request $request)
     {
         $message = 'Access Denied';
         return $this->render('/admin/accessdenied.html.twig',
             array( 'message' => $message)
         );
     }






}