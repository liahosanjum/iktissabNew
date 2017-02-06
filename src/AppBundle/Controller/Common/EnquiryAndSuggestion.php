<?php
/**
 * Created by PhpStorm.
 * User: s.aman
 * Date: 2/4/17
 * Time: 10:47 AM
 */

namespace AppBundle\Controller\Common;


use Doctrine\Common\Persistence\ObjectManager;


final class EnquiryAndSuggestion
{
    /**
     * @param ObjectManager $em
     * @param \AppBundle\Entity\EnquiryAndSuggestion $entity
     */
    public static function save(ObjectManager $em, \AppBundle\Entity\EnquiryAndSuggestion $entity)
    {
        $em->persist($entity);
        $em->flush();
        
        self::email();
    }

    private function email(){

    }
}