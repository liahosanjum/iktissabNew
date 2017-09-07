<?php
/**
 * Created by PhpStorm.
 * User: abdulali
 * Date: 1/8/17
 * Time: 4:46 PM
 */

namespace AppBundle\Services;


use AppBundle\AppConstant;


class SmsService
{
    private $params;
    private $restClient;

    public function __construct(FOpenWrapper $restClient, $params)
    {
        $this->params = $params;
        $this->restClient = $restClient;
    }

    private function cleanMobile($mobile, $country){
        $mobile = trim($mobile);
        $length = strlen($mobile);
        $m = '';
        if($country == 'sa'){
            switch ($length){
                case 9: $m =  $mobile; break;
                case 10: $m =  substr($mobile, 1, $length - 1); break;
                case 12: $m =  substr($mobile, 3, $length - 3); break;
                case 13: $m =  substr($mobile, 4, $length - 4); break;
                case 14: $m =  substr($mobile, 5, $length - 5); break;
            }
        }
        else{
            switch ($length){
                case 10: $m =  $mobile; break;
                case 11: $m =  substr($mobile, 1, $length - 1); break;
                case 12: $m =  substr($mobile, 2, $length - 2); break;
                case 13: $m =  substr($mobile, 3, $length - 3); break;
                case 14: $m =  substr($mobile, 4, $length - 4); break;

            }

        }


        return $m;
    }


    /**
     * @param $receiver
     * @param $message
     * @param $country
     * @return bool
     */
    public function sendSms($receiver, $message, $country)
    {
        if($country == '') $country = 'sa';
        $param = $this->params['country'][$country];
        $user = $param['user'];
        $pass = $param['pass'];
        $sender = $param['sender'];
        $prefix = $param['prefix'];
        $domain_name = $this->params['domain_name'];
        $application_type = $this->params['application_type'];
        $url = $this->params['url'];

        $number = $prefix.$this->cleanMobile($receiver, $country);

        $msgID  = rand(1, 9999);
        $deleteKey = rand(1, 9999);

        $msg = urlencode($message);

        $payload = sprintf("mobile=%s&password=%s&numbers=%s&sender=%s&msg=%s&applicationType=%s&domainName=%s&msgId=%d&deleteKey=%d&lang=3&timeSend=0&dateSend=0",
            $user, $pass, $number, $sender, $msg, $application_type, $domain_name, $msgID, $deleteKey );
        $url.='?'.$payload;

        $result = $this->restClient->get($url, ["Content-Type"=>"text/html"]);


        if ($result->getContent() == '1') {
            return true;
        }

        return false;
    }


    function sendSmsEmail($receiver, $message, $country)
    {
        return $this->sendSms($receiver, $message, $country);
        if ($country == 'eg')
        {
            $mobilyUser = $this->params['mobily_user_eg'];
            $mobilyPass = $this->params['mobily_pass_eg'];
            // for EG we store the full mobile number format 14
            $countryPrefix = '';  //AppConstant::IKT_EG_PREFIX;
            $mobilySender  = $this->params['mobily_sender_eg'];
        }
        else
        {
            $mobilyUser = $this->params['mobily_user'];
            $mobilyPass = $this->params['mobily_pass'];
            $countryPrefix = AppConstant::IKT_SA_PREFIX;
            $mobilySender = $this->params['mobily_sender'];
            // format number
            if(strlen($receiver) == 10)
                $receiver = substr($receiver,1 ,strlen($receiver)-1);
        }
        // for testing

        $msgID  = rand(1, 9999);
        $delKey = rand(1, 9999);
        //$messageFormatted = urlencode(iconv("UTF-8", "windows-1256", $message));
        $messageFormatted = urlencode($message);

        //$messageFormatted = $message;
        $payload = "mobile=" . $mobilyUser . "&password=" . $mobilyPass . "&numbers=" . $countryPrefix . $receiver . "&sender=" . $mobilySender . "&msg=" . $messageFormatted . "&timeSend=0&dateSend=0&applicationType=" . $this->params['mobily_app_type'] . "&domainName=" . $this->params['mobily_app_type'] . "&msgId=" . $msgID . "&deleteKey=" . $delKey . "&lang=3";
        $url = $this->params['mobily_url'] . '?' . $payload;
        //echo $url;
        $sms = $this->restClient->get($url, ["Content-Type"=>"text/html"]);
//        $sms = 1;
        // var_dump($sms);
//        die('---');
        if ($sms == '1') {
            return true;
        }
        /*
        else {
            Throw new Exception('Failed to send sms');
        }
        */

        return false;
    }
}