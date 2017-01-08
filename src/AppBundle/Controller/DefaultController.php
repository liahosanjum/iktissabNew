<?php

namespace AppBundle\Controller;

use AppBundle\AppConstant;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\HttpFoundation\Cookie;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="landingpage")
     */
    public function indexAction(Request $request)
    {
        $cookieLocale = $request->cookies->get(AppConstant::COOKIE_LOCALE);
        $cookieCountry = $request->cookies->get(AppConstant::COOKIE_COUNTRY);
        

        if(isset($cookieLocale) && $cookieLocale <> '' && isset($cookieCountry) && $cookieLocale <> ''){
            return $this->redirect($this->generateUrl('homepage', array('_country'=>$cookieCountry,'_locale'=>$cookieLocale)));
        }

        return $this->render('default/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.root_dir') . '/..') . DIRECTORY_SEPARATOR,
        ]);
    }

    /**
     * @Route("/{_country}/{_locale}/", name="homepage")
     * @param Request $request
     */
    public function homepageAction(Request $request)
    {
        $response = new Response();
        $locale = $request->getLocale();
        $country = $request->get('_country');

        $response->headers->setCookie(new Cookie(AppConstant::COOKIE_LOCALE, $locale,time()+AppConstant::COOKIE_EXPIRY,'/',null,false,false));
        $response->headers->setCookie(new Cookie(AppConstant::COOKIE_COUNTRY, $country,time()+AppConstant::COOKIE_EXPIRY,'/',null,false,false));
        $response->sendHeaders();

        

    }
    /**
     * @Route("/new", name="new")
     */
    public function newAction(Request $request)
    {
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());

        $serializer = new Serializer($normalizers, $encoders);
        echo "i am in the new action";

        $person = array('name' => 'Abdul basit',
            'age' => '44',
            'address' => 'office colony '
        );
        $jsonContent = $serializer->serialize($person, 'json');
        echo $jsonContent;
        return new Response();

    }
}

