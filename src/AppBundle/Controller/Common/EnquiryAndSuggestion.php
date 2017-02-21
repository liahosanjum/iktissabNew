<?php
/**
 * Created by PhpStorm.
 * User: s.aman
 * Date: 2/4/17
 * Time: 10:47 AM
 */

namespace AppBundle\Controller\Common;


use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Swift_Message;
use AppBundle\AppConstant;
use Symfony\Component\HttpFoundation\Request;


class EnquiryAndSuggestion
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

    /**
     * @param \AppBundle\Entity\EnquiryAndSuggestion $entity
     * @return EnquiryAndSuggestion
     */
    public function save(\AppBundle\Entity\EnquiryAndSuggestion $entity , $data)
    {
        $this->container->get('swiftmailer.mailer');
        $request = new Request();
        $entity->setUser_ip($this->getIP($request));
        $this->em->persist($entity);
        $this->em->flush();
        if ($entity->getId()) {
            $this->email($entity, $data);
            return true;
        }
        else{
            return false;
        }
    }

    public function email(\AppBundle\Entity\EnquiryAndSuggestion $entity , $data){

        $message = \Swift_Message::newInstance();
        $request = new Request();
        $entity->getEmail();
       

        $message->addTo($entity->getEmail());
            foreach($data['result'] as $email_list){
                $message->addCc($email_list['email']);
            }
            $message->addFrom($this->container->getParameter('mailer_user'))
            ->setSubject(AppConstant::EMAIL_SUBJECT)
            ->setBody(
                $this->container->get('templating')->render('enquiries_and_suggestions_en.html.twig', ['name' => $entity->getName()
                    , 'email' => $entity->getEmail()
                    , 'job' => $entity->getJob()
                    , 'mobile' => $entity->getMobile()
                    , 'reason' => $entity->getReason()
                    , 'country' => $entity->getCountry()
                    , 'comments' => $entity->getComments()
                ]),
                'text/html'
            );

        $this->container->get('mailer')->send($message);
        return $this->container->get('activation_thanks', array('_locale' => 'en', '_country' => 'sa'));


    }
    private function getIP(Request $request)
    {
        return $_SERVER['REMOTE_ADDR'];
    }
    

}