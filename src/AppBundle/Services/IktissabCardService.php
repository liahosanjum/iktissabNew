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
        $result = $client->Get($card . '/userinfo');
        return $result;
    }
    public function saveUser($data){
        
    }

    public function getCitiesAreasAndJobs()
    {
        $client = $this->container->get('app.services.iktissab_rest_service');
        $result = $client->Get('cities_areas_and_jobs');

        return $result;
    }

    public function updateCard($post){
        $client = $this->container->get('app.services.iktissab_rest_service');
        return $client->Post($post, "/");
    }
}