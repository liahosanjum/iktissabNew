<?php
/**
 * Created by PhpStorm.
 * User: abdulali
 * Date: 12/1/16
 * Time: 12:00 PM
 */
namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class SecurityController extends Controller
{
    /**
     * @Route("/{_country}/{_locale}/login", name="login")
     */
    public function loginAciton(Request $request){

        if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')){
            // create current user country session
            
            $this->get('session')->set('userSelectedCountry',$request->get('_country'));
            return $this->redirectToRoute('homepage');
        }


        $authenticationUtils = $this->get('security.authentication_utils');

        $error = $authenticationUtils->getLastAuthenticationError();

        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render(':default:login.html.twig', array(
           'last_username' => $lastUsername,
            'error' => $error
        ));
    }

    /**
     * @Route("/admin/admin")
     */
    public function adminAciton(Request $request)
    {
        if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')){
            return $this->redirectToRoute('homepage');
        }


        $authenticationUtils = $this->get('security.authentication_utils');

        $error = $authenticationUtils->getLastAuthenticationError();

        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render(':admin:login.html.twig', array(
            'last_username' => $lastUsername,
            'error' => $error
        ));
    }



    /**
     * @Route("{_country}/{_locale}/account/logout", name="account_logout")
     */
    public function accountLogout(Request $request)
    {
//       die('account logout');
    }
}