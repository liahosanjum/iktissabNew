<?php
/**
 * Created by PhpStorm.
 * User: abdulali
 * Date: 12/25/16
 * Time: 4:33 PM
 */
namespace AppBundle\Services;

use AppBundle\AppConstant;
use AppBundle\Exceptions\RestServiceFailedException;
use Circle\RestClientBundle\Exceptions\CurlException;
use Circle\RestClientBundle\Services\RestClient;

use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

class IktissabRestService
{
    private $restClient;
    private $jsonEncoder;
    private $request;

    /**
     * IktissabRestService constructor.
     * @param RestClient $restClient
     * @param JsonEncoder $jsonEncoder
     * @param RequestStack $requestStack
     */
    public function __construct(RestClient $restClient, JsonEncoder $jsonEncoder, RequestStack $requestStack )
    {
        $this->restClient = $restClient;
        $this->jsonEncoder = $jsonEncoder;
        $this->request = $requestStack->getCurrentRequest();
    }

    /**
     * @return array
     */
    private function GetHeaders()
    {
        $headers = [];
        $headers['Content-Type'] = 'Content-Type:application\json';
        $headers['x-wsse'] = 'x-wsse:' . $this->GetXWSSE();
        $headers['Country-Id'] = 'Country-Id:' . strtoupper($this->request->get('_country'));

        return $headers;
    }
    private function GetWebServiceUrl($path){
        if(empty($path) || $path == null) throw new Exception("Invalid Uri Path");
        return sprintf(AppConstant::IKTISSAB_API_URL, $this->request->getLocale(), $path );
    }
    private function GetXWSSE(){

        $d = new \DateTime("NOW");
        $currentDate = $d->format("y/m/d H:i:s");

        $guid = sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));

        $nonce = md5($guid);
        $passwordHash = sha1(base64_encode($nonce) . $currentDate . md5(AppConstant::IKTISSAB_API_SECRET));
        $passwordDigest =  base64_encode($passwordHash);
        $digest = 'UsernameToken Username="%s", PasswordDigest="%s", Nonce="%s", Created="%s"';
        $digest = sprintf($digest, AppConstant::IKTISSAB_API_USER, $passwordDigest, $nonce, $currentDate);
        return $digest;

    }

    /**
     * @param $path
     * @return mixed|string
     * @throws RestServiceFailedException
     */
    public function Get( $path)
    {
        $options = [CURLOPT_HTTPHEADER => $this->GetHeaders()];
        $uri = $this->GetWebServiceUrl($path);
        try {
            $result = $this->restClient->get($uri, $options);
            if ($result->headers->get('Content-Type') == 'application/json') {
                $resultFormatted = $this->jsonEncoder->decode($result->getContent(), 'json');
                return $resultFormatted;
            } else {
                return $result->getContent();
            }

        } catch (CurlException $e) {
            throw new RestServiceFailedException(404, $e);
        }
    }

    /**
     * @param $path
     * @param $payload
     * @return mixed|string
     * @throws RestServiceFailedException
     */
    public function Post($path, $payload)
    {
        $options = [CURLOPT_HTTPHEADER => $this->GetHeaders()];
        $uri = $this->GetWebServiceUrl($path);
        try {
            $result = $this->restClient->post($uri, $payload, $options);
            if ($result->headers->get('Content-Type') == 'application/json') {
                $resultFormatted = $this->jsonEncoder->decode($result->getContent(), 'json');
                return $resultFormatted;
            } else {
                return $result->getContent();
            }

        } catch (CurlException $e) {
            throw new RestServiceFailedException(404, $e);
        }
    }

}