<?php
/**
 * Created by PhpStorm.
 * User: s.aman
 * Date: 25/03/2018
 * Time: 4:15 PM
 */

namespace AppBundle\Controller\Api;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class CardController
 * @Rest\RouteResource("card", pluralize=false)
 * @package AppBundle\Controller\Api
 */
class CardController extends Api
{
    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function postIs_card_or_email_existAction(Request $request)
    {

        $post = $request->request->all();

        if(key_exists('email', $post) && key_exists('card', $post)){

            $user = $this->getDoctrine()->getManager()->getRepository('AppBundle:User')->findOneBy(['iktCardNo'=>$post['card']]);
            if($user != null){
                return $this->handleView($this->view([ "Value"=>"CardExist"], Response::HTTP_OK));
            }

            $user = $this->getDoctrine()->getManager()->getRepository('AppBundle:User')->findOneBy(['email'=>$post['email']]);
            if($user != null){
                return $this->handleView($this->view(["Value"=>"EmailExist"], Response::HTTP_OK));
            }

            //check card status
            $cardStatus = $this->get('app.services.iktissab_card_service')->getCardStatus($post['card']);
            if ($cardStatus['success'] && $cardStatus['status'] == "1") {
                if ( strtolower($cardStatus['data']['cust_status']) == "new" || strtolower($cardStatus['data']['cust_status']) == "distributed") {
                    //check in staging before going to form
                    $staging = $this->get('app.services.iktissab_card_service')->isCardInStaging($post['card']);

                if ($staging['success'] == true) {
                    return $this->handleView($this->view(["Value"=>"PendingRequest"], Response::HTTP_OK));
                }
                else {
                    return $this->handleView($this->view(["Value"=>"NewCard"], Response::HTTP_OK));
                }
              }
                else {
                    return $this->handleView($this->view(["Value"=>"OldCardCard"], Response::HTTP_OK));
                }

            }
            else {
                return $this->handleView($this->view(["Value"=>"OldCardCard"], Response::HTTP_OK));
            }

            return $this->handleView($this->view(["Value"=>"InValidCard"], Response::HTTP_OK));

        }
        return $this->handleView($this->view([], Response::HTTP_BAD_REQUEST));
    }

    public function getCardStatus(){

    }

}