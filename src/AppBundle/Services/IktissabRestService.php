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


use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

class IktissabRestService
{
    private $restClient;
    private $tokenStorage;
    private $jsonEncoder;
    private $request;
    private $isAuthorized = false;
    private $isAdmin = false;
    private $container;

    /**
     * IktissabRestService constructor.
     * @param TokenStorage $tokenStorage
     * @param FOpenWrapper $restClient
     * @param JsonEncoder $jsonEncoder
     * @param RequestStack $requestStack
     * @param ContainerInterface $container
     */
    public function __construct(TokenStorage $tokenStorage, FOpenWrapper $restClient, JsonEncoder $jsonEncoder, RequestStack $requestStack, ContainerInterface $container )
    {
        $this->tokenStorage = $tokenStorage;
        $this->restClient = $restClient;
        $this->jsonEncoder = $jsonEncoder;
        $this->request = $requestStack->getCurrentRequest();
        $this->container = $container;
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
        if(empty($path) || $path == null) throw new \Exception("Invalid Uri Path");
        return sprintf(AppConstant::IKTISSAB_API_URL, $this->request->getLocale(), $path );
    }
    private function GetXWSSE(){

        //$d = new \DateTime("NOW");
        //$currentDate = $d->format("Y/m/d H:i:s");

        $area = "anonymous";
        $email = "anonymous@gmail.com";
        $secret = "";
        if($this->isAdmin){
            $area = "admin";
            $email  = $this->container->getParameter('admin_user_email');
            $secret = md5($this->container->getParameter('admin_user_password'));
        }
        else if($this->isAuthorized){
            $area = "customer";
            $email = $this->tokenStorage->getToken()->getUser()->getUsername();
            $secret = $this->tokenStorage->getToken()->getUser()->getPassword();
        }

        $guid = sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));

        $nonce = md5($guid);
        $passwordHash = sha1(base64_encode($nonce) . $secret);
        $passwordDigest =  base64_encode($passwordHash);
        $digest = 'UsernameToken Username="%s", area="%s", PasswordDigest="%s", Nonce="%s"';
        $digest = sprintf($digest, $email, $area, $passwordDigest, $nonce);
        return $digest;

    }

    /**
     * @param boolean $isAuthorized
     * @return IktissabRestService
     */
    public function IsAuthorized($isAuthorized){
        $this->isAuthorized = $isAuthorized;
        return $this;
    }

    /**
     * @param boolean $isAdmin
     * @return $this
     */
    public function IsAdmin($isAdmin){
        $this->isAdmin = $isAdmin;
        return $this;
    }
    /**
     * @param $path
     * @return mixed|string
     * @throws RestServiceFailedException
     */
    public function Get( $path)
    {
        $uri = $this->GetWebServiceUrl($path);
        try {
            $result = $this->restClient->get($uri, $this->GetHeaders());
            if($result->getStatusCode() == Response::HTTP_FORBIDDEN){
                throw new AccessDeniedException();
            }
            if ($result->headers->get('Content-Type') == 'application/json') {
                $resultFormatted = $this->jsonEncoder->decode($result->getContent(), 'json');
                return $resultFormatted;
            } else {
                return $result->getContent();
            }

        } catch (\Exception $e) {
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
        $options = $this->GetHeaders();
        $uri = $this->GetWebServiceUrl($path);
        try {
            $result = $this->restClient->post($uri, $payload, $options);

            if($result->getStatusCode() == Response::HTTP_FORBIDDEN){
                throw new AccessDeniedException();
            }

            if ($result->headers->get('Content-Type') == 'application/json') {
                $resultFormatted = $this->jsonEncoder->decode($result->getContent(), 'json');
                return $resultFormatted;
            } else {
                return $result->getContent();
            }

        } catch (\Exception $e) {
            throw new RestServiceFailedException(404, $e);
        }
    }

}