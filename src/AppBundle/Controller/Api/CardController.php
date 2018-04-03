<?php
/**
 * Created by PhpStorm.
 * User: s.aman
 * Date: 25/03/2018
 * Time: 4:15 PM
 */

namespace AppBundle\Controller\Api;
use AppBundle\Exceptions\RestServiceFailedException;
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
     * value may be one of the following
     *  <ul>
     *      <li>InValidCard = 100</li>
     *      <li>CardExist = 101</li>
     *      <li>EmailExist = 102</li>
     *      <li>PendingRequest = 103</li>
     *      <li>ServicesNotAvailable = 104</li>
     *      <li>Error = 105</li>
     *      <li>NewCard = 200</li>
     *      <li>OldCard = 201</li>
     *  </ul>
     */
    public function postIs_card_or_email_existAction(Request $request)
    {

        try {
            $post = $request->request->all();

            if (key_exists('email', $post) && key_exists('card', $post)) {

                $user = $this->getDoctrine()->getManager()->getRepository('AppBundle:User')->findOneBy(['iktCardNo' => $post['card']]);
                if ($user != null) {
                    return $this->handleView($this->view(["Value" => "101"], Response::HTTP_OK));
                }

                $user = $this->getDoctrine()->getManager()->getRepository('AppBundle:User')->findOneBy(['email' => $post['email']]);
                if ($user != null) {
                    return $this->handleView($this->view(["Value" => "102"], Response::HTTP_OK));
                }

                //check card status
                $cardStatus = $this->get('app.services.iktissab_card_service')->getCardStatus($post['card']);

                if ($cardStatus['success'] && $cardStatus['status'] == "1" && (strtolower($cardStatus['data']['cust_status']) == "new" || strtolower($cardStatus['data']['cust_status']) == "distributed")) {

                    $staging = $this->get('app.services.iktissab_card_service')->isCardInStaging($post['card']);

                    if ($staging['success'] == true) {
                        return $this->handleView($this->view(["Value" => "103"], Response::HTTP_OK));
                    } else {
                        return $this->handleView($this->view(["Value" => "200"], Response::HTTP_OK));
                    }
                    //todo: add card status condition here
                }
                else {
                    return $this->handleView($this->view(["Value" => "201"], Response::HTTP_OK));
                }

            }
            return $this->handleView($this->view(["Value"=>'100'], Response::HTTP_BAD_REQUEST));
        }
        catch(RestServiceFailedException $exception){
            return $this->handleView($this->view(["Value" => "104"], Response::HTTP_OK));
        }
        finally{
            return $this->handleView($this->view(["Value" => "105"], Response::HTTP_OK));
        }
    }

    public function getCardStatus(){

    }

}