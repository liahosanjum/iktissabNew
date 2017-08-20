<?php
/**
 * Created by PhpStorm.
 * User: abdulali
 * Date: 1/8/17
 * Time: 4:46 PM
 */

namespace AppBundle\Services;


use AppBundle\AppConstant;
use Symfony\Component\Config\Definition\Exception\Exception;

class SmsService
{
    private $params;
    private $restClient;

    public function __construct(RestClientService $restClientService, $params)
    {
        $this->params = $params;
        $this->restClient = $restClientService;
    }

    function sendSms($receiver, $message, $country)
    {
        if ($country == 'eg')
        {
            $mobilyUser = $this->params['mobily_user_eg'];
            $mobilyPass = $this->params['mobily_pass_eg'];
            // for EG we store the countrycode in with mobile number
            // so no need to add prefix
            $countryPrefix = '';  //AppConstant::IKT_EG_PREFIX;
            $mobilySender = $this->params['mobily_sender_eg'];
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
        // $receiver_mob = $receiver;
        //
        $msgID  = rand(1, 9999);
        $delKey = rand(1, 9999);
        //iconv ( "UTF-8", "windows-1256", $message );
        //$messageFormatted = urlencode(iconv("UTF-8", "windows-1256", $message));
        $messageFormatted = urlencode($message);

        $payload = "mobile=" . $mobilyUser . "&password=" . $mobilyPass . "&numbers=" . $countryPrefix . $receiver . "&sender=" . $mobilySender . "&msg=" . $messageFormatted . "&timeSend=0&dateSend=0&applicationType=" . $this->params['mobily_app_type'] . "&domainName=" . $this->params['mobily_app_type'] . "&msgId=" . $msgID . "&deleteKey=" . $delKey . "&lang=3";
        $url = 'http://www.mobily.ws/api/msgSend.php?' . $payload;
        //echo $url;
        $sms = $this->restClient->restGet($url, array());
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


    function sendSmsTest($receiver, $message, $country)
    {
        if ($country == 'eg')
        {
            $mobilyUser = $this->params['mobily_user_eg'];
            $mobilyPass = $this->params['mobily_pass_eg'];
            // for EG we store the
            $countryPrefix = '';  //AppConstant::IKT_EG_PREFIX;
            $mobilySender = $this->params['mobily_sender_eg'];
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
        $msgID  = rand(1, 9999);
        $delKey = rand(1, 9999);
        //iconv ( "UTF-8", "windows-1256", $message );
        // $messageFormatted = urlencode(iconv("UTF-8", "windows-1256", $message));
        // $messageFormatted = iconv("UTF-8", "windows-1256", $message);

        $messageFormatted =  urlencode($message);

        $payload = "mobile=" . $mobilyUser . "&password=" . $mobilyPass . "&numbers=" . $countryPrefix . $receiver . "&sender=" . $mobilySender . "&msg=" . $messageFormatted . "&timeSend=0&dateSend=0&applicationType=" . $this->params['mobily_app_type'] . "&domainName=" . $this->params['mobily_app_type'] . "&msgId=" . $msgID . "&deleteKey=" . $delKey . "&lang=3";
        $url = 'http://www.mobily.ws/api/msgSend.php?' . $payload;
        //echo $url;
        $sms = $this->restClient->restGet($url, array());
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


    function sendSmsEmail($receiver, $message, $country)
    {
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
        $receiver_mob = $receiver;
        $msgID  = rand(1, 9999);
        $delKey = rand(1, 9999);
        //$messageFormatted = urlencode(iconv("UTF-8", "windows-1256", $message));
        $messageFormatted = urlencode($message);

        //$messageFormatted = $message;
        $payload = "mobile=" . $mobilyUser . "&password=" . $mobilyPass . "&numbers=" . $countryPrefix . $receiver . "&sender=" . $mobilySender . "&msg=" . $messageFormatted . "&timeSend=0&dateSend=0&applicationType=" . $this->params['mobily_app_type'] . "&domainName=" . $this->params['mobily_app_type'] . "&msgId=" . $msgID . "&deleteKey=" . $delKey . "&lang=3";
        $url = 'http://www.mobily.ws/api/msgSend.php?' . $payload;
        //echo $url;
        $sms = $this->restClient->restGet($url, array());
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