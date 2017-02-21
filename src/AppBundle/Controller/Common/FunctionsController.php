<?php

namespace AppBundle\Controller\Common;


use AppBundle\AppConstant;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;


class FunctionsController
{
    Public function getIP()
    {
        return $_SERVER['REMOTE_ADDR'];
    }

    Public function getCountryCode(Request $request)
    {
        return $country_id = $request->get('_country');
    }

    Public function getCountryLocal(Request $request)
    {
        return $locale = $request->getLocale();
    }
}
