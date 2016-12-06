<?php
/**
 * Created by PhpStorm.
 * User: abdulali
 * Date: 12/4/16
 * Time: 9:47 AM
 */

namespace AppBundle\Controller;

use FOS\RestBundle\Controller\Annotations\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class AccountController extends Controller
{

    /**
     * @Route("/account/home", name="account_home")
     */
    public function myAccountAction()
    {
        echo "This is my account function";
        return $this->render('default/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.root_dir') . '/..') . DIRECTORY_SEPARATOR,
        ]);
    }

    /**
     * @Route("/account/loginsuccess", name="loginsuccess")
     */
    public function loginSuccessAction()
    {
        $user = $this->getUser();
        $loginLog = $this->get('app.login_login');
        $loginLog->logEvent($user->getIktCardNo());

    }
}