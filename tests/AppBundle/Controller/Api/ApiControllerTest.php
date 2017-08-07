<?php
/**
 * Created by PhpStorm.
 * User: s.aman
 * Date: 8/2/17
 * Time: 4:54 PM
 */
namespace Test\AppBundle\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ApiControllerTest extends WebTestCase
{

    public function testApi(){
        $client = $this->createClient();
        $client->request(
            'POST',
            $this->GetUrl('salem_khan')
        );

        dump($client->getResponse()->isServerError());
    }

    private function GetUrl($path){
        return sprintf('/sa/en/api/%s.json', $path);
    }
}