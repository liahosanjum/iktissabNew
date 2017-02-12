<?php
/**
 * Created by PhpStorm.
 * User: abdulali
 * Date: 12/18/16
 * Time: 1:09 PM
 */
namespace AppBundle;
class AppConstant
{
    //Activity Log Constants
    const  ACTIVITY_LOGIN = 'Login';
    const  ACTIVITY_LOGOUT = 'Logout';
    const  ACTIVITY_SEND_SMS = 'SmsSent';
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


    //related to cookies
    const  COOKIE_EXPIRY_REST_PASSWORD = 23000; //'86400*30';
    const  COOKIE_EXPIRY = 2592000; //'86400*30';
    const  COOKIE_LOCALE = 'c_locale';
    const  COOKIE_COUNTRY = 'c_country';
    const  COOKIE_RESET_PASSWORD = 'rest_password';

    //webservice main url
    const WEBAPI_URL = 'http://ma.othaimmarkets.com:8080/iktissabv2s/web/';
    
    const IKTISSAB_API_URL = 'http://150.150.101.26:8080/iktserv2_2/web/%s/api/%s.json';
    const IKTISSAB_API_USER = "anounymouse@email.com";
    const IKTISSAB_API_SECRET = 'anounymouse';
    //ikt user languages
    const IKT_USER_LANG_EN_EN = 'English';
    const IKT_USER_LANG_EN_AR = 'Arabic';
    const IKT_USER_LANG_AR_EN = 'الإنجليزية';
    const IKT_USER_LANG_AR_AR = 'العربية';

    const IKT_REG_SCENERIO_1 = 'SCENERIO1';
    const IKT_REG_SCENERIO_2 = 'SCENERIO2';


    const IKT_SA_PREFIX = '966';
    const IKT_EG_PREFIX = '002';


    const EMAIL_SUBJECT = 'Abdullah Al Othaim Markets';

    const INVALID_DATA = "Invalid Data";
}