<?php
/**
 * Created by PhpStorm.
 * User: abdulali
 * Date: 12/25/16
 * Time: 4:33 PM
 */
namespace AppBundle\Services;

use AppBundle\AppConstant;
use Circle\RestClientBundle\Exceptions\CurlException;
use Circle\RestClientBundle\Services\RestClient;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

class RestClientService
{
    private $restClient;
    private $apiUrl;
    private $jsonEncoder;

    public function __construct(RestClient $restClient, JsonEncoder $jsonEncoder)
    {
        $this->restClient = $restClient;
        $this->jsonEncoder = $jsonEncoder;
    }

    public function restGet($url, array $headers = array())
    {
        $headerFormatted = array();
        $options = array();
        $resultFormatted = array();
        $returnFailure = array('success' => 'false', 'msg' => 'APi error');
        foreach ($headers as $key => $val) {
            $headerFormatted[$key] = $key . ':' . $val;
        }
        if (!empty($headerFormatted)) {
            $options = array(CURLOPT_HTTPHEADER => $headerFormatted);
        }
        try {
            $result = $this->restClient->get($url, $options);
            if ($result->headers->get('content_type') == 'application/json') {
                $resultFormatted = $this->jsonEncoder->decode($result->getContent(), 'json');
                return $resultFormatted;
            } else {
                return $result->getContent();
            }

        } catch (CurlException $e) {
            return $returnFailure;
        }
    }
//    public function restPost($url, $payload, $headers = array())
    public function restPost($url, $payload, $headers = array())
    {
        $headerFormatted = array();
        $options = array();
        $resultFormatted = array();
        $returnFailure = array('success' => 'false', 'msg' => 'APi error');
        foreach ($headers as $key => $val) {
            $headerFormatted[$key] = $key . ':' . $val;
        }
        if (!empty($headerFormatted)) {
            $options = array(CURLOPT_HTTPHEADER => $headerFormatted);
        }

        try {
//            $result = $this->restClient->post($this->apiUrl . $url,$payload, $options);
            $result = $this->restClient->post($url,$payload, $options);
            if ($result->headers->get('content_type') == 'application/json') {
                $resultFormatted = $this->jsonEncoder->decode($result->getContent(), 'json');
                return $resultFormatted;
            } else {
                return $result->getContent();
            }

        } catch (CurlException $e) {
            return $returnFailure;
        }
    }
}