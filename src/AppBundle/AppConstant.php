<?php
/**
 * Created by PhpStorm.
 * User: abdulali
 * Date: 12/18/16
 * Time: 1:09 PM
 */
namespace AppBundle;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
class AppConstant
{
    //Activity Log Constants
    const  ACTIVITY_LOGIN = 'Login';
    const  ACTIVITY_LOGOUT = 'Logout';
    const  ACTIVITY_SEND_SMS = 'SmsSent';
    const  ACTIVITY_SEND_SMS_FAILED = 'SmsNotSent';
    const  ACTIVITY_SEND_EMAIL = 'EmailSent';
    const  ACTIVITY_NEW_CARD_REGISTRATION = 'NewCardRegistration';
    const  ACTIVITY_NEW_CARD_REGISTRATION_SUCCESS = 'NewCardRegistrationSuccessfull';
    const  ACTIVITY_NEW_CARD_REGISTRATION_ERROR = 'NewCardRegistrationError';
    const  ACTIVITY_EXISTING_CARD_REGISTRATION = 'ExistingCardRegistration';
    const  ACTIVITY_EXISTING_CARD_REGISTRATION_SUCCESS = 'ExistingCardRegistrationSuccessfull';
    const  ACTIVITY_EXISTING_CARD_REGISTRATION_ERROR = 'ExistingCardRegistrationError';


    const  ACTIVITY_UPDATE_MOBILE_SUCCESS = 'UpdateMobileSuccessfull';
    const  ACTIVITY_UPDATE_MOBILE_ERROR = 'UpdateMobileError';

    const  ACTIVITY_UPDATE_IQAMA_SUCCESS = 'UpdateIqamaSuccessfull';
    const  ACTIVITY_UPDATE_IQAMA_ERROR   = 'UpdateIqamaError';

    const  ACTIVITY_UPDATE_FULLNAME_SUCCESS = 'UpdateFullnameSuccessfull';
    const  ACTIVITY_UPDATE_FULLNAME_ERROR   = 'UpdateFullnameError';

    const  ACTIVITY_UPDATE_MISSINGCARD_SUCCESS = 'UpdateMissingCardSuccessfull';
    const  ACTIVITY_UPDATE_MISSINGCARD_ERROR   = 'UpdateMissingCardError';

    const  ACTIVITY_UPDATE_PASSWORD_SUCCESS = 'UpdatePasswordSuccessfull';
    const  ACTIVITY_UPDATE_PASSWORD_ERROR   = 'UpdatePasswordError';

    const  ACTIVITY_UPDATE_USERINFO_SUCCESS = 'UpdateUserInfoSuccessfull';
    const  ACTIVITY_UPDATE_USERINFO_ERROR   = 'UpdateUserInfoError';

    const ACTIVITY_FORGOT_EMAIL_SMS = 'SmsSentForForgotEmail';
    const ACTIVITY_FORGOT_EMAIL_SMS_FAILED = 'SmsNotSentForForgotEmail';


    const  ACTIVITY_UPDATE_RESETPASSWORD_SUCCESS = 'UpdateRestPasswordSuccessfull';
    const  ACTIVITY_UPDATE_RESETPASSWORD_ERROR   = 'UpdateRestPasswordError';

    const  ACTIVITY_FORGOT_PASSWORD_SUCCESS = 'UpdateForgotPasswordSuccessfull';
    const  ACTIVITY_FORGOT_PASSWORD_ERROR   = 'UpdateForgotPasswordError';

    const  ACTIVITY_UPDATE_EMAIL_SUCCESS = 'UpdateEmailSuccessfull';
    const  ACTIVITY_UPDATE_EMAIL_ERROR   = 'UpdateEmailError';
    const  ACTIVITY_UPDATE_EMAIL_ALREADY_REGISTERED   = 'UpdateEmailAlreadyRegistered';

    const  ACTIVITY_EMAIL_UPDATE_SMS_SUCCESS = 'UpdateEmailSMSSuccessfull';
    const  ACTIVITY_EMAIL_UPDATE_SMS_ERROR   = 'UpdateEmailSMSError';

    const ACTIVITY_ADD_INQUIRY_FORM = 'InquiryFormSubmittedSuccessfully';
    const ACTIVITY_ADD_INQUIRY_FORM_ERROR = 'InquiryFormSubmittedError';

    const ACTIVITY_ACCOUNT_HOME = 'ActivityAccountHome';
    const ACTIVITY_ACCOUNT_HOME_ERROR = 'ActivityAccountHomeError';
    const ACOUNR_PERSONAL_INFO_ERROR = 'AccountPersonalInfoError';




    //   related to cookies
    const  COOKIE_EXPIRY   = 2592000; //'86400*30';
    const  COOKIE_LOCALE   = 'c_locale';
    const  COOKIE_COUNTRY  = 'c_country';

    //webservice main url

    // const WEBAPI_URL =  'http://ma.othaimmarkets.com:8080/iktissabv2s/web/';
    const WEBAPI_URL =  'http://ma.othaimmarkets.com:8080/IktissabServicesV2/web/';
    // const WEBAPI_URL =  'http://150.150.100.93:8080/IktissabServicesV2/web/';

    const OTHAIM_WEBSERVICE_URL     = "http://www.othaimmarkets.com/webservices/api/v2";
    const OTHAIM_WEBSERVICE_URL_EG  = "http://www.othaimmarkets.com/eg/webservices/api/v2";
    
    const IKTISSAB_API_URL    = 'http://ma.othaimmarkets.com:8080/IktissabServicesV2/web/%s/api/%s.json';
    // const IKTISSAB_API_URL    = 'http://150.150.100.93:8080/IktissabServicesV2/web/%s/api/%s.json';

    const IKTISSAB_API_USER   = '1eaf95a1-4e8c-11e7-9833-842b2b4da8dd';

    const IKTISSAB_API_SECRET = 'da28cd9e2ae7bb9d157f091240015a8b';
    //ikt user languages
    const IKT_USER_LANG_EN_EN = 'English';
    const IKT_USER_LANG_EN_AR = 'Arabic';
    const IKT_USER_LANG_AR_EN = 'الإنجليزية';
    const IKT_USER_LANG_AR_AR = 'العربية';

    const IKT_REG_SCENERIO_1 = 'SCENERIO1';
    const IKT_REG_SCENERIO_2 = 'SCENERIO2';


    const IKT_SA_PREFIX    = '966';
    const IKT_EG_PREFIX    = '0020';
    const EMAIL_SUBJECT    = 'Abdullah Al Othaim Markets';
    const INVALID_DATA     = "Invalid Data";
    const SECRET_KEY_FP    = 'SDAWEI123123AJT';
    // const DATE_FORMAT   = "d-m-Y";
    const DATE_FORMAT_DOB  = "Y-m-d";
    const DATE_FORMAT      = "m/d/Y";
    const DATE_TIME_FORMAT = "Y-m-d h:i:m";

    
    const PROMOTIONS_PATH    = 'http://www.othaimmarkets.com/othaim-promotions/promotions';
    const PROMOTIONS_PATH_EG = 'http://www.othaimmarkets.com/eg/othaim-promotions/promotions';
    // const COOKIE_NOT_PRESENT_URL = "http://test.othaimmarkets.com/splash";

    // const BASE_URL = "http://othaimmarkets.com/ikitssab/index.php";
    const BASE_URL = "http://localhost/iktissabNew/index.php";
}