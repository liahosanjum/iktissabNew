<?php
/**
 * Created by PhpStorm.
 * User: s.aman
 * Date: 6/1/17
 * Time: 3:14 PM
 */

namespace AppBundle\Controller\Common;


use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

class NotificationSubscription
{

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var EntityManager
     */
    private $em;
    /**
     * EnquiryAndSuggestion constructor.
     * @param ContainerInterface $container
     * @param EntityManager $em
     */
    public function __construct(ContainerInterface $container, EntityManager $em)
    {
        $this->container = $container;
        $this->em = $em;
    }

    public function saveSubscription(){

    }

    public function deleteDevice(){

    }
}