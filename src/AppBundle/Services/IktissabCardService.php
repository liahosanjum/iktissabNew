<?php
namespace AppBundle\Services;


use AppBundle\AppConstant;
use AppBundle\Exceptions\RestServiceFailedException;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

class IktissabCardService
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * IktissabCardService constructor.
     * @param EntityManager $em
     * @param ContainerInterface $container
     */
    public function __construct(EntityManager $em, ContainerInterface $container)
    {
        $this->em = $em;
        $this->container = $container;
    }

    /**
     * this method return false if the ssn is not used otherwise return true
     * @param $ssn
     * @return int
     * @throws RestServiceFailedException
     */
    public function isSSNUsed($ssn)
    {

        $client = $this->container->get("app.services.iktissab_rest_service");
        $data = $client->Get($ssn .'/is_ssn_used');
        if (in_array($data['success'], [true, false])) {
            return $data['success'];
        }
        else {
            return AppConstant::INVALID_DATA;
        }

    }

    /**
     * this method return customer and card status
     * @param $card
     * @return mixed|string
     * @throws RestServiceFailedException
     */
    public function getCardStatus($card)
    {
        $client = $this->container->get('app.services.iktissab_rest_service');
        $data = $client->Get($card.'/card_status');

        return $data;
    }

    /**
     * this method return true if card is in staging, false if not and InvalidaData if card is not valid
     * @param $card
     * @return bool|string
     * @throws RestServiceFailedException
     */
    public function isCardInStaging($card)
    {
        $client = $this->container->get('app.services.iktissab_rest_service');
        $data = $client->Get($card.'/is_in_stagging');
        if (in_array($data['success'], [true, false])) {
            return $data['success'];
        }
        else {
            return AppConstant::INVALID_DATA;
        }

    }

    /**
     * @param $data
     * @return mixed|string
     * @throws RestServiceFailedException
     */
    public function saveCard($data)
    {
        $client = $this->container->get('app.services.iktissab_rest_service');
        $result = $client->Post('add_new_user', json_encode($data));

        return $result;
    }

    /**
     * this method return user information from offline iktissab databse
     * @param $card
     * @return mixed|string
     * @throws RestServiceFailedException
     */
    public function getUserInfo($card){
        $client = $this->container->get('app.services.iktissab_rest_service');
        $result = $client->IsAuthorized(true)->Get($card . '/userinfo');
        return $result;
    }
    public function saveUser($data){

    }
    public function updateUserDetails($card, $post){
        $client = $this->container->get('app.services.iktissab_rest_service');
        $result = $client->IsAuthorized(true)->Post('update_user_detail', $post);
        $this->container->get('app.activity_log')->logEvent(AppConstant::ACTIVITY_UPDATE_USERINFO_SUCCESS, $card, $post);
        return $result;
    }
    public function getCitiesAreasAndJobs()
    {
        $client = $this->container->get('app.services.iktissab_rest_service');
        $result = $client->Get('cities_areas_and_jobs');

        return $result;
    }

    public function updateMobile($data){
        $client = $this->container->get('app.services.iktissab_rest_service');
        $result  = $client->IsAuthorized(true)->Post("update_user_mobile", $data);
        return $result;
    }

    public function updateSSN($data){
        $client = $this->container->get('app.services.iktissab_rest_service');
        $result  = $client->IsAuthorized(true)->Post("update_user_ssn", $data);
        return $result;
    }
    public function updateLostCard($data){
        $client = $this->container->get('app.services.iktissab_rest_service');
        $result  = $client->IsAuthorized(true)->Post("update_lost_card", $data);
        return $result;
    }

    public function updateName($data){
        $client = $this->container->get('app.services.iktissab_rest_service');
        $result  = $client->IsAuthorized(true)->Post("update_user_name", $data);
        return $result;
    }

    public function updateEmail($data)
    {
        $client = $this->container->get('app.services.iktissab_rest_service');
        $result = $client->IsAuthorized(true)->Post('update_user_email', $data);
        return $result;
    }
    public function updatePassword($data)
    {
        $user = $this->em->getRepository("AppBundle:User")->find($data['card']);
        if($user){
            $user->setPassword(md5($data['password']));
            $this->container->get('app.activity_log')->logEvent(AppConstant::ACTIVITY_UPDATE_PASSWORD_SUCCESS, $data['card'], $data['password']);

            return array('success'=>true);
        }
        return array('success'=>false);
    }
    public function updateCard($post){
        $client = $this->container->get('app.services.iktissab_rest_service');
        return $client->IsAuthorized(true)->Post($post, "/");
    }


}