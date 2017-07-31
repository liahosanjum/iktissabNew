<?php
/**
 * Created by PhpStorm.
 * User: s.aman
 * Date: 7/26/17
 * Time: 4:54 PM
 */

namespace AppBundle\Services;


class IKTWebService
{
    private $url ='http://iktweb.othaimmarkets.com/ikt_data/n_ikt_webservice.asmx?WSDL';
    private $client;

    public function __construct()
    {
        $this->client = new \SoapClient($this->url);
    }

    /**
     * @param $serviceName
     * @param $params
     * @return mixed
     */
    public function Get($serviceName, $params){
        $this->client = $this->client->$serviceName($params);
        $serviceResult = $serviceName."Result";
        return $this->client->$serviceResult;
    }
}