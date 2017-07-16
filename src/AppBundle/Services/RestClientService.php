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
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;


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


    private  function GetXWSSE(){

        $d = new \DateTime("NOW");
        $currentDate = $d->format("Y/m/d H:i:s");

        $guid = sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));

        $nonce = md5($guid);
        $passwordHash = sha1(base64_encode($nonce) . $currentDate . AppConstant::IKTISSAB_API_SECRET);
        $passwordDigest =  base64_encode($passwordHash);
        $digest = 'UsernameToken Username="%s", PasswordDigest="%s", Nonce="%s", Created="%s"';
        $digest = sprintf($digest, AppConstant::IKTISSAB_API_USER, $passwordDigest, $nonce, $currentDate);
        return $digest;
    }


    private  function GetXWSSETEST(){

        $d = new \DateTime("NOW");
        $currentDate = $d->format("Y/m/d H:i:s");

        $guid = sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));

        $nonce = md5($guid);
        $passwordHash = sha1(base64_encode($nonce) . $currentDate . AppConstant::IKTISSAB_API_SECRET);
        $passwordDigest =  base64_encode($passwordHash);
        $digest = 'UsernameToken Username="%s", PasswordDigest="%s", Nonce="%s", Created="%s"';
        $digest = sprintf($digest, AppConstant::IKTISSAB_API_USER.'asdf', $passwordDigest, $nonce, $currentDate);
        return $digest;
    }



    public function restGetTEST($url, array $headers = array())
    {
        $headerFormatted = array();
        $options = array();
        $resultFormatted = array();
        $returnFailure = array('success' => 'false', 'msg' => 'APi error');
        foreach ($headers as $key => $val) {
            $headerFormatted[$key] = $key . ':' . $val;
        }
        $headerFormatted['x-wsse'] = 'x-wsse:'. $this->GetXWSSETEST();
        if ($headerFormatted) {
            //if (!empty($headerFormatted)) {
            $options = array(CURLOPT_HTTPHEADER => $headerFormatted);
        }
        try
        {
            $result = $this->restClient->get($url, $options);
            $result->getContent();
            if($result->getStatusCode() == Response::HTTP_OK)
            {
                if($result->headers->get('content_type') == 'application/json')
                {
                    $resultFormatted = $this->jsonEncoder->decode($result->getContent(), 'json');
                    return $resultFormatted;
                }
                else
                {
                    return $result->getContent();
                }
            }
            else if($result->getStatusCode() == Response::HTTP_FORBIDDEN )
            {
                // this is for handling unauthorized access
                // status code HTTP_INTERNAL_SERVER_ERROR
                throw new AccessDeniedException('Unable to process your request.Please try later22');
            }
            else
            {
                throw new AccessDeniedException('Unable to process your request.Please try later11');
            }

        } catch (CurlException $e) {
            throw new AccessDeniedException('Unable to process your request.Please try later11');
        }
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
        $headerFormatted['x-wsse'] = 'x-wsse:'. $this->GetXWSSE();
        if ($headerFormatted) {
            //if (!empty($headerFormatted)) {
            $options = array(CURLOPT_HTTPHEADER => $headerFormatted);
        }
        try
        {
            $result = $this->restClient->get($url, $options);
            $result->getContent();
            if($result->getStatusCode() == Response::HTTP_OK)
            {
                if($result->headers->get('content_type') == 'application/json')
                {
                    $resultFormatted = $this->jsonEncoder->decode($result->getContent(), 'json');
                    return $resultFormatted;
                }
                else
                {
                    return $result->getContent();
                }
            }
            else if($result->getStatusCode() == Response::HTTP_FORBIDDEN )
            {
                // this is for handling unauthorized access
                // status code HTTP_INTERNAL_SERVER_ERROR
                throw new AccessDeniedException('Unable to process your request.Please try later');
            }
            else
            {
                throw new AccessDeniedException('Unable to process your request.Please try later');
            }

        } catch (CurlException $e) {
            throw new AccessDeniedException('Unable to process your request.Please try later');
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
        $headerFormatted['x-wsse'] = 'x-wsse:'. $this->GetXWSSE();
        if (!empty($headerFormatted)) {
            $options = array(CURLOPT_HTTPHEADER => $headerFormatted);
        }

        try
        {
            $result = $this->restClient->post($url,$payload, $options);
            if($result->getStatusCode() == Response::HTTP_OK)
            {
                if ($result->headers->get('content_type') == 'application/json')
                {
                    $resultFormatted = $this->jsonEncoder->decode($result->getContent(), 'json');
                    return $resultFormatted;
                }
                else
                {
                    return $result->getContent();
                }
            }
            else if($result->getStatusCode() == Response::HTTP_FORBIDDEN )
            {
                throw new AccessDeniedException('Unable to process your request.Please try later22');
            }
            else
            {
                throw new AccessDeniedException('Unable to process your request.Please try later11');
            }
        } catch (CurlException $e) {
            return $returnFailure;
        }
    }

    public function restPostForm($url, $payload, $headers = array())
    {
        $headerFormatted = array();
        $options = array();
        $resultFormatted = array();
        $returnFailure = array('success' => false, 'message' => 'APi Error' , 'status' => 0 );
        foreach ($headers as $key => $val) {
            $headerFormatted[$key] = $key . ':' . $val;
        }
        $headerFormatted['x-wsse'] = 'x-wsse:'. $this->GetXWSSE();
        if (!empty($headerFormatted)) {
            $options = array(CURLOPT_HTTPHEADER => $headerFormatted);
        }

        try
        {
            $result = $this->restClient->post($url,$payload, $options);

            $result->getContent();
            if($result->getStatusCode() == Response::HTTP_OK)
            {
                if($result->headers->get('content_type') == 'application/json')
                {
                    $resultFormatted = $this->jsonEncoder->decode($result->getContent(), 'json');
                    return $resultFormatted;
                }
                else
                {
                    return $result->getContent();
                }
            }
            else if($result->getStatusCode() == Response::HTTP_FORBIDDEN )
            {
                throw new Exception('Unable to process your request at this time.Please try later');
            }
            else
            {
                throw new Exception('Unable to process your request at this time.Please try later');
            }


        }
        catch (CurlException $e)
        {
           throw new Exception('Unable to process your request at this time.Please try later');
        }
    }





    public function restGetFormTEST($url, array $headers = array())
    {
        $headerFormatted = array();
        $options = array();
        $resultFormatted = array();
        $returnFailure = array('success' => false, 'message' => 'APi error' , 'status' => 0 );
        foreach ($headers as $key => $val) {
            $headerFormatted[$key] = $key . ':'  . $val;
        }
        $headerFormatted['x-wsse'] = 'x-wsse:'. $this->GetXWSSETEST();
        if ($headerFormatted) {
            // if (!empty($headerFormatted)) {
            $options = array(CURLOPT_HTTPHEADER => $headerFormatted);
        }
        try
        {
            $result = $this->restClient->get($url, $options);
            // print_r($result);
            echo 'here restGetForm '.$result->getContent();
            if($result->getStatusCode() == Response::HTTP_OK)
            {
                if($result->headers->get('content_type') == 'application/json')
                {
                    $resultFormatted = $this->jsonEncoder->decode($result->getContent(), 'json');
                    return $resultFormatted;
                }
                else
                {
                    return $result->getContent();
                }
            }
            else if($result->getStatusCode() == Response::HTTP_FORBIDDEN )
            {
                throw new AccessDeniedException('Unable to process your request at this time.Please try later');
            }
            else
            {
                throw new AccessDeniedException('Unable to process your request at this time.Please try later');
            }
        }
        catch (CurlException $e)
        {
            throw new AccessDeniedException('Unable to process your request at this time.Please try later');
        }
    }



    public function restGetForm($url, array $headers = array())
    {
        $headerFormatted = array();
        $options = array();
        $resultFormatted = array();
        $returnFailure = array('success' => false, 'message' => 'APi error' , 'status' => 0 );
        foreach ($headers as $key => $val) {
            $headerFormatted[$key] = $key . ':'  . $val;
        }
        $headerFormatted['x-wsse'] = 'x-wsse:'. $this->GetXWSSE();
        if ($headerFormatted) {
            // if (!empty($headerFormatted)) {
            $options = array(CURLOPT_HTTPHEADER => $headerFormatted);
        }
        try
        {
            $result = $this->restClient->get($url, $options);
            // print_r($result);
            echo 'here restGetForm '.$result->getContent();
            if($result->getStatusCode() == Response::HTTP_OK)
            {
                if($result->headers->get('content_type') == 'application/json')
                {
                    $resultFormatted = $this->jsonEncoder->decode($result->getContent(), 'json');
                    return $resultFormatted;
                }
                else
                {
                    return $result->getContent();
                }
            }
            else if($result->getStatusCode() == Response::HTTP_FORBIDDEN )
            {
                throw new AccessDeniedException('Unable to process your request at this time.Please try later');
            }
            else
            {
                throw new AccessDeniedException('Unable to process your request at this time.Please try later');
            }
        }
        catch (CurlException $e)
        {
            throw new AccessDeniedException('Unable to process your request at this time.Please try later');
        }
    }




    public function restPostFormTEST($url, $payload, $headers = array())
    {
        $headerFormatted = array();
        $options = array();
        $resultFormatted = array();
        $returnFailure = array('success' => false, 'message' => 'APi Error' , 'status' => 0 );
        foreach ($headers as $key => $val) {
            $headerFormatted[$key] = $key . ':' . $val;
        }
        $headerFormatted['x-wsse'] = 'x-wsse:'. $this->GetXWSSETEST();
        if (!empty($headerFormatted)) {
            $options = array(CURLOPT_HTTPHEADER => $headerFormatted);
        }

        try
        {
            $result = $this->restClient->post($url,$payload, $options);
            $result->getContent();
            if($result->getStatusCode() == Response::HTTP_OK)
            {
                if($result->headers->get('content_type') == 'application/json')
                {
                    $resultFormatted = $this->jsonEncoder->decode($result->getContent(), 'json');
                    return $resultFormatted;
                }
                else
                {
                    return $result->getContent();
                }
            }
            else if($result->getStatusCode() == Response::HTTP_FORBIDDEN )
            {
                throw new AccessDeniedException('Unable to process your request at this time.Please try later');
            }
            else
            {
                throw new AccessDeniedException('Unable to process your request at this time.Please try later');
            }


        }
        catch (CurlException $e)
        {
            throw new AccessDeniedException('Unable to process your request at this time.Please try later');
        }
    }

}